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
require_once( 'header.php' );
require_once( 'config.php' );
require_once( 'mod_errorlogs.php' );
require_once( 'mod_affiliates.php' );
require_once( 'mod_emails.php' );
require_once( 'mod_owned.php' );
require_once( 'mod_members.php' );
require_once( 'mod_settings.php' );

if( !class_exists( 'Mail' ) ) {
   require_once( 'Mail.php' );
}

$show_default = true;
echo '<h1>Email Templates</h1>';
$action = ( isset( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : '';


/*_______________________________________________________________________ADD_*/
if( $action == 'add' ) {
   $show_default = false;
   $show_form = true;

   if( isset( $_POST['done'] ) && $_POST['done'] == 'yes' ) {
      if( $_POST['templatename'] && $_POST['subject'] && $_POST['content'] ) {
         $success = add_template( $_POST['templatename'], $_POST['subject'],
            $_POST['content'] );
         if( $success ) {
            $show_default = true;
            $show_form = false;
            echo '<p class="success">Template <i>' . stripslashes(
               $_POST['templatename'] ) . '</i> added successfully.</p>';
          }

       } else
         echo '<p class="error">All fields are required for adding a ' .
            'template.</p>';
   } // end if form has been submitted

   if( $show_form ) {
?>
      <p>You can add email templates for future use on this page. Fill out the
      entire form and click "Add this template".</p>

      <p>Templates, when parsed for sending, will use template variables for
      the listing it will be sent for.</p>

      <form action="emails.php" method="post">
      <input type="hidden" name="action" value="add" />
      <input type="hidden" name="done" value="yes" />

      <table>

      <tr><td class="important">
      Template name
      </td><td>
      <input type="text" name="templatename" />
      </td></tr>

      <tr class="rowshade"><td class="important">
      Subject
      </td><td>
      <input type="text" name="subject" />
      </td></tr>

      <tr><td class="important">
      Content
      </td><td>
      <textarea name="content" rows="7" cols="60"></textarea>
      </td></tr>

      <tr class="rowshade"><td colspan="2" class="right">
      <input type="submit" value="Add this template" />
      <input type="button" value="Cancel"
         onclick="javascript:window.location='emails.php';" />
      </td></tr>

      </table>
      </form>
<?php
   }
}


/*______________________________________________________________________EDIT_*/
if( $action == 'edit' ) {
   $show_default = false;
   $show_form = true;

   if( isset( $_POST['done'] ) && $_POST['done'] == 'yes' ) {
      if( $_POST['templatename'] && $_POST['subject'] && $_POST['content'] ) {
         $success = edit_template( $_POST['id'], $_POST['templatename'],
            $_POST['subject'], $_POST['content'] );
         if( $success ) {
            $show_default = true;
            $show_form = false;
            echo '<p class="success">Template <i>' . $_POST['templatename'] .
               '</i> edited successfully.</p>';
         }
      } else
         echo '<p class="error">All fields are required for email ' .
            'templates.</p>';
   }

   if( $show_form ) {
      $info = get_template_info( $_REQUEST['id'] );
?>
      <p>You can edit the <i><?php echo $info['templatename'] ?></i> template on
      this page. Its current values are shown below; when you're done
      modifying them, click on "Edit this template".</p>

      <form action="emails.php" method="post">
      <input type="hidden" name="action" value="edit" />
      <input type="hidden" name="id" value="<?php echo $_REQUEST['id'] ?>" />
      <input type="hidden" name="done" value="yes" />

      <table>

      <tr><td>
      Template name
      </td><td>
      <input type="text" name="templatename" value="<?php echo $info['templatename']
         ?>" />
      </td></tr>

      <tr class="rowshade"><td>
      Subject
      </td><td>
      <input type="text" name="subject" value="<?php echo $info['subject'] ?>" />
      </td></tr>

      <tr><td>
      Content
      </td><td>
      <textarea name="content" rows="7" cols="60"><?php echo $info['content']
         ?></textarea>
      </td></tr>

      <tr class="rowshade"><td colspan="2" class="right">
      <input type="submit" value="Edit this template" />
      <input type="button" value="Cancel"
         onclick="javascript:window.location='emails.php';" />
      </td></tr>

      </table></p>
      </form>
<?php
      }
   }


/*____________________________________________________________________DELETE_*/
if( $action == 'delete' ) {
   $info = get_template_info( $_REQUEST['id'] );
   if( $info['deletable'] == 0 ) {
?>
      <p class="error">The <i><?php echo $info['templatename'] ?></i> template is
      a system template and cannot be deleted.</p>
<?php
   } else {
      $success = delete_template( $_REQUEST['id'] );
      if( $success ) {
?>
         <p class="success">Template <i><?php echo $info['templatename'] ?></i>
         deleted successfully.</p>
<?php
      } else
         echo '<p class="error">Error deleting template.</p>';
   }
}



/*_______________________________________________________________DIRECTEMAIL_*/
if( $action == 'directemail' ) {
   $show_default = false;
   $show_form = true;

   $to = $_REQUEST['address'];
   $from = '';
   $subject = '';
   $body = '';
   if( !isset( $_REQUEST['listing'] ) )
      $_REQUEST['listing'] = 'collective';

   if( $_REQUEST['listing'] == 'collective' ) {
      $from = '"' . html_entity_decode( get_setting( 'collective_name' ),
         ENT_QUOTES ) . '" <' . get_setting( 'owner_email' ) . '>';
   } else {
      $info = get_listing_info( $_REQUEST['listing'] );
      $from = '"' . html_entity_decode( $info['title'], ENT_QUOTES ) .
         '" <' . $info['email'] . '>';
   }

   if( isset( $_POST['send'] ) && $_POST['send'] == 'yes' ) {
      if( $_POST['emailtemplate'] == 'no' ) {
         // parse the body and subject
         $parsed = parse_email_text( $_POST['emailsubject'],
            $_POST['emailbody'], $to, $_POST['listing'], $_POST['id'] );

         if( $_POST['listing'] == 'collective' ) {
            $subject = html_entity_decode( get_setting( 'collective_name' ),
               ENT_QUOTES ) . ': ' . $parsed['subject'];
         } else {
            $info = get_listing_info( $_POST['listing'] );
            $subject = html_entity_decode( $info['title'], ENT_QUOTES ) .
               ': ' . $parsed['subject'];
         }
         $body = $parsed['body'];

      } else { // use predefined template
         $sendthis = parse_template( $_POST['emailtemplate'], $to,
            $_POST['listing'], $_POST['id'] );
         $subject = $sendthis['subject'];
         $body = $sendthis['body'];
      }

      // use send_email function
      $mail_sent = send_email( $to, $from, stripslashes( $subject ),
         stripslashes( $body ) );

      if( $mail_sent === true ) {
         echo '<p class="success">Email to <i>' . $to . '</i> successfully' .
            ' sent.</p>';
      } else {
         echo '<p class="error">Email sending to <i>' . $to .
            '</i> failed. Please try again.</p>';
      }
   }

   if( $show_form ) {
      $info = array();
      $listing = 'collective';
      $email = $_REQUEST['address'];
      if( isset( $_REQUEST['listing'] ) && $_REQUEST['listing'] != '' &&
         $_REQUEST['listing'] != 'collective' ) {
         $info = get_listing_info( $_REQUEST['listing'] );
         $listing = $_REQUEST['listing'];
      } else
         $info['title'] = get_setting( 'collective_title' );
?>
      <p>You can send an email to <i><?php echo $_REQUEST['address'] ?></i> via this
      page. Enter the email or the template below, and click "Send email".</p>

      <form action="emails.php" method="post">
      <input type="hidden" name="action" value="directemail" />
      <input type="hidden" name="listing" value="<?php echo $listing ?>" />
      <input type="hidden" name="address" value="<?php echo $email ?>" />
      <input type="hidden" name="send" value="yes" />
      <input type="hidden" name="id" value="<?php echo $_REQUEST['id'] ?>" />

      <table>

      <tr><th colspan="2">
      For <?php echo $info['title'] ?>
      </th></tr>

      <tr><td>
      From
      </td><td>
      <?php echo str_replace( '<', '&lt;',
         str_replace( '>', '&gt;', $from ) ) ?>
      </td></tr>

      <tr><td>
      Email
      </td><td>
      <i><?php echo $email ?></i>
      </td></tr>

      <tr class="rowshade"><td>
      Subject and Body
      </td><td style="text-align: left;">
      <?php echo $info['title'] ?>: <input type="text" name="emailsubject" /><br />
      <textarea name="emailbody" rows="7" cols="50"></textarea>
      </td></tr>

      <tr><td>
      Use Template?
      </td><td>
      <select name="emailtemplate">
      <option value="no">No, use the textarea</option>
<?php
      $templates = get_email_templates();
      foreach( $templates as $t ) {
         $info = get_template_info( $t );
         echo '<option value="' . $t . '">' . $info['templatename'] .
            '</option>';
      }
?>
      </select>
      </td></tr>

      <tr class="rowshade"><td colspan="2" class="right">
      <input type="submit" value="Send email" />
      </td></tr>

      </table></form>
<?php
   }
}




/*________________________________________________________________AFFILIATES_*/
if( $action == 'affiliates' ) {
   $show_default = false;
   $show_form = true;

   $from = '';
   $info = null;
   $id = 'collective';
   if( $_REQUEST['id'] == 'collective' ) {
      $info['title'] = get_setting( 'collective_name' );
      $from = '"' . html_entity_decode( $info['title'], ENT_QUOTES ) .
         '" <' . get_setting( 'owner_email' ) . '>';
   } else {
      $info = get_listing_info( $_REQUEST['id'] );
      $id = $_REQUEST['id'];
      $from = '"' . html_entity_decode( $info['title'], ENT_QUOTES ) .
         '" <' . $info['email'] . '>';
   }

   if( isset( $_POST['send'] ) && $_POST['send'] == 'yes' ) {
      $headers = '';
      $subject = '';
      $body = '';

      // get all affiliates
      $aff = get_affiliates( $_POST['id'] );
      foreach( $aff as $a ) {
         if( $_POST['emailtemplate'] == 'no' ) {
            $parsed = parse_email_text( $_POST['emailsubject'],
               $_POST['emailbody'], $a['affiliateid'], $_POST['id'] );
            $subject = $parsed['subject'];
            $body = $parsed['body'];

         } else { // use predefined template
            $sendthis = parse_template( $_POST['emailtemplate'], 
               $a['affiliateid'], $_POST['id'] );
            $subject = $sendthis['subject'];
            $body = $sendthis['body'];
         }

         // use send_email function
         $success = send_email( $a['email'], $from, stripslashes( $subject ),
            stripslashes( $body ) );

         if( $success !== true ) {
            echo '<p class="error">Email sending to <i>' . $a['email'] .
               '</i> failed. Please try sending to that address again.</p>';
         }
      }
      echo '<p class="success">Email sending done.</p>';
   }

   if( $show_form ) {
      $title = $info['title'];
      if( isset( $_REQUEST['id'] ) && $_REQUEST['id'] != '' &&
         $_REQUEST['id'] != 'collective' ) {
         $title = ( ( $info['title'] ) ? $info['title'] . ': ' : '' ) .
            $info['subject'] . ' ' . $info['listingtype'];
      }
?>
      <p>You can send an email to all affiliates of <i><?php echo $title ?></i> via
      this page. Enter the email body or the template below, and click "Send
      email".</p>

      <form action="emails.php" method="post">
      <input type="hidden" name="action" value="affiliates" />
      <input type="hidden" name="id" value="<?php echo $id ?>"/>
      <input type="hidden" name="send" value="yes" />

      <table>

      <tr><td>
      From
      </td><td>
      <?php echo str_replace( '<', '&lt;', str_replace( '>', '&gt;', $from ) ) ?>
      </td></tr>

      <tr class="rowshade"><td>
      Subject and Body
      </td><td style="text-align: left;">
      <input type="text" name="emailsubject" /><br /><br />
      <textarea name="emailbody" rows="7" cols="50"></textarea>
      </td></tr>

      <tr><td>
      Use Template?
      </td><td>
      <select name="emailtemplate">
      <option value="no">No, use the textarea</option>
<?php
      $templates = get_email_templates();
      foreach( $templates as $t ) {
         $info = get_template_info( $t );
         echo '<option value="' . $t . '">' . $info['templatename'] .
            '</option>';
         }
?>
      </select>
      </td></tr>

      <tr class="rowshade"><td colspan="2" class="right">
      <input type="submit" value="Send email" />
      </td></tr>

      </table></form>
<?php
   }
}





/*___________________________________________________________________MEMBERS_*/
if( $action == 'members' ) {
   $show_default = false;
   $show_form = true;

   $info = get_listing_info( $_REQUEST['id'] );
   $from = '"' . html_entity_decode( $info['title'], ENT_QUOTES ) .
      '" <' . $info['email'] . '>';

   if( isset( $_POST['send'] ) && $_POST['send'] == 'yes' ) {
      $subject = '';
      $body = '';

      // get all members
      $members = get_members( $_POST['id'], 'approved' );
      foreach( $members as $mem ) {
         if( $_POST['emailtemplate'] == 'no' ) {
            $sendthis = parse_email_text( $_POST['emailsubject'],
               $_POST['emailbody'], $mem['email'], $_POST['id'] );
            $subject = $sendthis['subject'];
            $body = $sendthis['body'];
         } else {
            $sendthis = parse_template( $_POST['emailtemplate'], $mem['email'],
               $_POST['id'] );
            $subject = $sendthis['subject'];
            $body = $sendthis['body'];
         }

         // use send_email function
         $success = send_email( $mem['email'], $from, stripslashes( $subject ),
            stripslashes( $body ) );

         if( $success !== true ) {
            echo '<p class="error">Email sending to <i>' . $mem['email'] .
               '</i> failed. Please try sending to that address again.</p>';
         }
      }
      echo '<p class="success">Email sending done.</p>';
   }

   if( $show_form ) {
      $id = $_REQUEST['id'];
?>
      <p>You can send an email to all members of <i><?php echo ( ( $info['title'] )
         ? $info['title'] . ': ' : '' ) . $info['subject'] . ' ' .
         $info['listingtype'] ?></i> via this page. Enter the email body or
      the template below, and click "Send email".</p>

      <form action="emails.php" method="post">
      <input type="hidden" name="action" value="members" />
      <input type="hidden" name="id" value="<?php echo $id ?>"/>
      <input type="hidden" name="send" value="yes" />

      <table>

      <tr><td>
      From
      </td><td>
      <?php echo $info['title'] ?> <code>&lt;<?php echo $info['email'] ?>&gt;</code>
      </td></tr>

      <tr class="rowshade"><td>
      Subject and Body
      </td><td style="text-align: left;">
      <input type="text" name="emailsubject" /><br /><br />
      <textarea name="emailbody" rows="7" cols="50"></textarea>
      </td></tr>

      <tr><td>
      Use Template?
      </td><td>
      <select name="emailtemplate">
      <option value="no">No, use the textarea</option>
<?php
      $templates = get_email_templates();
      foreach( $templates as $t ) {
         $info = get_template_info( $t );
         echo '<option value="' . $t . '">' . $info['templatename'] .
            '</option>';
         }
?>
      </select>
      </td></tr>

      <tr class="rowshade"><td colspan="2" class="right">
      <input type="submit" value="Send email" />
      </td></tr>

      </table></form>
<?php
   }
}


/*___________________________________________________________________DEFAULT_*/
if( $show_default ) {
?>
   <div class="submenu">
   <a href="emails.php?action=add">Add</a>
   </div>

   <p>Via this page, you can set up email templates for you to use later on
   when emailing members and affiliates when needed. Existing email
   templates are shown below. If you want to add an email template,
   click on the "Add" submenu item.</p>

   <table>

   <tr><th>Template Name</th>
   <th>Subject</th>
   <th>Content Excerpt</th>
   <th>Action</th></tr>
<?php
   $templates = get_email_templates();
   $shade = false;
   foreach( $templates as $t ) {
      $class = '';
      if( $shade ) {
         $class = ' class="rowshade"';
         $shade = false;
      } else $shade = true;
      $info = get_template_info( $t );
?>
      <tr<?php echo $class ?>><td>
      <?php echo $info['templatename'] ?>
      </td><td>
      <?php echo $info['subject'] ?>
      </td><td>
      <?php echo substr_replace( $info['content'], '...', 125 ) ?>
      </td><td>
      <a href="emails.php?action=edit&id=<?php echo $t ?>"><img src="edit.gif"
         width="42" height="19" border="0" alt=" edit" /></a>
<?php
      if( $info['deletable'] == 1 ) {
?>
         <a href="emails.php?action=delete&id=<?php echo $t ?>"
            onclick="go = confirm('Are you sure you want to delete the <?php echo addslashes( $info['templatename'] ) ?> template?');
            return go;"> <img src="delete.gif" width="42" height="19"
            border="0" alt=" delete" /></a>
<?php
      }
?>
      </td></tr>
<?php
   }
?>
   </table>
<?php
}
require_once( 'footer.php' );
?>