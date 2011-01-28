<?php
/*****************************************************************************
 Enthusiast: Listing Collective Management System
 Copyright (c) by Angela Sabas
 http://scripts.indisguise.org/

 Enthusiast is a tool for (fan)listing collective owners to easily
 maintain their listing collectives and listings under that collective.

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.

 For more information please view the readme.txt file.
******************************************************************************/
session_start();
require_once( 'logincheck.inc.php' );
if( !isset( $logged_in ) || !$logged_in ) {
   $_SESSION['message'] = 'You are not logged in. Please log in to continue.';
   $next = '';
   if( isset( $_SERVER['REQUEST_URI'] ) )
      $next = $_SERVER['REQUEST_URI'];
   else if( isset( $_SERVER['PATH_INFO'] ) )
      $next = $_SERVER['PATH_INFO'];
   $_SESSION['next'] = $next;
   header( 'location: index.php' );
   die( 'Redirecting you...' );
}
require( 'config.php' );
require_once( 'header.php' );
require_once( 'mod_errorlogs.php' );
require_once( 'mod_categories.php' );
require_once( 'mod_owned.php' );
require_once( 'mod_members.php' );
require_once( 'mod_settings.php' );

$show_default = true;
?>
<h1>Owned Listings</h1>
<?php
$action = '';
if( isset( $_REQUEST["action"] ) )
   $action = $_REQUEST['action'];


/*__________________________________________________________________TEMPLATE_*/
if( $action == 'template' ) {
   $show_default = false;
   $show_template_form = true;
   if( isset( $_POST['done'] ) ) {
      $update_header = update_setting( 'owned_template_header',
         $_POST['header'] );
      $update_template = update_setting( 'owned_template',
         $_POST['template'] );
      $update_footer = update_setting( 'owned_template_footer',
         $_POST['footer'] );

      $show_default = true;
      $show_template_form = false;
      echo '<p class="success">Templates successfully updated.</p>';
   }

   if( $show_template_form ) {
      $header = get_setting( 'owned_template_header' );
      $template = get_setting( 'owned_template' );
      $footer = get_setting( 'owned_template_footer' );

      $header_help = get_setting_desc( 'owned_template_header' );
      $template_help = get_setting_desc( 'owned_template' );
      $footer_help = get_setting_desc( 'owned_template_footer' );
?>
      <p>You can edit the way your owned fanlistings will be shown via the
      templates below. If you need help, click on the 'help' button.</p>

      <form action="owned.php" method="post">
      <input type="hidden" name="action" value="template" />
      <input type="hidden" name="done" value="yes" />

      <table>

      <tr><td>
      Header<br />
      <a href="#" onclick="alert( '<?php echo addslashes( $header_help ) ?>' );"><img
         src="help.gif" width="42" height="19" border="0"
         alt=" click for help on this setting" /></a>
      </td><td>
      <textarea name="header" rows="5" cols="65"><?php echo $header ?></textarea>
      </td></tr>

      <tr class="rowshade"><td>
      Template<br />
      <a href="#" onclick="alert('<?php echo addslashes( $template_help ) ?>');"><img
         src="help.gif" width="42" height="19" border="0"
         alt=" click for help on this setting" /></a>
      </td><td>
      <textarea name="template" rows="5" cols="65"><?php echo $template ?></textarea>
      </td></tr>

      <tr><td>
      Footer<br />
      <a href="#" onclick="alert( '<?php echo addslashes( $footer_help ) ?>' );"><img
         src="help.gif" width="42" height="19" border="0"
         alt=" click for help on this setting" /></a>
      </td><td>
      <textarea name="footer" rows="5" cols="65"><?php echo $footer ?></textarea>
      </td></tr>

      <tr class="rowshade"><td colspan="2" class="right">
      <input type="submit" value="Update templates" />
      <input type="reset" value="Restore settings" />
      <input type="button" value="Cancel"
         onclick="javascript:window.location='owned.php';" />
      </td></tr>

      </table></form>
<?php
   }


/*______________________________________________________________________EDIT_*/
} else if( $action == 'edit' ) {
   $show_default = false;
   $show_edit_forms = true;
   $info = get_listing_info( $_REQUEST['id'] );

   if( isset( $_POST['done'] ) ) {
      if( isset( $_POST['image_change'] ) && $_POST['image_change'] == 'yes' &&
         isset( $_FILES['image']['name'] ) && $_FILES['image']['name'] != '' ){
         $dir = get_setting( 'owned_images_dir' );
         $filename = $_FILES['image']['name'];
         $upload_success = @move_uploaded_file( $_FILES['image']['tmp_name'],
            $dir . $info['listingid'] . '_' . $filename );
         if( !$upload_success ) {
            // try chmodding the $dir to 755 and then reuploading
            @chmod( $dir, 0755 );
            $upload_success = @move_uploaded_file( $_FILES['image']['tmp_name'],
               $dir . $info['listingid'] . '_' . $filename );
         }
         if( !$upload_success ) {
            // 755 doesn't work, so we chmod to 777 and then go back
            @chmod( $dir, 0777 );
            $upload_success = @move_uploaded_file( $_FILES['image']['tmp_name'],
               $dir . $info['listingid'] . '_' . $filename );
            @chmod( $dir, 0755 ); // 777 is unsafe
         }
         if( $upload_success ) {
            // we had better check again!
            if( is_file( $dir . $info['listingid'] . '_' . $filename ) ) {
               $_POST['imagefile'] = $info['listingid'] . '_' . $filename;
            } else {
               echo '<p class="error">There was an error uploading the ' .
                  'new image.</p>';
               // we had better debug, because it says upload was successful
               // but the file is not a file daw!
               log_error( __FILE__ . ':' . __LINE__,
                  'Uploading owned image failed, PHP says it\'s not a file ' .
                  'or we don\'t have permission to see it.', false );
            }
         } else {
            echo '<p class="error">There was an error uploading the ' .
               'new image. Please check if your owned images folder path ' .
               'is correct (<code>' . $dir . '</code>) and if it is CHMODed to ' .
               '755.</p>';
         }
      }
      $changes = edit_owned( $_POST['id'], $_POST );
      if( count( $changes ) > 0 ) {
         echo '<div class="success">The following changes have been made:<ul>';
         foreach( $changes as $c ) echo '<li> ' . $c . ' </li>';
         echo '</ul></div>';
      }
   }

   if( $show_edit_forms ) {
      show_edit_forms();
   }




/*____________________________________________________________________DELETE_*/
} else if( $action == 'delete' ) {
   $info = get_listing_info( $_REQUEST['id'] );
   $success = delete_owned( $_REQUEST['id'] );
   if( $success )
      echo '<p class="success">Successfully deleted the <i>' .
         $info['subject'] . ' ' . $info['listingtype'] . '</i>.</p>';
      

}


