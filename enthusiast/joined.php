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
require_once( 'mod_joined.php' );
require_once( 'mod_settings.php' );

$errors = array();
$show_default = true;
?>
<h1>Joined Listings</h1>
<?php
$action = '';
if( isset( $_REQUEST["action"] ) )
   $action = $_REQUEST['action'];

/*_______________________________________________________________________ADD_*/
if( $action == 'add' ) {
   $show_default = false;
   $show_add_form = true;
   if( isset( $_POST['done'] ) ) {
      if( !isset( $_POST['catid'] ) || !is_array( $_POST['catid'] ) )
         $errors['catid'] = 'You must select at least one category for ' .
            'this listing to belong to.';
      if( $_POST['url'] == '' )
         $errors['url'] = 'You must enter the URL of the listing.';
      if( $_POST['subject'] == '' )
         $errors['subject'] = 'You must enter the subject of the listing.';

      if( count( $errors ) > 0 ) {
         echo '<p class="error">You have left out some required fields.</p>';
      } else {

         $pending = 1;
         if( isset( $_POST['approved'] ) && $_POST['approved'] == '1' )
            $pending = '0';

         $success = add_joined( $_POST['catid'], $_POST['url'],
            $_POST['subject'], $_POST['desc'], $_POST['comments'],
            $pending );
         if( !$success )
            echo '<p class="error">Error adding the listing.</p>';
         else {
            $show_add_form = false;
            $show_default = true;
            echo '<p class="success">Listing successfully added.</p>';

            // upload file if there is
            if( $_FILES['image']['name'] != '' ) {
               $filename = $success . '_' . $_FILES['image']['name'];
               $dir = get_setting( 'joined_images_dir' );
               $upload_success =
                  @move_uploaded_file( $_FILES['image']['tmp_name'],
                  $dir . $filename );
               if( !$upload_success ) {
                  // try chmodding the $dir to 755 and then reuploading
                  @chmod( $dir, 0755 );
                  $upload_success =
                     @move_uploaded_file( $_FILES['image']['tmp_name'],
                     $dir . $filename );
               }
               if( !$upload_success ) {
                  // 755 doesn't work, so we chmod to 777 and then go back
                  @chmod( $dir, 0777 );
                  $upload_success =
                     @move_uploaded_file( $_FILES['image']['tmp_name'],
                     $dir . $filename );
                  @chmod( $dir, 0755 ); // 777 is unsafe
               }
               if( $upload_success ) {
                  // chmod the file to 644
                  @chmod( $dir . $filename, 0644 );

                  $edited = edit_joined( $success, $filename );
                  if( $edited )
                     echo '<p class="success">Listing image uploaded ' .
                        'successfully.</p>';
                  else
                     echo '<p class="error">Error uploading/setting ' .
                        'image.</p>';
               } else {
                  echo '<p class="error">Error uploading the image. Please ' .
                     'make sure your joined images folder path is correct (<code>' .
                     $dir . '</code>) and it is CHMODed to 755.</p>';
               }
            }
         }
      }
   }

   if( $show_add_form ) {
      $catid = '';
      $url = '';
      $subject = '';
      $desc = '';
      $comments = '';
      $approved = '0';
      
      if( isset( $_REQUEST['catid'] ) )
         $catid = $_REQUEST['catid'];
      if( isset( $_REQUEST['url'] ) )
         $url = $_REQUEST['url'];
      if( isset( $_REQUEST['subject'] ) )
         $subject = $_REQUEST['subject'];
      if( isset( $_REQUEST['desc'] ) )
         $desc = $_REQUEST['desc'];
      if( isset( $_REQUEST['comments'] ) )
         $comments = $_REQUEST['comments'];
      if( isset( $_REQUEST['approved'] ) )
         $approved = 1;
?>
      <p>This page allows you to add a listing to your joined listings
      list. Fill out the form below and click on "Add this listing". Items
      marked red are required fields. You may select multiple categories
      for this listing to fall under.</p>

      <form action="joined.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add" />
      <input type="hidden" name="done" value="yes" />

      <table>

      <tr><td class="important" valign="top">
      Fanlisting Category
      </td><td>
      <select name="catid[]" multiple="multiple" size="5">
<?php
      $cats = enth_get_categories();
      $options = array();
      foreach( $cats as $cat ) {
         $optiontext = $cat['catname'];
         if( count( $ancestors =
            array_reverse( get_ancestors( $cat['catid'] ) ) ) > 1 ) {
            // get ancestors
            $text = '';
            foreach( $ancestors as $a )
               $text .= get_category_name( $a ) . ' > ';
            $optiontext = rtrim( $text, ' > ' );
            $optiontext = str_replace( '>', '&raquo;', $optiontext );
         }
         $options[] = array( 'text' => $optiontext, 'id' => $cat['catid'] );
      }
      usort( $options, 'category_array_compare' );
      foreach( $options as $o )
         echo '<option value="' . $o['id'] . '">' . $o['text'] . '</option>';
?>
      </select>
      </td></tr>

      <tr class="rowshade"><td class="important">
      Fanlisting URL
      </td><td>
      <input type="text" name="url" value="<?php echo $url ?>" />
      </td></tr>

      <tr><td class="important">
      Subject
      </td><td>
      <input type="text" name="subject" value="<?php echo $subject ?>" />
      </td></tr>

      <tr class="rowshade"><td>
      Description
      </td><td>
      <textarea name="desc" rows="5" cols="50"><?php echo $desc ?></textarea>
      </td></tr>

      <tr><td>
      Comments
      </td><td>
      <textarea name="comments" rows="5" cols="50"><?php echo $comments ?></textarea>
      </td></tr>

      <tr class="rowshade"><td>
      Image
      </td><td>
      <input type="file" name="image" value="<?php echo $filename ?>" />
      </td></tr>

      <tr><td colspan="2" class="right">
      Approved already?
<?php
      if( $approved == 1 )
         echo '<input type="checkbox" checked="checked" name="approved" ' .
            'value="1" />';
      else
         echo '<input type="checkbox" name="approved" value="1" />';
?>    
      <input type="submit" value="Add this listing" />
      <input type="reset" value="Clear form" />
      <input type="button" value="Cancel"
         onclick="javascript:window.location='joined.php';" />
      </td></tr>

      </table>
<?php
   }


/*__________________________________________________________________TEMPLATE_*/
} else if( $action == 'template' ) {
   $show_default = false;
   $show_template_form = true;
   if( isset( $_POST['done'] ) ) {
      $update_header = update_setting( 'joined_template_header',
         $_POST['header'] );
      $update_template = update_setting( 'joined_template',
         $_POST['template'] );
      $update_footer = update_setting( 'joined_template_footer',
         $_POST['footer'] );

      $show_default = true;
      $show_template_form = false;
      echo '<p class="success">Templates successfully updated.</p>';
   }

   if( $show_template_form ) {
      $header = get_setting( 'joined_template_header' );
      $template = get_setting( 'joined_template' );
      $footer = get_setting( 'joined_template_footer' );

      $header_help = get_setting_desc( 'joined_template_header' );
      $template_help = get_setting_desc( 'joined_template' );
      $footer_help = get_setting_desc( 'joined_template_footer' );
?>
      <p>You can edit the way your joined listings will be shown via the
      templates below. If you need help, click on the 'help' button.</p>

      <form action="joined.php" method="post">
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
         onclick="javascript:window.location='joined.php';" />
      </td></tr>

      </table></p></form>
<?php
   }


/*____________________________________________________________________DELETE_*/
} else if( $action == 'delete' ) {
   $info = get_joined_info( $_GET['id'] );
   $dir = get_setting( 'joined_images_dir' );
   @unlink( $dir . $info['imagefile'] ); // delete image if present

   $success = delete_joined( $_GET['id'] );
   if( $success )
      echo '<p class="success">Fanlisting <i>' . $info['subject'] . '</i>' .
         ' deleted.</p>';
   else
      echo '<p class="error">Error deleting fanlisting <i>' .
         $info['subject'] . '</i>.</p>';


/*______________________________________________________________________EDIT_*/
} else if( $action == 'edit' ) {
   $show_edit_form = true;
   $show_default = false;

   if( isset( $_POST['done'] ) ) {
      if( $_POST['catid'] == '' || $_POST['url'] == '' ||
         $_POST['subject'] == '' )
         echo '<p class="error">You cannot leave the category, url, ' .
            'and subject fields blank.</p>';
      else {
         if( $_POST['image_change'] == 'delete' ) {
            $info = get_joined_info( $_POST['id'] );
            $dir = get_setting( 'joined_images_dir' );
            @unlink( $dir . $info['imagefile'] );
            $image_deleted = edit_joined( $_POST['id'], 'null' );
         } else if( $_POST['image_change'] == 'yes' &&
            $_FILES['image']['name'] ) {
            $info = get_joined_info( $_POST['id'] );
            $dir = get_setting( 'joined_images_dir' );
            @unlink( $dir . $info['imagefile'] );
            $filename = $_POST['id'] . '_' . $_FILES['image']['name'];
            $uploaded = @move_uploaded_file( $_FILES['image']['tmp_name'],
               $dir . $filename );
            if( !$uploaded ) {
               // try chmodding the $dir to 755 and then reuploading
               @chmod( $dir, 0755 );
               $uploaded = @move_uploaded_file( $_FILES['image']['tmp_name'],
                  $dir . $filename );
            }
            if( !$uploaded ) {
               // 755 doesn't work, so we chmod to 777 and then go back
               @chmod( $dir, 0777 );
               $uploaded = @move_uploaded_file( $_FILES['image']['tmp_name'],
                  $dir . $filename );
               @chmod( $dir, 0755 ); // 777 is unsafe
            }
            if( $uploaded ) {
               // chmod the file to 644
               @chmod( $dir . $filename, 0644 );
               $image_changed = edit_joined( $_POST['id'], $filename );
            } else {
               $image_changed_error = edit_joined( $_POST['id'], 'null' );
            }
         }
         $pending = 1;
         if( isset( $_POST['approved'] ) && $_POST['approved'] == 1 )
            $pending = '0';
         $changed = edit_joined( $_POST['id'], '', $_POST['catid'],
            $_POST['url'], $_POST['subject'], $_POST['desc'],
            $_POST['comments'], $pending );
         if( isset( $image_deleted ) && $image_deleted )
            echo '<p class="success">Image successfully deleted ';
         if( isset( $image_changed ) && $image_changed )
            echo '<p class="success">Image successfully changed ';
         if( isset( $image_changed_error ) && $image_changed_error )
            echo '<p class="error">There was an error changing the image ';
         if( !isset( $image_deleted ) && !isset( $image_changed ) &&
            !isset( $image_changed_error ) )
            echo '<p class="success">No image changes were made ';
         if( $changed ) {
            $show_default = true;
            $show_edit_form = false;
            echo 'and the fanlisting information has been successfully ' .
               'updated.';
         } else {
            echo 'but there were errors updating other information. Please ' .
               'try again.';
         }
         if( isset( $image_changed_error ) && $image_changed_error ) {
            echo ' Please make sure that your joined images folder path is ' .
               'correct (<code>' . $dir . '</code>) and it is CHMODed to 755.';
         }
         echo '</p>';
      }
   }

   if( $show_edit_form ) {
      $info = get_joined_info( $_REQUEST['id'] );
?>
      <p>The current values of the joined listing is shown in the form
      below. Please change these values as needed and click on "Edit this
      listing".</p>

      <form action="joined.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit" />
      <input type="hidden" name="done" value="yes" />
      <input type="hidden" name="id" value="<?php echo $_REQUEST['id'] ?>" />

      <table>

      <tr><td class="important" valign="top">
      Listing Category
      </td><td>
      <select name="catid[]" multiple="multiple" size="5">
<?php
      $cats = enth_get_categories();
      $options = array();
      foreach( $cats as $cat ) {
         $optiontext = $cat['catname'];
         if( count( $ancestors =
            array_reverse( get_ancestors( $cat['catid'] ) ) ) > 1 ) {
            // get ancestors
            $text = '';
            foreach( $ancestors as $a )
               $text .= get_category_name( $a ) . ' > ';
            $optiontext = rtrim( $text, ' > ' );
            $optiontext = str_replace( '>', '&raquo;', $optiontext );
         }
         $options[] = array( 'text' => $optiontext, 'id' => $cat['catid'] );
      }
      usort( $options, 'category_array_compare' );
      $selected = explode( '|', $info['catid'] );
      foreach( $options as $o ) {
         echo '<option value="' . $o['id'];
         if( in_array( $o['id'], $selected ) )
            echo '" selected="selected';
         echo '">' . $o['text'] . '</option>';
      }
?>
      </select>
      </td></tr>

      <tr class="rowshade"><td class="important">
      Fanlisting URL
      </td><td>
      <input type="text" name="url" value="<?php echo $info['url'] ?>" />
      </td></tr>

      <tr><td class="important">
      Subject
      </td><td>
      <input type="text" name="subject" value="<?php echo $info['subject'] ?>" />
      </td></tr>

      <tr class="rowshade"><td>
      Description
      </td><td>
      <textarea name="desc" rows="5" cols="50"><?php echo $info['desc'] ?></textarea>
      </td></tr>

      <tr><td>
      Comments
      </td><td>
      <textarea name="comments" rows="5" cols="50"><?php echo $info['comments']
         ?></textarea>
      </td></tr>

      <tr class="rowshade"><td rowspan="3">
      Image
      </td><td>
<?php
      $dir = get_setting( 'joined_images_dir' );
      if( $info['imagefile'] == '' || !is_file( $dir . $info['imagefile'] ) )
         echo 'No image specified.';
      else {
         $root_web = get_setting( 'root_path_web' );
         $root_abs = get_setting( 'root_path_absolute' );
         $image = @getimagesize( $dir . $info['imagefile'] );
         $dir = str_replace( $root_abs, $root_web, $dir );
         $dir = str_replace( '\\', '/', $dir );
         // in case getimagesize() failed... do this
         if( !is_array( $image ) )
            echo 'Error retrieving image.';
         else
            echo '<img src="' . $dir . $info['imagefile'] . '" ' . $image[3] .
               ' border="0" alt="" />';
      }
?>
      </td></tr><tr class="rowshade"><td>
      <input type="radio" name="image_change" value="no" checked="checked" />
         Leave as it is<br />
      <input type="radio" name="image_change" value="delete" /> Delete
         image<br />
      <input type="radio" name="image_change" value="yes" /> Change with:
      </td></tr><tr class="rowshade"><td>
      <input type="file" name="image" />
      </td></tr>

      <tr><td colspan="2" class="right">
      Approved already?
<?php
      if( $info['pending'] != '1' )
         echo '<input type="checkbox" checked="checked" name="approved" ' .
            'value="1" />';
      else
         echo '<input type="checkbox" name="approved" value="1" />';
?>    
      <input type="submit" value="Edit this listing" />
      <input type="reset" value="Reset form" />
      <input type="button" value="Cancel"
         onclick="javascript:window.location='joined.php';" />
      </td></tr>

      </table></p></table>
<?php
   }
}


