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
require 'config.php';

require_once( 'mod_errorlogs.php' );
require_once( 'mod_owned.php' );
require_once( 'mod_members.php' );
require_once( 'mod_settings.php' );
require_once( 'mod_emails.php' );

$install_path = get_setting( 'installation_path' );
require_once( $install_path . 'Mail.php' );

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

// get listing information
$info = get_listing_info( $listing );

// initialize variables
$show_form = true;
$messages = array();
$errorstyle = ' style="font-weight: bold; display: block;" ' .
   'class="show_join_error"';
$name = '';
$email = '';
$country = '';
$password = '';
$vpassword = '';
$url = '';
$comments = '';
$additional = $info['additional'];
$fields = explode( ',', $additional );
if( $fields[0] == '' )
   array_pop( $fields );
$values = array();
if( count( $fields ) > 0 )
   foreach( $fields as $field ) {
      $values[$field] = '';
      if( isset( $_POST["enth_$field"] ) )
         $values[$field] = clean( $_POST["enth_$field"] );
   }

// process the join form
if( isset( $_POST['enth_join'] ) && $_POST['enth_join'] == 'yes' ) {
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
               die( "<p$errorstyle>Bad strings found in form.</p>" );
               exit;
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

   // check nonce field
   $nonce = explode( ':', clean( $_POST['enth_nonce'] ) );
   $mdfived = substr( $nonce[2], 0, ( strlen( $nonce[2] ) - 3 ) );
   $appended = substr( $nonce[2], -3 );
   // check the timestamp; must be more than three seconds after
   if( abs( $nonce[1] - strtotime( date( 'r' ) ) ) < 3 ) {
      // probably a bot, or multiple-clicking... do this again
      die( "<p$errorstyle>ERROR: Please try again.</p>" );
      exit;
   }
   // check the timestamp; must not be over 12 hours before, either :p
   if( abs( $nonce[1] - strtotime( date( 'r' ) ) ) > ( 60 * 60 * 12 ) ) {
      // join window expired, try again
      die( "<p$errorstyle>ERROR: Please try again.</p>" );
      exit;
   }
   // check if the rand and the md5 hash is correct... last three digits first
   if( $appended != substr( $nonce[0], 2, 3 ) ) {
      // appended portion of random chars doesn't match actual random chars
      die( "<p$errorstyle>ERROR: Please try again.</p>" );
      exit;
   }
   // now check the hash
   if( md5( $nonce[0] ) != $mdfived ) {
      // hash of random chars and the submitted one isn't the same!
      die( "<p$errorstyle>ERROR: Please try again.</p>" );
      exit;
   }

   // go on
   if( $_POST['enth_name'] )
      $name = ucwords( clean( $_POST['enth_name'] ) );
   else
      $messages['name'] = 'You must enter your name.';

   $matchstring = "^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+" .
      "@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$";
   if( $_POST['enth_email'] && ereg( $matchstring, $_POST['enth_email'] ) )
      $email = clean( $_POST['enth_email'] );
   else
      $messages['email'] = 'You must enter a valid email address.';

   if( isset( $_POST['enth_country'] ) && $_POST['enth_country'] )
      $country = clean( $_POST['enth_country'] );
   else if( $info['country'] == 1 )
      $messages['country'] = 'You must specify your country.';

   if( $_POST['enth_password'] && $_POST['enth_vpassword'] &&
      $_POST['enth_vpassword'] == $_POST['enth_password'] ) {
      // has password and validates
      $password = clean( $_POST['enth_password'] );
   } else if( $_POST['enth_password'] == '' && $_POST['enth_vpassword'] == '' ) {
      // no password, must generate
      $password = '';
      $k = 0;
      while( $k <= 10 ) {
         $password .= chr( rand( 97, 122 ) );
         $k++;
      }
   } else
      $messages['password'] = 'The password you entered does not validate ' .
         '(does not match each other).';

   if( $_POST['enth_url'] && $_POST['enth_url'] != 'n/a' && $_POST['enth_url'] != 'N/A' &&
      $_POST['enth_url'] != 'none' && $_POST['enth_url'] != '-' ) {
      $url = clean( $_POST['enth_url'] );
      if( substr_count( $url, 'http://' ) == 0 )
         $url = 'http://' . $url;
   }

   foreach( $fields as $field )
      $values[$field] = clean( $_POST["enth_$field"] );

   if( isset( $_POST['enth_comments'] ) )
      $comments = clean( $_POST['enth_comments'] );

   if( count( $messages ) == 0 ) {
      $show_form = false;
      $show_email = clean( $_POST['enth_show_email'] );
      $send_account_info = ( isset( $_POST['enth_send_account_info'] ) &&
         $_POST['enth_send_account_info'] == 'yes' ) ? true : false;
      $table = $info['dbtable'];

      // more spamform checking
      // thanks to Jem of jemjabella.co.uk
      $find = "/(content-type|bcc:|cc:|onload|onclick|javascript)/i";
      if( preg_match( $find, $name ) || preg_match( $find, $email ) ||
         preg_match( $find, $url ) || preg_match( $find, $comments ) ||
         preg_match( $find, $country ) || preg_match( $find, $show_email ) ) {
         echo "<p$errorstyle>No naughty injecting, please.</p>";
         exit;
      }

      $query = "INSERT INTO `$table` VALUES( :email, :name, ";
      if( $info['country'] == 1 )
         $query .= "'$country', ";
      $query .= ":url, ";
      foreach( $fields as $field )
         $query .= '\'' . $values[$field] . '\', ';
      $query .= "1, MD5( :password ), :show_email, 1, NULL )";

      $db_link = new PDO('mysql:host=' . $info['dbserver'] . ';dbname=' . $info['dbdatabase'] . ';charset=utf8', $info['dbuser'], $info['dbpassword']);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $result = $db_link->prepare($query);
      $result->bindParam(':email', $email, PDO::PARAM_STR);
      $result->bindParam(':name', $name, PDO::PARAM_STR);
      $result->bindParam(':url', $url, PDO::PARAM_STR);
      $result->bindParam(':password', $password, PDO::PARAM_STR);
      $result->bindParam(':show_email', $show_email, PDO::PARAM_STR);
      $result->execute();

      // if addition is successful
      if( $result ) {
         // check if notify owner
         if( $info['notifynew'] == 1 ) {
            $subject = $info['subject'];
            $listingtype = $info['listingtype'];
            $listingurl = $info['url'];
            $notify_subject = "$subject - New member!";
            $notify_message = "Someone has joined your $subject $listingtype" .
               " ($listingurl). Relevant information is below:\r\n\r\n" .
               "Name: $name\r\n" .
               "Email: $email\r\n" .
               "Country: $country\r\n" .
               "URL: $url\r\n";
            foreach( $fields as $field )
               $notify_message .= ucwords( str_replace( '_', ' ', $field ) ) .
                  ': ' . $values[$field] . "\r\n";
            $notify_message .= "Comments: $comments\r\n\r\nTo add this " .
               "member, go to " . str_replace( get_setting(
               'root_path_absolute' ), get_setting( 'root_path_web' ),
               get_setting( 'installation_path' ) ) .
               "members.php\r\n";
            $notify_message = stripslashes( $notify_message );
            $notify_from = 'Enthusiast 3 <' .
               get_setting( 'owner_email' ) . '>';

            // use send_email function
            $mail_sent = send_email( $info['email'], $notify_from,
               $notify_subject, $notify_message );
         } // end notify owner

         // email new member, or just show success message
         if( !$send_account_info ) {
?>
            <p class="show_join_processed_noemail">The application form 
            for the <?php echo $info['subject'] ?> <?php echo $info['listingtype'] ?> has 
            been sent. You will be notified when you have been added into 
            the actual members list. If two weeks have passed and you have 
            received no email, please <a href="mailto:<?php echo
            str_replace( '@', '&#' . ord( '@' ) . ';', $info['email'] )
            ?>">email me</a> if you wish to check up on your form.</p>
<?php
         } else { // email!
            $to = $email;
            $subject = $info['title'] . ' ' . ucfirst( $info['listingtype'] ) .
               ' Information';
//            $from = str_replace( ',', '', $info['title'] ) . // strip commas
//               ' <' . $info['email'] . '>';
            $from = '"' . html_entity_decode( $info['title'], ENT_QUOTES ) .
               '" <' . $info['email'] . '>';
            $message = parse_email( 'signup', $listing, $email, $password );
            $message = stripslashes( $message );

            // use send_email function
            $success_mail = send_email( $to, $from, $subject, $message );
            if( $success_mail !== true ) {
?>
               <p class="show_join_processed_errormail">Your form has been 
               processed correctly, but unfortunately there was an error
               sending your application information to you. If you
               wish to receive information about your application, please feel
               free to <a href="mailto:<?php echo
               str_replace( '@', '&#' . ord( '@' ) . ';', $info['email'] )
               ?>">email me</a> and I will personally 
               look into it. Please not I cannot send your password to you.</p>

               <p class="show_join_processed_errormail">If two weeks have 
               passed and you have not yet been added,
               please feel free to check up on your application.</p>
<?php
            } else {
?>
               <p class="show_join_processed_emailsent">The application form 
               for the <?php echo $info['subject'] ?> <?php echo $info['listingtype'] ?> has 
               been sent. You will be notified when you have been added into 
               the actual members list. If two weeks have passed and you have 
               received no email, please <a href="mailto:<?php echo
               str_replace( '@', '&#' . ord( '@' ) . ';', $info['email'] )
               ?>">email me</a> if you wish to check up on your form.</p>

               <p class="show_join_processed_emailsent">An email has also 
               been sent to you with your information
               as you requested. Please do not lose this information.</p>
<?php
            }
         }
      } else {
         if( $result->errorCode() != 1062 ) {
            log_error( __FILE__ . ':' . __LINE__,
               'Error executing query: <i>' . $result->errorInfo()[2] .
               '</i>; Query is: <code>' . $query . '</code>' );
?>
            <p<?php echo $errorstyle ?>>An error occured while attempting to add you to the pending
            members queue. Unfortunately, this was caused by a database error
            on this <?php echo $info['listingtype'] ?>. The error has been logged, but
            feel free to <a href="mailto:<?php echo
            str_replace( '@', '&#' . ord( '@' ) . ';', $info['email'] )
            ?>">contact me</a>
            about it and I will try to fix the problem as soon as possible.</p>
<?php
         } else {
            $messages['form'] = 'An error occured while attempting to add ' .
               'you to the pending members queue. This is because you are ' .
               'possibly already a member (approved or unapproved) or ' .
               'someone used your email address to join this ' .
               $info['listingtype'] . ' before. If you wish to update ' .
               'your information, please go <a href="' . $info['updatepage'] .
               '">here</a>.';
            $show_form = true;
         }
      } // end if there is no result
   } // end if there is no error
} // end process the form