if( $show_default ) {
?>
   <div class="submenu">
   <a href="setup.php">Add</a>
   <a href="owned.php?action=template">Template</a>
   </div>

   <p>Via this page, you can search for and manage listings you own. These 
   listings will show up on your collective's owned page when you have set
   the page up. To add/setup a new listing, click on the "Add" submenu item;
   to modify your owned listing template, click on the "Template" submenu
   item. Listings with a red question mark (<b class="important">?</b>) are 
   those pending approvals, and fanlistings with a blue exclamation point
   (<b class="upcoming">!</b>) are those on upcoming.</p>

   <form action="owned.php" method="get">
   <input type="hidden" name="dosearch" value="now" />

   <p class="center">
   <input type="text" name="search" />

   <select name="status" value="">
   <option value="">All</option>
   <option value="pending">Pending</option>
   <option value="upcoming">Upcoming</option>
   <option value="current">Current</option>
   </select>

   <input type="submit" value="Search" />
   </p>

   </form>

<?php
   $start = '0';
   if( isset( $_REQUEST['start'] ) )
      $start = $_REQUEST['start'];

   $total = 0;
   $ids = array();
   if( isset( $_GET['dosearch'] ) ) {
      if( $_GET['search'] == '' ) {
         $ids = get_owned( $_GET['status'], $start, 'bydate' );
         $total = count( get_owned( $_GET['status'] ) );
      } else { // search!
         $status = '';
         if( isset( $_GET['status'] ) )
            $status = $_GET['status'];
         $ids = search_owned( $_GET['search'], $status, $start );
         $total = count( search_owned( $_GET['search'], $status ) );
      }
   } else {
      $ids = get_owned( 'all', $start, 'bydate' );
      $total = count( get_owned() );
   }
?>
   <table>
   <tr><th>&nbsp;</th>
   <th>ID</th>
   <th>Title</th>
   <th>Subject/URL</th>
   <th>Category</th>
   <th>Image</th>
   <th>Action</th>
   </tr>
<?php
   $shade = false;
   foreach( $ids as $id ) {
      $info = get_listing_info( $id );
      $class = '';
      if( $shade ) {
         $class = ' class="rowshade"';
         $shade = false;
      } else $shade = true;

      $dir = get_setting( 'owned_images_dir' );
      $root_web = get_setting( 'root_path_web' );
      $root_abs = get_setting( 'root_path_absolute' );
      $dir = str_replace( $root_abs, $root_web, $dir );
      $image = ( $info['imagefile'] && is_file( $dir . $info['imagefile'] ) )
         ? getimagesize( $dir . $info['imagefile'] ) : array();
      // make sure $image is an array, in case getimagesize() failed
      if( !is_array( $image ) ) 
         $image = array();

      $target = '_self';

      if( $info['status'] == 0 )
         echo '<tr' . $class . '><td class="important"><b>?</b></td>';
      else if( $info['status'] == 1 )
         echo '<tr' . $class . '><td class="upcoming"><b>!</b></td>';
      else
         echo '<tr' . $class . '><td></td>';
?>
      <td><?php echo $info['listingid'] ?></td>
      <td><?php echo $info['title'] ?></td>
      <td><a href="<?php echo $info['url'] ?>" target="<?php echo $target
         ?>"><?php echo $info['subject'] ?></a></td>
<?php
      $catstring = '';
      $cats = explode( '|', $info['catid'] );
      foreach( $cats as $c )
         if( $c != '' ) {
            if( $ancestors = array_reverse( get_ancestors( $c ) ) ) {
               // get ancestors
               $text = '';
               foreach( $ancestors as $a )
                  $text .= get_category_name( $a ) . ' > ';
               $catstring .= str_replace( '>', '&raquo;', rtrim( $text, ' > ' ) ) .
                  ', ';
            }
         }
      $catstring = rtrim( $catstring, ', ' );
?>
      <td><?php echo $catstring ?></td>
<?php
      $dir = str_replace( '\\', '/', $dir );
      if( $info['imagefile'] != '' && count( $image ) )
         echo '<td class="center"><img src="' . $dir . $info['imagefile'] .
            '" ' . $image[3] . 'border="0" alt="" /></td>';
      else if( $info['imagefile'] != '' && count( $image ) == 0 )
         echo '<td class="center"><img src="' . $dir . $info['imagefile'] .
            '" border="0" alt="" /></td>';
      else
         echo '<td class="center">x</td>';
?>
      <td class="center">
      <a href="owned.php?action=edit&id=<?php echo $id ?>"><img src="edit.gif"
         width="42" height="19" border="0" alt=" edit" title=" edit" /></a>
      <a href="members.php?id=<?php echo $id ?>"><img src="manage.gif"
         width="42" height="19" border="0" alt=" manage" title=" manage" /></a>
      <a href="owned.php?action=delete&id=<?php echo $id ?>"
         onclick="go=confirm('Are you sure you want to delete the <?php echo addslashes( $info['subject'] ) ?> fanlisting?!?'); return go;"><img
         src="delete.gif" width="42" height="19" border="0"
         alt=" delete" title=" delete" /></a>
      </td></tr>
<?php
   }
?>
   </table>
<?php
   $page_qty = $total / get_setting( 'per_page' );
   $url = $_SERVER['REQUEST_URI'];

   $url = 'owned.php';
   $connector = '?';
   foreach( $_GET as $key => $value )
      if( $key != 'start' && $key != 'PHPSESSID' && $key != 'action' &&
         $key != 'id' ) {
         $url .= $connector . $key . '=' . $value;
         $connector = '&amp;';
      }

   if( $page_qty > 1 )
      echo '<p class="center">Go to page: ';

   $i = 1;
   while( ( $i <= $page_qty + 1 ) && $page_qty > 1 ) {
      $start_link = ( $i - 1 ) * get_setting( 'per_page' );
      echo '<a href="' . $url . $connector . 'start=' . $start_link . '">' .
      $i . '</a> ';
      $i++;
   }

   if( $page_qty > 1 )
      echo '</p>';
}
require_once( 'footer.php' );
?>