if( $show_default ) {
?>
   <div class="submenu">
   <a href="joined.php?action=add">Add</a>
   <a href="joined.php?action=template">Template</a>
   </div>

   <p>Via this page, you can manage the listings you have joined. These will
   show up on your collective's joined page when you have set the page up.
   To add a listing, click on the "Add" submenu item; to modify your joined
   listing template, click on the "Template" submenu item. Listings that
   are still pending approval have a red "X" on the left.</p>

   <form action="joined.php" method="get">
   <input type="hidden" name="dosearch" value="now" />

   <p class="center">
   <input type="text" name="search" />
   <select name="status" value="">
   <option value="">All</option>
   <option value="pending">Pending</option>
   <option value="approved">Approved</option>
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
         $ids = get_joined( $_GET['status'], $start, 'id' );
         $total = count( get_joined( $_GET['status'] ) );
      } else { // search!
         $status = '';
         if( isset( $_GET['status'] ) )
            $status = $_GET['status'];
         $ids = search_joined( $_GET['search'], $status, $start );
         $total = count( search_joined( $_GET['search'], $status ) );
      }
   } else {
      $ids = get_joined( 'all', $start, 'id' );
      $total = count( get_joined() );
   }
?>
   <table>

   <tr><th></th><th>
   Subject/URL
   </th><th>
   Categories
   </th><th>
   Image
   </th><th>
   Action
   </th></tr>
