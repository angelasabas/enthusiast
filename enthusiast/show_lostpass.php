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
require_once 'config.php';

require_once( 'mod_errorlogs.php' );
require_once( 'mod_owned.php' );
require_once( 'mod_members.php' );
require_once( 'mod_settings.php' );
require_once( 'mod_emails.php' );

$install_path = get_setting( 'installation_path' );
require_once( $install_path . 'Mail.php' );

// get listing info
$info = get_listing_info( $listing );

// functions
if( !function_exists( 'clean' ) ) {
   function clean( $data ) {
      $data = trim( htmlentities( strip_tags( $data ), ENT_QUOTES ) );

      if( get_magic_quotes_gpc() )
         $data = stripslashes( $data );

      $data = addslashes( $data );

      return $data;
   }
}

// initialize variables
$show_form = true;
$errorstyle = ' style="font-weight: bold; display: block;" ' .
   'class="show_lostpass_error"';

// process forms
if( isset( $_POST['enth_email'] ) && $_POST['enth_email'] != '' ) {
   // do some spam/bot checking first
   $goahead = false;
   $badStrings = array( 'Content-Type:',
      'MIME-Version:',
      'Content-Transfer-Encoding:',
      'bcc:',
      'cc:',
      'content-type',
      'onload',
      'onclick',
      'javascript' );
   // 1. check that user is submitting from browser
   // 2. check the POST was indeed used
   // 3. no bad strings in any of the form fields
   if( isset( $_SERVER['HTTP_USER_AGENT'] ) &&
      $_SERVER['REQUEST_METHOD'] == 'POST' ) {
      foreach( $_POST as $k => $v ) {
         foreach( $badStrings as $v2 ){
            if( strpos( $v, $v2 ) !== false ) {
               echo "<p$errorstyle>Bad strings found in form.</p>";
               return;
            }
         }
      }
      $goahead = true;
   }
   unset( $k, $v, $v2, $badStrings );
   if( !$goahead ) {
      echo "<p$errorstyle>ERROR: Attempted circumventing of the form detected.</p>";
      return;
   }

   $email = '';
   $matchstring = "^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+" .
      "@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$";
   if( !ereg( $matchstring, clean( $_POST['enth_email'] ) ) ||
      !ctype_graph( clean( $_POST['enth_email'] ) ) )  {
?>
      <p style="font-weight: bold;" class="show_lostpass_bad_email">That
      email address is not valid. Please check your entered address and try
      again.</p>
<?php
      return;
   } else
      $email = clean( $_POST['enth_email'] );

   $member = get_member_info( $listing, $email );
   if( $member['email'] == '' ) {
?>
      <p style="font-weight: bold;" class="show_lostpass_no_such_member">There 
      was an error in  your request to reset your password. This may be 
      because there is no member recorded in the <?php echo $info['listingtype'] ?>
      with that email address. Please check your spelling and try
      again.</p>
<?php
   } else {
      $password = reset_member_password( $listing, $member['email'] );
      // send email
      $to = $member['email'];
      $subject = $info['title'] . ' ' . ucfirst( $info['listingtype'] ) .
         ': Password Reset';
      $from = '"' . html_entity_decode( $info['title'], ENT_QUOTES ) .
         '" <' . $info['email'] . '>';
      $message = parse_email( 'lostpass', $listing, $member['email'],
         $password );
      $message = stripslashes( $message );

      // use send_email function
      $mail_sent = send_email( $to, $from, $subject, $message );

      if( $mail_sent ) {
?>
         <p class="show_lostpass_processed_done">A password has been 
         successfully generated for you and this has
         been sent to your email address. Please update this password
         as soon as possible for your own security.</p>
<?php
      } else {
?>
         <p class="show_lostpass_processed_error">There was an error 
         sending the generated password to you. Please
         email me instead and let me know of the problem.</p>
<?php
      }
      $show_form = false;
   }
}

if( $show_form ) {
?>
   <p class="show_lostpass_intro">If you have lost or forgotten your 
   password, you can reset your password using this form. The new 
   generated password will be sent to you, and we advise you to 
   immediately change/update your password once you receive this.</p>

   <p class="show_lostpass_intro_instructions">Enter your email address on 
   the field below to generate a password.</p>

   <form method="post" action="<?php echo $info['lostpasspage'] ?>"
      class="show_lostpass_form">

   <p class="show_lostpass_email">
   <span style="display: block;" class="show_lostpass_email_label">
   Email address: </span>
   <input type="text" name="enth_email" class="show_lostpass_email_field" />
   <input type="submit" value="Reset my password"
      class="show_lostpass_submit_button" />
   </p>

   </form>

   <p style="text-align: center;" class="show_lostpass_credits"><a
   href="http://scripts.indisguise.org">Powered by
   Enthusiast <?php include ENTH_PATH . 'show_enthversion.php' ?></a></p>
<?php
}
?>