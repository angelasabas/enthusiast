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
require_once( 'mod_owned.php' );
require_once( 'mod_affiliates.php' );
require_once( 'mod_emails.php' );

$show_default = true;
$action = ( isset( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : '';
$listing = ( isset( $_REQUEST['listing'] ) )
   ? $_REQUEST['listing'] : 'collective';
echo '<h1>Manage Affiliates</h1>';

/*_______________________________________________________________________ADD_*/
if( $action == 'add' || $action == 'Add' ) {
   $show_default = false;
   $show_add_form = true;

   if( isset( $_POST['done'] ) ) { // form has been submitted
      if( !$_POST['url'] || !$_POST['title'] || !$_POST['email'] )
         echo '<p class="error">You have left out some required fields.' .
            '</p>';

      else { // go ahead and add
         $success = add_affiliate( $_POST['url'], $_POST['title'],
            $_POST['email'], $listing );
         if( !$success )
            echo '<p class="error">Error adding the affiliate.</p>';
         else {
            $show_add_form = false;
            $show_default = true;

            // upload file if there is
            if( $_FILES['image']['name'] != '' ) {
               $filename = $success . '_' . $_FILES['image']['name'];
               $dir = get_setting( 'affiliates_dir' );
               if( $listing != '' && $listing != 'collective' ) {
                  $info = get_listing_info( $listing );
                  $dir = $info['affiliatesdir'];
               }
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
               $edited = false;
               if( $upload_success ) {
                  // chmod the file to 644
                  @chmod( $dir . $filename, 0644 );

                  $edited = edit_affiliate( $success, $listing, $filename );
                  if( $edited )
                     echo '<p class="success">Affiliate added ' .
                        'successfully.</p>';
               } else if( !$upload_success || !$edited ) {
                  echo '<p class="error">Affiliate added ' .
                     'successfully, but there was an error ' .
                     'uploading/setting image. Please make sure your ' .
                     'affiliate folder path is correct (<code>' . $dir .
                     '</code>) and that it is CHMODed to 755.</p>';
               } // end of upload attempt

            } else { // if there is no image to upload
               echo '<p class="success">Affiliate added ' .
                  'successfully.</p>';
            }

            // email affiliate?
            if( isset( $_POST['sendemail'] ) &&
               $_POST['sendemail'] == 'yes' ) {
               $to = $_POST['email'];
               $email = parse_affiliate_add_email( $success, $listing );
               $from = '"' . get_setting( 'owner_name' ) . '"';
               if( $listing == 'collective' )
                  $from .= ' <' . get_setting( 'owner_email' ) . '>';
               else {
                  $info = get_listing_info( $listing );
                  $from .= ' <' . $info['email'] . '>';
               }

               // use send_email function
               $mail_sent = send_email( $to, $from, $email['subject'],
                  $email['body'] );

               if( $mail_sent === true ) {
                  echo '<p class="success">Affiliate successfully ' .
                     'emailed.</p>';
               } else {
                  echo '<p class="success">Affiliate emailing failed.</p>';
               }
            } // end email aff

         } // end if affiliate adding is successful
      } // end form submitted correctly
   } // end done is present

   if( $show_add_form ) {
      $url = '';
      $title = '';
      $email = '';
      $id = $listing;
      $to = 'Collective';
      
      if( isset( $_POST['url'] ) )
         $url = $_POST['url'];
      if( isset( $_POST['title'] ) )
         $title = $_POST['title'];
      if( isset( $_POST['email'] ) )
         $email = $_POST['email'];
      if( is_numeric( $id ) ) {
         $info = get_listing_info( $id );
         $to = $info['title'] . ': ' . $info['subject'] . ' ' .
            $info['listingtype'];
      } else
         $to = get_setting( 'collective_title' );
?>
      <p>This page allows you to add an affiliate to <i><?php echo $to ?></i>.
      Fill out the form below and click on "Add this affiliate".</p>

      <form action="affiliates.php" method="post"
         enctype="multipart/form-data">
      <input type="hidden" name="action" value="add" />
      <input type="hidden" name="done" value="yes" />
      <input type="hidden" name="listing" value="<?php echo $listing ?>" />

      <table>

      <tr><td class="important">
      Affiliate URL
      </td><td>
      <input type="text" name="url" value="<?php if ( $url ) { echo $url; } else { echo 'http://'; } ?>" />
      </td></tr>

      <tr class="rowshade"><td class="important">
      Site title
      </td><td>
      <input type="text" name="title" value="<?php echo $title ?>" />
      </td></tr>

      <tr><td class="important">
      Owner email
      </td><td>
      <input type="text" name="email" value="<?php echo $email ?>" />
      </td></tr>

      <tr class="rowshade"><td>
      Image
      </td><td>
      <input type="file" name="image" value="" />
      </td></tr>

      <tr><td>
      Send notification email?
      </td><td>
      <input type="checkbox" name="sendemail" value="yes" checked="checked" />
      Yes, send email
      </td></tr>

      <tr class="rowshade"><td colspan="2" class="right">
      <input type="submit" value="Add this affiliate" />
      <input type="button" value="Cancel"
         onclick="javascript:window.location='affiliates.php?listing=<?php echo $id ?>';" />
      </td></tr>

      </table></form>
<?php
   }
}


/*__________________________________________________________________TEMPLATE_*/
if( $action == 'template' ) {
   $show_default = false;
   $show_template_form = true;
   if( isset( $_POST['done'] ) ) {
      $update_header = update_setting( 'affiliates_template_header',
         $_POST['header'] );
      $update_template = update_setting( 'affiliates_template',
         $_POST['template'] );
      $update_footer = update_setting( 'affiliates_template_footer',
         $_POST['footer'] );

      $show_default = true;
      $show_template_form = false;
      echo '<p class="success">Templates successfully updated.</p>';
   }

   if( $show_template_form ) {
      $header = get_setting( 'affiliates_template_header' );
      $template = get_setting( 'affiliates_template' );
      $footer = get_setting( 'affiliates_template_footer' );

      $header_help = get_setting_desc( 'affiliates_template_header' );
      $template_help = get_setting_desc( 'affiliates_template' );
      $footer_help = get_setting_desc( 'affiliates_template_footer' );
?>
      <p>You can edit the way your collective affiliates will be shown via the
      templates below. If you need help, click on the 'help' button.</p>

      <form action="affiliates.php" method="post">
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
         onclick="javascript:window.location='affiliates.php';" />
      </td></tr>

      </table></form>
<?php
   }
}


/*____________________________________________________________________DELETE_*/
if( $action == 'delete' ) {
   if( is_numeric( $listing ) ) {
      // delete listing affiliate
      $info = get_listing_info( $_REQUEST['listing'] );
      $listingid = $info['listingid'];
      $dir = $info['affiliatesdir'];
      $aff = get_affiliate_info( $_GET['id'], $listingid );
   } else {
      // delete collective affiliate
      $aff = get_affiliate_info( $_GET['id'] );
      $dir = get_setting( 'affiliates_dir' );
   }
   @unlink( $dir . $aff['imagefile'] );

   $success = delete_affiliate( $_GET['id'], $listing );
   if( $success )
      echo '<p class="success">Affiliate <i>' . $aff['title'] . '</i>' .
         ' deleted.</p>';
   else
      echo '<p class="error">Error deleting affiliate <i>' .
         $aff['title'] . '</i>.</p>';
}


/*______________________________________________________________________EDIT_*/
if( $action == 'edit' ) {
   $show_edit_form = true;
   $show_default = false;

   if( isset( $_POST['done'] ) ) {
      if( $_POST['email'] == '' || $_POST['url'] == '' ||
         $_POST['title'] == '' )
         echo '<p class="error">You cannot leave the url, title and ' .
            'email fields blank.</p>';
      else {
         if( $_POST['image_change'] == 'delete' ) {
            $info = get_affiliate_info( $_POST['id'] );
            $dir = get_setting( 'affiliates_dir' );
            if( isset( $_REQUEST['listing'] ) && $_REQUEST['listing'] != '' &&
               $_REQUEST['listing'] != 'collective' ) {
               $listing = get_listing_info( $_REQUEST['listing'] );
               $dir = $listing['affiliatesdir'];
            }
            $image_deleted = @unlink( $dir . $info['imagefile'] );
            if( $image_deleted ) {
               $image_deleted = edit_affiliate( $_POST['id'], $_POST['listing'],
                  'null' );
            } else {
               echo '<p class="error">Error deleting the image from the ' .
                  'filesystem.</p>';
            }

         } else if( $_POST['image_change'] == 'yes' &&
            $_FILES['image']['name'] ) {
            $info = get_affiliate_info( $_POST['id'] );
            $dir = get_setting( 'affiliates_dir' );
            if( isset( $_REQUEST['listing'] ) && $_REQUEST['listing'] != '' &&
               $_REQUEST['listing'] != 'collective' ) {
               $listing = get_listing_info( $_REQUEST['listing'] );
               $dir = $listing['affiliatesdir'];
            }
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

               $image_changed = edit_affiliate( $_POST['id'],
                  $_POST['listing'], $filename );
            } else
               $image_changed_error = edit_affiliate( $_POST['id'],
                  $_POST['listing'], 'null' );
            }

         $changed = edit_affiliate( $_POST['id'], $_POST['listing'], '',
            $_POST['url'], $_POST['title'], $_POST['email'] );
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
            echo 'and the affiliate information has been ' .
               'successfully updated.';
            $listing = $_REQUEST['listing'];
         } else {
            echo 'but there were errors updating other information. Please ' .
               'try again.';
         }
         if( isset( $image_changed_error ) && $image_changed_error )
            echo ' Please make sure that your affiliate folder path is correct (<code>' .
               $dir . '</code>) and it is CHMODed to 755.';
         echo '</p>';
      }
   }

   if( $show_edit_form ) {
      $info = get_affiliate_info( $_REQUEST['id'] );
      $listing = array();
      if( isset( $_REQUEST['listing'] ) && $_REQUEST['listing'] != '' &&
         $_REQUEST['listing'] != 'collective' ) {
         $listing = get_listing_info( $_REQUEST['listing'] );
         $info = get_affiliate_info( $_REQUEST['id'], $_REQUEST['listing'] );
      }
?>
      <p>The current values of the affiliate is shown in the form
      below. Please change these values as needed and click on "Edit this
      affiliate".</p>

      <form action="affiliates.php" method="post"
         enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit" />
      <input type="hidden" name="done" value="yes" />
      <input type="hidden" name="id" value="<?php echo $_REQUEST['id'] ?>" />
      <input type="hidden" name="listing"
         value="<?php echo ( $_REQUEST['listing'] )
            ? $_REQUEST['listing'] : 'collective' ?>" />

      <table>

      <tr><td class="important">
      Affiliate URL
      </td><td>
      <input type="text" name="url" value="<?php echo $info['url'] ?>" />
      </td></tr>

      <tr class="rowshade"><td class="important">
      Site title
      </td><td>
      <input type="text" name="title" value="<?php echo $info['title'] ?>" />
      </td></tr>

      <tr><td class="important">
      Owner email
      </td><td>
      <input type="text" name="email" value="<?php echo $info['email'] ?>" />
      </td></tr>

      <tr class="rowshade"><td rowspan="3">
      Image
      </td><td>
<?php
      if( $info['imagefile'] == '' )
         echo 'No image specified.';
      else {
         $dir = get_setting( 'affiliates_dir' );
         if( count( $listing ) > 0 )
            $dir = $listing['affiliatesdir'];
         if( is_file( $dir . $info['imagefile'] ) ) {
            $image = @getimagesize( $dir . $info['imagefile'] );
            // make sure $image is an array, in case getimagesize() failed
            if( !is_array( $image ) ) {
               echo 'Error retrieving image.';
            } else {
               $root_web = get_setting( 'root_path_web' );
               $root_abs = get_setting( 'root_path_absolute' );
               $dir = str_replace( $root_abs, $root_web, $dir );
               $dir = str_replace( '\\', '/', $dir );
               echo '<img src="' . $dir . $info['imagefile'] . '" ' . $image[3] .
                  ' border="0" alt="" />';
            }
         }
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
      <input type="submit" value="Edit this affiliate" />
      <input type="reset" value="Reset original values" />
      <input type="button" value="Cancel"
         onclick="javascript:window.location='affiliates.php?listing=<?php echo ( $_REQUEST['listing'] ) ? $_REQUEST['listing'] : 'collective'
         ?>';" />
      </td></tr>

      </table></form>
<?php
   }
}



/*__________________________________________________________________DEFAULT__*/
if( $show_default ) {
   $info = array();
   if( $listing == 'collective' ) {
      $info['title'] = get_setting( 'collective_title' );
   } else {
      $info = get_listing_info( $listing );
   }
?>
   <div class="submenu">
   <a href="affiliates.php?action=add&listing=<?php echo $listing ?>">Add</a>
   <a href="<?php echo ( $listing == 'collective' )
   ? 'affiliates.php?action=template'
   : "owned.php?action=edit&id=$listing&type=templates"; ?>">Template</a>
   <a href="emails.php?action=affiliates&id=<?php echo $listing ?>">Email</a>
   </div>

   <form action="affiliates.php" method="get">

   <p class="right"> Manage:
   <select name="listing">
   <option value="collective">Collective affiliates</option>
<?php
   $owned = get_owned();
   foreach( $owned as $id ) {
      $own = get_listing_info( $id );
      if( $own['affiliates'] == 1 ) {
         echo '<option value="' . $id;
         if( isset( $_REQUEST['listing'] ) && $_REQUEST['listing'] == $id )
            echo '" selected="selected';
         echo '">' . $own['subject'] . ' ' . $own['listingtype'] .
            ' affiliates </option>';
      }
   }
?>
   </select>
   <input type="submit" value="Manage" />

   </p></form>

   <p>
   The existing affiliates for <i><?php echo $info['title'] ?></i> are listed below.
   To add an affiliate, click on the "Add" submenu item; if you wish to modify
   your affiliates' template; click on the "Template" submenu item; if you wish
   to email all affiliates, click on the "Email" submenu item.</p>

   <p>If you wish to manage affiliates for a different site under the
   collective, select the site from the dropdown above. Note that only listings
   where you have defined the affiliates feature to be enabled are
   shown above.</p>

   <table>
   <tr><th>
   Site name
   </th><th>
   Image
   </th><th>
   Action
   </th></tr>
<?php
   $start = ( isset( $_REQUEST['start'] ) ) ? $_REQUEST['start'] : '0';
   $affiliates = get_affiliates( $listing, $start );
   $total = count( get_affiliates( $listing ) );

   if( count( $affiliates ) == 0 )
      echo '<tr><td colspan="3">No affiliates yet.</td></tr>';

   $shade = false;
   foreach( $affiliates as $aff ) {
      $class = ( $shade ) ? ' class="rowshade"' : '';
      $shade = !$shade;
      echo "<tr$class><td>";
      echo '<a href="' . $aff['url'] . '">' . $aff['title'] . '</a>';
      echo '</td><td>';
      if( $aff['imagefile'] == '' )
         echo 'x</td><td>';
      else {
         $dir = get_setting( 'affiliates_dir' );
         if( $listing != '' && ctype_digit( $listing ) )
            $dir = $info['affiliatesdir'];
         $root_web = get_setting( 'root_path_web' );
         $root_abs = get_setting( 'root_path_absolute' );
         if( is_file( $dir . $aff['imagefile'] ) ) {
            $image = @getimagesize( $dir . $aff['imagefile'] );
            $dir = str_replace( $root_abs, $root_web, $dir );
            $dir = str_replace( '\\', '/', $dir );
            if( !is_array( $image ) )
               echo 'x</td></tr>';
            else
               echo '<img src="' . $dir . $aff['imagefile'] . '" ' . $image[3] .
                  ' border="0" alt="" /></td><td>';
         } else
            echo 'x</td><td>';
      }
?>
      <a href="affiliates.php?action=edit&listing=<?php echo $listing
         ?>&id=<?php echo $aff['affiliateid'] ?>"><img src="edit.gif"
         width="42" height="19" alt=" edit" /></a>
      <a href="emails.php?action=directemail&address=<?php echo $aff['email']
         ?>&listing=<?php echo $listing ?>&id=<?php echo $aff['affiliateid']
         ?>"><img src="email.gif" width="42" height="19" alt=" email" /></a>
      <a href="affiliates.php?action=delete&listing=<?php echo $listing
         ?>&id=<?php echo $aff['affiliateid'] ?>"
         onclick="go = confirm( 'Are you sure you want to delete <?php echo addslashes( $aff['title'] ) ?>?' ); return go;"><img
         src="delete.gif" width="42" height="19" alt=" delete" /></a>
<?php
      echo '</td></tr>';
   }
?>
   </table></p>
<?php
   $page_qty = $total / get_setting( 'per_page' );
   $url = $_SERVER['REQUEST_URI'];

   $url = "affiliates.php?listing=$listing";
   foreach( $_GET as $key => $value )
      if( $key != 'start' && $key != 'PHPSESSID' && $key != 'listing' ) {
         $url .= "&amp;$key=$value";
      }

   if( $page_qty > 1 )
      echo '<p class="center">Go to page: ';

   $i = 1;
   while( ( $i <= $page_qty + 1 ) && $page_qty > 1 ) {
      $start_link = ( $i - 1 ) * get_setting( 'per_page' );
      echo '<a href="' . $url . '&start=' . $start_link . '">' .
      $i . '</a> ';
      $i++;
   }
}
require_once( 'footer.php' );
?>