<?php
   $shade = false;
   foreach( $ids as $id ) {
      $info = get_joined_info( $id );
      $class = '';
      if( $shade ) {
         $class = ' class="rowshade"';
         $shade = false;
      } else $shade = true;

      $dir = get_setting( 'joined_images_dir' );
      $root_web = get_setting( 'root_path_web' );
      $root_abs = get_setting( 'root_path_absolute' );
      $image = ( $info['imagefile'] && is_file( $dir . $info['imagefile'] ) )
         ? @getimagesize( $dir . $info['imagefile'] ) : '';
      $dir = str_replace( $root_abs, $root_web, $dir );

      $target = '_self';

      if( $info['pending'] == 1 )
         echo '<tr' . $class . '><td class="important"><b>x</b></td>';
      else
         echo "<tr$class><td></td>";
?>
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
      if( $info['imagefile'] != '' )
         echo '<td class="center"><img src="' . $dir . $info['imagefile'] .
            '" ' . ( ( isset( $image[3] ) ) ? $image[3] : '' ) .
            'border="0" alt="" /></td>';
      else
         echo '<td class="center">x</td>';
?>
      <td class="center">
      <a href="joined.php?action=edit&id=<?php echo $id ?>"><img src="edit.gif"
         width="42" height="19" border="0" alt=" edit" title=" edit" /></a>
      <a href="joined.php?action=delete&id=<?php echo $id ?>"
         onclick="go=confirm('Are you sure you want to delete the <?php echo addslashes( $info['subject'] ) ?> fanlisting?'); return go;"><img
         src="delete.gif" width="42" height="19" border="0" alt=" delete"
         title=" delete" /></a>
      </td></tr>
<?php
   }
   echo '</table>';

   $page_qty = $total / get_setting( 'per_page' );
   $url = $_SERVER['REQUEST_URI'];

   $url = 'joined.php';
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
