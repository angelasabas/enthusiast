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
require_once( 'mod_settings.php' );
require_once( 'mod_categories.php' );

$show_default = true;
$action = '';
if( isset( $_REQUEST['action'] ) )
   $action = $_REQUEST['action'];

echo '<h1>Manage Collective Categories</h1>';


/*______________________________________________________________________ADD__*/
if( $action == 'add' ) {
   $show_default = false;
   $show_form = true;

   if( isset( $_POST['done'] ) && $_POST['done'] == 'yes' ) {
      if( isset( $_POST['catname'] ) && $_POST['catname'] != '' ) {
         $success = add_category( $_POST['catname'], $_POST['parent'] );
         if( $success ) {
            echo '<p class="success">Category <i>' . $_POST['catname'] .
               '</i> added.</p>';
            $show_form = false;
            $show_default = true;
         } else
            echo '<p class="error">Error adding category. ' .
               'Please try again.</p>';
      }
   }

   if( $show_form ) {
?>
      <p>
      To add a category, select the parent category (if applicable)
      from the dropdown, enter the category name, and click
      "Add this Category".
      </p>

      <form action="categories.php" method="post">
      <input type="hidden" name="action" value="add" />
      <input type="hidden" name="done" value="yes" />

      <table>

      <tr><td>
      Parent Category
      </td><td>
      <select name="parent">
      <option value="0">(No parent)</option>
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
               $text .= ( $text ) ? ' > ' . get_category_name( $a ) : get_category_name( $a );
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

      <tr class="rowshade"><td>
      Category name
      </td><td>
      <input type="text" name="catname" />
      </td></tr>

      <tr><td colspan="2" class="right">
      <input type="submit" value="Add this category" />
      <input type="reset" value="Clear form" />
      <input type="button" value="Cancel"
         onclick="javascript:window.location='categories.php';" />
      </td></tr>

      </table></form>
<?php
   }




/*___________________________________________________________________DELETE__*/
} else if( $action == 'delete' ) {
   if( isset( $_GET['id'] ) && $_GET['id'] != '' ) {
      // make sure first that there are no children -- delete them first
      if( count( get_enth_category_children( $_GET['id'] ) ) == 0 ) {
         $cat = get_category_name( $_GET['id'] );
         $success = delete_category( $_GET['id'] );
         if( $success )
            echo '<p class="success">Category <i>'. $cat .'</i> deleted.</p>';
         else
            echo '<p class="error">Error deleting category. ' .
               'Please try again.</p>';
      } else
         echo '<p class="error">There are categories that are assigned as ' .
            'children to this category. Please delete them before ' .
            'attempting to delete this category.</p>';
   }


/*_____________________________________________________________________EDIT__*/
} else if( $action == 'edit' ) {
   $show_edit_form = true;
   $show_default = false;

   if( isset( $_POST['catname'] ) && $_POST['catname'] != '' ) {
      $old = get_category_name( $_POST['id'] );
      $success = edit_category( $_POST['id'], $_POST['catname'],
         $_POST['parentid'] );
      if( $success ) {
         echo '<p class="success">Category <i>' . $old .
            '</i> successfully changed.</p>';
         $show_edit_form = false;
         $show_default = true;
      } else
         echo '<p class="error">Error editing <i>' . $old .
            ' </i> category. Please try again.</p>';
   } // end if catname is present

   if( $show_edit_form ) {
      $old = get_category_name( $_GET['id'] );
      $parent = get_category_parent( $_GET['id'] );
?>
      <p>You can edit the category info for <i><?php echo $old ?></i> via this page.
      Edit the pre-loaded information in the form below, then click "Edit this
      Category".</p>

      <form method="post" action="categories.php">
      <input type="hidden" name="action" value="edit" />
      <input type="hidden" name="id" value="<?php echo $_GET['id'] ?>" />

      <table>

      <tr><td>
      Category name
      </td><td>
      <input type="text" name="catname" value="<?php echo $old ?>" />
      </td></tr>

      <tr class="rowshade"><td>
      Parent category
      </td><td>
      <select name="parentid">
      <option value=""></option>
<?php
      $cats = enth_get_categories();
      $options = array();
      foreach( $cats as $cat ) {
         if( $cat['catid'] == $_GET['id'] )
            continue;
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
      foreach( $options as $o ) {
         echo '<option value="' . $o['id'];
         if( $o['id'] == $parent ) echo '" selected="selected';
         echo '">' . $o['text'] . '</option>';
      }
?>
      </select>
      </td></tr>

      <tr><td colspan="2" class="right">
      <input type="submit" value="Edit this category" />
      <input type="reset" value="Clear form" />
      <input type="button" value="Cancel"
         onclick="javascript:window.location='categories.php';" />
      </td></tr>

      </table>
      </form>
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
      <p>You can edit the way the categories will be shown on your
      collective pages if the category list is to be shown. Please note that
      the header and footer entries will NOT be shown if you will be using
      the dropdown category menu. If you need help, click on the 'help'
      button.</p>

      <form action="categories.php" method="post">
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



} // end of actions
	

/*__________________________________________________________________DEFAULT__*/
if( $show_default ) {
?>
   <div class="submenu">
   <a href="categories.php?action=add">Add</a>
   <a href="categories.php?action=template">Template</a>
   </div>

   <p>The existing categories are listed below. You may search for the
   categories using the form below. Categories are listed in alphabetical
   order of the category name (not by parent).</p>

   <form action="categories.php" method="get">
   <input type="hidden" name="dosearch" value="now" />

   <p class="center">
   <input type="text" name="search" />
   <input type="submit" value="Search" />
   </p>

   </form>

<?php
   $start = ( isset( $_REQUEST['start'] ) ) ? $_REQUEST['start'] : '0';
   $search = ( isset( $_GET['search'] ) ) ? $_GET['search'] : '';

   $total = 0;
   $cats = enth_get_categories( $search, $start );
   $total = count( enth_get_categories( $search ) );

   // fix cats array
   foreach( $cats as $i => $c ) {
      $cats[$i]['text'] = '';
      if( $ancestors = array_reverse( get_ancestors( $c['catid'] ) ) ) {
         // get ancestors
         $text = '';
         foreach( $ancestors as $a )
            $text .= ( strlen( $text ) > 0 ) ? ' &raquo; ' . get_category_name( $a ) : get_category_name( $a );
         $cats[$i]['text'] .= $text;
      } else $cats[$i]['text'] = get_category_name( $c['catid'] );
   }

   usort( $cats, 'category_array_compare' );
?>
   <table>

   <tr><th>
   Category ID
   </th><th>
   Category
   </th><th>
   Action
   </th></tr>

<?php
   $shade = false;
   foreach( $cats as $cat ) {
      $class = ( $shade ) ? ' class="rowshade"' : '';
      $shade = !$shade;
      echo "<tr$class><td>" . $cat['catid'] . '</td><td>';

      $catstring = '';
      if( $ancestors = array_reverse( get_ancestors( $cat['catid'] ) ) ) {
         // get ancestors
         $text = '';
         foreach( $ancestors as $a )
            $text .= ( strlen( $text ) > 0 ) ? ' &raquo; ' . get_category_name( $a ) : get_category_name( $a );
         $catstring .= $text;
      } else $catstring = get_category_name( $cat['catid'] );

      echo $catstring . '</td><td class="center">';
      echo '<a href="?action=edit&id=' . $cat['catid'] . '">' .
         '<img src="edit.gif" width="42" height="19" alt=" edit" />' .
         '</a><a href="?action=delete&id=' . $cat['catid'] .
         '" onclick="go=confirm( \'Are you sure you want to delete category ' .
         $catstring . '?\' ); return go;"><img src="delete.gif" ' .
         'width="42" height="19" alt=" delete" /></a></td></tr>';
   }
   echo '</table>';

   $page_qty = $total / get_setting( 'per_page' );
   $url = $_SERVER['REQUEST_URI'];

   $url = 'categories.php';
   $connector = '?';
   foreach( $_GET as $key => $value )
      if( $key != 'start' && $key != 'PHPSESSID' ) {
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