if( $show_form ) {
   $cutup = explode( '@', $info['email'] );
   $email_js = '<script type="text/javascript">' . "\r\n<!--\r\n" .
      "jsemail = ( '$cutup[0]' + '@' + '$cutup[1]' ); \r\n" .
      "document.write( '<a href=\"mailto:' + jsemail + '\">email me</' + " .
      "'a>' );\r\n" . ' -->' . "\r\n" . '</script>';

   // extra spam checking variable
   $rand = md5( uniqid( '', true ) );

   // strip slashes
   $email = stripslashes( $email );
   $name = stripslashes( $name );
   $country = stripslashes( $country );
   $url = stripslashes( $url );
   $password = '';
   foreach( $fields as $ind => $val )
      $fields[$ind] = stripslashes( $val );
?>
   <p class="show_join_intro">Please use the form below for joining the
   <?php echo $info['listingtype'] ?>. <b>Please hit the submit button only once.</b>
   Your entry is fed instantly into the database, and your email address is
   checked for duplicates. Passwords are encrypted into the database and will 
   not be seen by anyone else other than you. If left blank, a password will 
   be generated for you.</p>

   <p class="show_join_intro_problems">If you encounter problems, please 
   feel free to <?php echo $email_js ?>.</p>

   <p class="show_join_intro_required">The fields with asterisks (*) are
   required fields.</p>

<?php
   if( isset( $messages['form'] ) )
      echo "<p$errorstyle>{$messages['form']}</p>";
?>
   <form method="post" action="<?php echo $info['joinpage'] ?>"
      class="show_join_form">
   
   <p class="show_join_name">
   <input type="hidden" name="enth_join" value="yes" />
   <input type="hidden" name="enth_nonce" value="<?php echo $rand ?>:<?php echo
      strtotime( date( 'r' ) ) ?>:<?php echo md5( $rand ) . substr( $rand, 2, 3 ) ?>" />
   <span style="display: block;" class="show_join_name_label">* Name: </span>
<?php
   if( isset( $messages['name'] ) )
      echo "<span$errorstyle>{$messages['name']}</span>";
?>
   <input type="text" name="enth_name" value="<?php echo $name ?>"
      class="show_join_name_field" />
   </p>

   <p class="show_join_email">
   <span style="display: block;" class="show_join_email_label">* Email
   address: </span>
<?php
   if( isset( $messages['email'] ) )
      echo "<span$errorstyle>{$messages['email']}</span>";
?>
   <input type="text" name="enth_email" value="<?php echo $email ?>"
      class="show_join_email_field" />
   </p>

   <p class="show_join_email_settings">
   <span style="display: block;" class="show_join_email_settings_label">Show
   email address on the list? </span>
   <span style="display: block" class="show_join_email_settings_yes">
   <input type="radio" name="enth_show_email" value="1"
      class="show_join_email_settings_field" checked="checked" />
      <span class="show_join_email_settings_field_label">
      Yes (SPAM-protected on the site)</span>
   </span><span style="display: block" class="show_join_email_settings_no">
   <input type="radio" name="enth_show_email" value="0"
      class="show_join_email_settings_field" />
      <span class="show_join_email_settings_field_label">No</span>
   </span>
   </p>

<?php
   if( $info['country'] == 1 ) {
?>
      <p class="show_join_country">
      <span style="display: block;" class="show_join_country_label">*
      Country</span>
<?php
      if( isset( $messages['country'] ) )
         echo "<span$errorstyle>{$messages['country']}</span>";
?>
      <select name="enth_country" class="show_join_country_field">
<?php
      if( $country != '' ) {
         echo '<option selected="selected">' . $country . '</option>';
         echo '<option value="' . $country . '">---</option>';
      }
      include ENTH_PATH . 'countries.inc.php';
?>
      </select>
      </p>
<?php
   }
?>
   <p class="show_join_password">
   <span style="display: block;" class="show_join_password_label">Password
   (to change your details; type twice):</span>
<?php
   if( isset( $messages['password'] ) )
      echo "<span$errorstyle>{$messages['password']}</span>";
?>
   <input type="password" name="enth_password" class="show_join_password_field" />
   <input type="password" name="enth_vpassword" class="show_join_password_field2" />
   </p>

   <p class="show_join_url">
   <span style="display: block;" class="show_join_url_label">Website
   URL:</span>
   <input type="text" name="enth_url" value="<?php echo $url ?>"
      class="show_join_url_field" />
   </p>
<?php
   if( count( $fields ) > 0 && !( file_exists( 'addform.inc.php' ) ) ) {
      foreach( $fields as $field ) {
?>
         <p class="show_join_<?php echo $field ?>">
         <span style="display: block;" class="show_join_<?php echo $field ?>_label">
         <?php echo ucwords( str_replace( '_', ' ', $field ) ) ?>:</span>
         <input type="text" name="enth_<?php echo $field ?>" value="<?php echo $values[$field]
            ?>" class="show_join_<?php echo $field ?>_field" />
         </p>
<?php
      }
   } else if( count( $fields ) > 0 && file_exists( 'addform.inc.php' ) ) {
      require( 'addform.inc.php' );
   }
?>
   <p class="show_join_comments">
   <span style="display: block;" class="show_join_comments_label">
   Comments: </span>
   <textarea name="enth_comments" rows="3" cols="40"
      class="show_join_comments_field"><?php echo $comments ?></textarea>
   </p>

   <p class="show_join_submit">
   <span style="display: block;" class="show_join_send_account_info">
   <input type="checkbox" name="enth_send_account_info" value="yes"
      checked="checked" class="enth_show_join_send_account_info_field" />
   <span class="show_join_send_account_info_label">
   Yes, send me my account information!</span>
   </span>
   <input type="submit" value="Join the <?php echo $info['listingtype'] ?>"
      class="show_join_submit_button" />
   <input type="reset" value="Clear form" class="show_join_reset_button" />
   </p>

   </form>

   <p style="text-align: center;" class="show_join_credits"><a
   href="http://scripts.indisguise.org">Powered by
   Enthusiast <?php include ENTH_PATH . 'show_enthversion.php' ?></a></p>
<?php
}
?>
