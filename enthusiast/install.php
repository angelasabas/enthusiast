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
require_once( 'header.php' );
require_once( 'config.php' );
echo '<h1>Welcome to the Enthusiast 3 Setup!</h1>';

$errors = array();
$show_form = true;

$owner_name = ( isset( $_POST['owner_name'] ) )
   ? $_POST['owner_name'] : 'Your Name';
$owner_email = ( isset( $_POST['owner_email'] ) )
   ? $_POST['owner_email'] : 'user@domain.tld';
$collective_title = ( isset( $_POST['collective_title'] ) )
   ? $_POST['collective_title'] : 'My collective';
$collective_url = ( isset( $_POST['collective_url'] ) )
   ? $_POST['collective_url'] : 'http://collective.yourdomain.tld';
$log_errors = ( isset( $_POST['log_errors'] ) )
   ? $_POST['log_errors'] : 'yes';
$installation_path = ( isset( $_POST['installation_path'] ) )
   ? $_POST['installation_path']
   : str_replace( basename( $_SERVER['PHP_SELF'] ), '', $_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF'] ) ;
$root_path_absolute = ( isset( $_POST['root_path_absolute'] ) )
   ? $_POST['root_path_absolute'] : $_SERVER['DOCUMENT_ROOT'] . '/';
$root_path_web = ( isset( $_POST['root_path_web'] ) )
   ? $_POST['root_path_web'] : "http://{$_SERVER['HTTP_HOST']}/";
$date_format = ( isset( $_POST['date_format'] ) )
   ? $_POST['date_format'] : 'dS F Y';
$per_page = ( isset( $_POST['per_page'] ) )
   ? $_POST['per_page'] : '10';
$mail_settings = ( isset( $_POST['mail_settings'] ) )
   ? $_POST['mail_settings'] : 'mail';
$sendmail_path = ( isset( $_POST['sendmail_path'] ) )
   ? $_POST['sendmail_path'] : '/usr/bin/sendmail';
$sendmail_args = ( isset( $_POST['sendmail_args'] ) )
   ? $_POST['sendmail_args'] : '';
$smtp_host = ( isset( $_POST['smtp_host'] ) )
   ? $_POST['smtp_host'] : 'mail.' . $_SERVER['HTTP_HOST'];
$smtp_port = ( isset( $_POST['smtp_port'] ) )
   ? $_POST['smtp_port'] : '25';
$smtp_auth = ( isset( $_POST['smtp_auth'] ) )
   ? $_POST['smtp_auth'] : 'no';
$smtp_username = ( isset( $_POST['smtp_username'] ) )
   ? $_POST['smtp_username'] : '';
$smtp_password = ( isset( $_POST['smtp_password'] ) )
   ? $_POST['smtp_password'] : '';
$affiliates_dir = ( isset( $_POST['affiliates_dir'] ) )
   ? $_POST['affiliates_dir'] : "{$installation_path}affiliates/";
$joined_images_dir = ( isset( $_POST['joined_images_dir'] ) )
   ? $_POST['joined_images_dir'] : "{$installation_path}joined/";
$owned_images_dir = ( isset( $_POST['owned_images_dir'] ) )
   ? $_POST['owned_images_dir'] : "{$installation_path}owned/";

if( isset( $_POST['install'] ) && $_POST['install'] == 'yes' ) {

   // validate data
   if( $_POST['owner_name'] == '' )
      $errors['owner_name'] = 'You must enter the name you wish to be ' .
         'shown on outgoing emails.';

   if( $_POST['owner_email'] == '' )
      $errors['owner_email'] = 'You must enter the email address you wish ' .
         'to be connected to your collective.';

   if( $_POST['password'] == '' )
      $errors['password'] = 'You must enter a password to log into ' .
         'the admin system.';

   if( $_POST['password'] != $_POST['passwordv'] )
      $errors['password'] = 'Password validation failed.';

   if( $_POST['installation_path'] == '' )
      $errors['installation_path'] = 'You must enter the installation path ' .
         'of the Enth3 admin panel.';
   if( substr( $_POST['installation_path'], -1, 1 ) != '/' &&
      !isset( $errors['installation_path'] ) )
      $errors['installation_path'] = 'You must enter a trailing slash!';

   if( $_POST['root_path_absolute'] == '' )
      $errors['root_path_absolute'] = 'You must enter the absolute root path ' .
         'of your server.';
   if( substr( $_POST['root_path_absolute'], -1, 1 ) != '/' &&
      !isset( $errors['root_path_absolute'] ) )
      $errors['root_path_absolute'] = 'You must enter a trailing slash!';

   if( $_POST['root_path_web'] == '' )
      $errors['root_path_web'] = 'You must enter the web root path ' .
         'of your server.';
   if( substr( $_POST['root_path_web'], -1, 1 ) != '/' &&
      !isset( $errors['root_path_web'] ) )
      $errors['root_path_web'] = 'You must enter a trailing slash!';

   if( substr( $_POST['affiliates_dir'], -1, 1 ) != '/' &&
      !isset( $errors['affiliates_dir'] ) )
      $errors['affiliates_dir'] = 'You must enter a trailing slash!';

   if( substr( $_POST['joined_images_dir'], -1, 1 ) != '/' &&
      !isset( $errors['joined_images_dir'] ) )
      $errors['joined_images_dir'] = 'You must enter a trailing slash!';

   if( substr( $_POST['owned_images_dir'], -1, 1 ) != '/' &&
      !isset( $errors['owned_images_dir'] ) )
      $errors['owned_images_dir'] = 'You must enter a trailing slash!';

   if( count( $errors ) == 0 ) { // continue now
      // try to connect
      try {
         $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
         $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
         echo $e->getMessage();
      }

      // create collective affiliates table
      $query = "CREATE TABLE `$db_affiliates` (" .
         '`affiliateid` int(5) NOT NULL auto_increment,' .
         '`url` varchar(254) NOT NULL default \'\',' .
         '`title` varchar(254) NOT NULL default \'\',' .
         '`imagefile` varchar(254) default NULL,' .
         '`email` varchar(255) NOT NULL default \'\',' .
         '`added` date default NULL,' .
         'PRIMARY KEY  (`affiliateid`)' .
         ') ENGINE=MyISAM AUTO_INCREMENT=1';
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';

      // create categories table
      $query = "CREATE TABLE `$db_category` (" .
         '`catid` int(5) NOT NULL auto_increment, ' .
         '`catname` varchar(255) NOT NULL default \'\', ' .
         '`parent` int(5) NOT NULL DEFAULT \'0\', ' .
         'PRIMARY KEY  (`catid`)' .
         ') ENGINE=MyISAM AUTO_INCREMENT=1';
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';

      // create email templates table
      $query = "CREATE TABLE `$db_emailtemplate` (" .
         '`templateid` int(3) NOT NULL auto_increment,' .
         '`templatename` varchar(255) NOT NULL default \'\',' .
         '`subject` varchar(255) NOT NULL default \'\',' .
         '`content` text NOT NULL,' .
         '`deletable` tinyint(1) NOT NULL default \'1\',' .
         'PRIMARY KEY  (`templateid`)' .
         ') ENGINE=MyISAM AUTO_INCREMENT=2';
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';
      $query = "INSERT INTO `$db_emailtemplate` VALUES (1, 'Add Affiliate " .
         'Template\', \'Affiliation with $$site_title$$\', ' .
         '\'Hello!\r\n\r\nThis is a notification for you to know that I ' .
         'have just added your site, $$site_aff_title$$ ($$site_aff_url$$) ' .
         'as an affiliate at $$site_title$$, ' .
         'found at $$site_url$$. :)\r\n\r\nSincerely,\r\n$$' .
         'site_owner$$\', 0 )';
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';

      // create joined table
      $query = "CREATE TABLE `$db_joined` (" .
         '`joinedid` int(10) NOT NULL auto_increment,' .
         '`catid` varchar(255) NOT NULL default \'0\',' .
         '`url` varchar(255) NOT NULL default \'\',' .
         '`subject` varchar(255) NOT NULL default \'\',' .
         '`desc` text,' .
         '`comments` text,' .
         '`imagefile` varchar(255) default NULL,' .
         '`added` date default NULL,' .
         '`pending` tinyint(1) NOT NULL default \'0\',' .
         'PRIMARY KEY  (`joinedid`),' .
         'FULLTEXT KEY `subject` (`subject`,`desc`,`comments`)' .
         ') ENGINE=MyISAM AUTO_INCREMENT=1';
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';

      // create owned table
      $query = "CREATE TABLE `$db_owned` (" .
         '`listingid` int(8) NOT NULL auto_increment,' .
         '`dbserver` varchar(255) NOT NULL default \'\',' .
         '`dbuser` varchar(255) NOT NULL default \'\',' .
         '`dbpassword` varchar(255) NOT NULL default \'\',' .
         '`dbdatabase` varchar(255) NOT NULL default \'\',' .
         '`dbtable` varchar(255) NOT NULL default \'\',' .
         '`title` varchar(255) default NULL,' .
         '`subject` varchar(255) NOT NULL default \'\',' .
         '`email` varchar(255) NOT NULL default \'\',' .
         '`url` varchar(255) default NULL,' .
         '`imagefile` varchar(255) default NULL,' .
         '`desc` text,' .
         '`catid` varchar(255) NOT NULL default \'0\',' .
         '`listingtype` varchar(255) NOT NULL default \'fanlisting\',' .
         '`country` tinyint(1) NOT NULL default \'1\',' .
         '`affiliates` tinyint(1) NOT NULL default \'0\',' .
         '`affiliatesdir` varchar(255) default NULL,' .
         '`dropdown` tinyint(1) NOT NULL default \'1\',' .
         '`sort` varchar(255) NOT NULL default \'country\',' .
         '`perpage` int(3) NOT NULL default \'25\',' .
         '`linktarget` varchar(255) NOT NULL default \'_top\',' .
         '`additional` text,' .
         '`joinpage` varchar(255) NOT NULL default \'join.php\',' .
         '`listpage` varchar(255) NOT NULL default \'list.php\',' .
         '`updatepage` varchar(255) NOT NULL default \'update.php\',' .
         '`lostpasspage` varchar(255) NOT NULL default \'lostpass.php\',' .
         '`emailsignup` text NOT NULL,' .
         '`emailapproved` text NOT NULL,' .
         '`emailupdate` text NOT NULL,' .
         '`emaillostpass` text NOT NULL,' .
         '`listtemplate` text NOT NULL,' .
         '`affiliatestemplate` text,' .
         '`statstemplate` text,' .
         '`notifynew` tinyint(1) NOT NULL default \'1\',' .
         '`holdupdate` tinyint(1) NOT NULL default \'0\',' .
         '`opened` date default NULL,' .
         '`status` tinyint(1) NOT NULL default \'0\',' .
         'PRIMARY KEY  (`listingid`),' .
         'FULLTEXT KEY `title` (`title`,`subject`,`url`,`desc`)' .
         ') ENGINE=MyISAM AUTO_INCREMENT=1';
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';

      // create error logs table
      $query = "CREATE TABLE `$db_errorlog` (" .
         '`date` DATETIME NOT NULL ,' .
         '`source` VARCHAR( 100 ) NOT NULL ,' .
         '`log` TEXT NOT NULL )';
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';

      // create settings table
      $query = "CREATE TABLE `$db_settings` (" .
         '`setting` varchar(255) NOT NULL default \'\',' .
         '`title` varchar(255) NOT NULL default \'\',' .
         '`value` text NOT NULL,' .
         '`help` text NOT NULL,' .
         'PRIMARY KEY  (`setting`)' .
         ') ENGINE=MyISAM';
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';

      // populate settings table, templates first
      $query = "INSERT INTO `$db_settings` VALUES ('affiliates_template', " .
         "'Collective affiliates template', '<a href=\"enth3-url\"><img " .
         "src=\"enth3-image\" width=\"enth3-width\" height=\"enth3-height\" " .
         "border=\"0\" alt=\" enth3-title\" /></a> ', 'Template for showing " .
         "collective affiliates.')";
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';
      $query = "INSERT INTO `$db_settings` VALUES ( " .
         "'affiliates_template_footer', 'Affiliates template footer', '</p>', " .
         "'Text that is inserted directly after the collective affiliates are " .
         "shown.')";
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';
      $query = "INSERT INTO `$db_settings` VALUES ( " .
         "'affiliates_template_header', 'Affiliates template header', " .
         "'<p class=\"center\">', 'Text inserted directly before collective " .
         "affiliates are shown.')";
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';
      $query = "INSERT INTO `$db_settings` VALUES ('joined_template', " .
         "'Joined fanlistings template', '<a href=\"enth3-url\"><img " .
         "src=\"enth3-image\" width=\"enth3-width\" height=\"enth3-height\" " .
         "border=\"0\" alt=\" enth3-subject: enth3-desc\" /></a> ', " .
         "'Template for showing joined fanlistings.')";
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';
      $query = "INSERT INTO `$db_settings` VALUES ('joined_template_footer', " .
         "'Joined template footer', '</p>', 'Text that is inserted directly " .
         "after the joined listings are shown.')";
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';
      $query = "INSERT INTO `$db_settings` VALUES ('joined_template_header', " .
         "'Joined template header', '<p class=\"center\">', " .
         "'Text inserted directly before joined listings are shown.')";
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';
      $query = "INSERT INTO `$db_settings` VALUES ('owned_template', " .
         "'Owned fanlistings template', '<p class=\"center\"><a " .
         "href=\"enth3-url\"><img src=\"enth3-image\" width=\"enth3-width\" " .
         "height=\"enth3-height\" border=\"0\" alt=\" enth3-title\" " .
         "/></a><br />\r\n<b>enth3-title: enth3-subject</b><br />\r\n<b><a " .
         "href=\"enth3-url\">enth3-url</a></b><br />\r\nenth3-desc</p>', " .
         "'Template for showing owned fanlistings.')";
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';
      $query = "INSERT INTO `$db_settings` VALUES ('owned_template_footer', " .
         "'Owned template footer', '</p>', 'owned listings are shown.')";
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';
      $query = "INSERT INTO `$db_settings` VALUES ('owned_template_header', " .
         "'Owned template header', '<p class=\"center\">', 'Text inserted " .
         "directly before owned listings are shown.')";
      try {
         $success = $db_link->prepare($query);
         $success->execute();
      } catch (PDOException $e) {
         die('<p class="error">Error executing query: ' . $e->getMessage() . '; <i>' . $query . '</i></p>');
      }
      if( !$success )
         echo '<p class="error">Query unsuccessful: ' . $success->errorInfo()[2] . ' ' .
            $query . '</p>';

      foreach( $_POST as $field => $value ) {
         if( $field != 'install' && $field != 'passwordv' &&
            $field != 'login_password' ) {
            $title = '';
            $help = '';
            switch( $field ) {
               case 'affiliates_dir' :
                  $title = 'Collective Affiliates Directory';
                  $help = 'Directory where your collective affiliates images ' .
                     '(if any) are stored.';
                  break;
               case 'collective_title' :
                  $title = 'Collective Title';
                  $help = 'Your collective title.';
                  break;
               case 'collective_url' :
                  $title = 'Collective URL';
                  $help = 'Web address of your collective.';
                  break;
               case 'date_format' :
                  $title = 'Date format';
                  $help = 'Date format (same as PHP variables).';
                  break;
               case 'installation_path' :
                  $title = 'Installation Path (Absolute)';
                  $help = 'Installation path (absolute path) for this ' .
                     'installation of Enthusiast 3.';
                  break;
               case 'joined_images_dir' :
                  $title = 'Joined images directory';
                  $help = 'Directory where your joined images will be stored. ' .
                     'This should be an absolute path, and a trailing slash is ' .
                     'important.';
                  break;
               case 'link_target' :
                  $title = 'Link targets';
                  $help = 'Your link target (_blank, _top, _self, etc.)';
                  break;
               case 'owned_images_dir';
                  $title = 'Owned images directory';
                  $help = 'Directory where your owned listing images will be ' .
                     'stored. This should be an absolute path, and a trailing ' .
                     'slash is important.';
                  break;
               case 'owner_email' :
                  $title = 'Your email';
                  $help = 'Your email address for outgoing emails.';
                  break;
               case 'owner_name' :
                  $title = 'Your name';
                  $help = 'Your name for outgoing emails.';
                  break;
               case 'password' :
                  $title = 'Password';
                  $help = 'The password used to log into this installation of ' .
                     'Enthusiast 3.';
                  break;
               case 'per_page' :
                  $title = 'Number of items per page';
                  $help = 'Number of items shown per page on any given view.';
                  break;
               case 'root_path_absolute' :
                  $title = 'Root absolute path';
                  $help = 'Absolute path of your root directory (i.e., ' .
                     '/home/username/public_html/)';
                  break;
               case 'root_path_web' :
                  $title = 'Root web address';
                  $help = 'Web address of your root directory ' .
                     '(i.e.,http://yourdomain.com)';
                  break;
               case 'log_errors' :
                  $title = 'Log errors?';
                  $help = 'Turn error logging on or off.';
                  break;
               case 'mail_settings' :
                  $title = 'Mail interface setting';
                  $help = 'Which mail interface to use (or PHP\\\'s native ' .
                     'mail() function)';
                  break;
               case 'sendmail_path' :
                  $title = 'Sendmail path';
                  $help = 'The location of the sendmail program on the filesystem. ';
                  break;
               case 'sendmail_args' :
                  $title = 'Additional sendmail arguments';
                  $help = 'Additional parameters to pass to the sendmail.';
                  break;
               case 'smtp_host' :
                  $title = 'SMTP host';
                  $help = 'The SMTP server to connect to.';
                  break;
               case 'smtp_port' :
                  $title = 'SMTP port';
                  $help = 'The port to connect to on the SMTP server.';
                  break;
               case 'smtp_auth' :
                  $title = 'Use SMTP authentication?';
                  $help = 'Whether or not to use SMTP authentication.';
                  break;
               case 'smtp_username' :
                  $title = 'SMTP username';
                  $help = 'The username to use for SMTP authentication.';
                  break;
               case 'smtp_password' :
                  $title = 'SMTP password';
                  $help = 'The password to use for SMTP authentication.';
                  break;
               default :
                  break;
            }

            $query = "INSERT INTO `$db_settings` VALUES (:field, " .
               ":title, :value, :help)";
            if( $field == 'password' ) {
               $query = "INSERT INTO `$db_settings` VALUES (:field, " .
                  ":title, MD5( :value ), :help)";
               }
            if( $field != 'passwordv' ) {
               try {
                  $success = $db_link->prepare($query);
                  $success->bindParam(':field', $field, PDO::PARAM_STR);
                  $success->bindParam(':title', $title, PDO::PARAM_STR);
                  $success->bindParam(':value', $value, PDO::PARAM_STR);
                  $success->bindParam(':help', $help, PDO::PARAM_STR);
                  $success->execute();
               } catch (PDOException $e) {
                  die('<p class="error">' .
                  'Error executing query: ' . $e->getMessage() . '; <i>' . $query .
                  '</i></p>');
               }

               if( !$success )
                  echo '<p class="error">Query unsuccessful: ' .
                     $success->errorInfo()[2] . ' ' . $query . '</p>';
            }
         }
      }
?>
      <p>Database creation has ended.</p>

      <p>If you wish to add the TFL/TAFL categories, <a
      href="install_cats.php">click here</a>. Otherwise, you may now
      <a href="index.php">login</a>.</p>
<?php
   $show_form = false;
   }
}


if( $show_form ) {
?>
   <p>
   Welcome to the setup/installation of
   <a href="http://scripts.indisguise.org">Enthusiast 3</a>, the listing
   collective management system! Thank you for trying out the script. This file
   sets up the database tables and initial settings for your Enthusiast 3
   installation. Please make sure that your <code>config.php</code> file has
   been edited and customized to reflect the proper database settings.
   </p>

   <p>
   Please fill out the following fields, so that the setup file can correctly
   initialize Enth3's settings for you.
   </p>

<?php
   if( count( $errors ) ) {
      echo '<p class="error">There were errors with your form. Please try ' .
         'again.</p>';
   }
?>

   <form action="install.php" method="post">
   <input type="hidden" name="install" value="yes" />

   <table>

   <tr><th colspan="2">
   General settings
   </th></tr>

   <tr><td rowspan="2" valign="top" width="15%">
   <b>Owner name</b>
   </td><td>
   Your name as the owner of the collective; this will show up on outgoing
   collective emails (such as affiliate addition notifications).
   <?php echo ( isset( $errors['owner_name'] ) )
      ? '<br /><span class="error">' . $errors['owner_name'] . '</span>' : '' ?>
   </td></tr><tr><td>
   <input type="text" size="50" name="owner_name" value="<?php echo $owner_name ?>" />
   </td></tr>

   </table><table>

   <tr class="rowshade"><td rowspan="2" valign="top" width="15%">
   <b>Owner email</b>
   </td><td>
   This is the email address connected to this collective; all outgoing
   emails will come from this email account.
   <?php echo ( isset( $errors['owner_email'] ) )
      ? '<br /><span class="error">' . $errors['owner_email'] . '</span>' : '' ?>
   </td></tr><tr class="rowshade"><td>
   <input type="text" size="50" name="owner_email" value="<?php echo $owner_email ?>" />
   </td></tr>

   </table><table>

   <tr><td rowspan="2" valign="top" width="15%"><b>
   Collective title
   </b></td><td>
   This is the title of the collective. This will, again, show up in all
   outgoing emails.
   </td></tr><tr><td>
   <input type="text" size="50" name="collective_title"
      value="<?php echo $collective_title ?>" />
   </td></tr>

   </table><table>

   <tr class="rowshade"><td rowspan="2" valign="top" width="15%"><b>
   Collective URL
   </b></td><td>
   This is the URL of the collective that will be shown in outgoing emails.
   </td></tr><tr class="rowshade"><td>
   <input type="text" size="50" name="collective_url"
      value="<?php echo $collective_url ?>" />
   </td></tr>

   </table><table>

   <tr><td rowspan="2" valign="top" width="15%"><b>
   Password
   </b></td><td>
   This is the password you will be using to log into Enthusiast 3. This
   is case-sensitive, and it is encrypted in the database. Type it twice
   for verification purposes.
   <?php echo ( isset( $errors['password'] ) )
      ? '<br /><span class="error">' . $errors['password'] . '</span>' : '' ?>
   </td></tr><tr><td>
   <input type="password" name="password" />
   <input type="password" name="passwordv" />
   </td></tr>

   </table><table>

   <tr><th colspan="2">
   Technical settings
   </th></tr>

   <tr><td rowspan="2" valign="top" width="15%"><b>
   Log errors?
   </b></td><td>
   If you wish Enth3 to log errors while you or your visitors are using
   Enth3-related features, click the checkbox. This will consume some
   MySQL database space if you do not flush the error logs regularly.
   </td></tr><tr><td>
   <input type="radio" name="log_errors" value="no" <?php echo ( $log_errors == 'no' )
      ? 'checked="checked"' : '' ?> />
   No, do not log errors.<br />
   <input type="radio" name="log_errors" value="yes" <?php echo ( $log_errors == 'yes' )
      ? 'checked="checked"' : '' ?> />
   Yes, log errors!<br />
   </td></tr>

   <tr class="rowshade"><td rowspan="2" valign="top" width="15%"><b>
   Installation path
   </b></td><td>
   This is the absolute path for this installation of Enthusiast 3, i.e.,
   <code>/home/username/public_html/enthusiast3/</code>. This
   is important for showing the proper forms for fanlistings.
   <?php echo ( isset( $errors['installation_path'] ) )
      ? '<br /><span class="error">' . $errors['installation_path'] . '</span>' : '' ?>
   </td></tr><tr class="rowshade"><td>
   <input type="text" size="50" name="installation_path"
   value="<?php echo $installation_path ?>" />
   </td></tr>

   </table><table>

   <tr><td rowspan="2" valign="top" width="15%"><b>
   Root path (absolute)
   </b></td><td>
   This is the absolute path to the root of your website: note that this is
   different from the root of your collective. This is important if you plan
   to use images (i.e., on joined fanlistings, owned fanlistings, and
   affiliates). Example: <code>/home/username/public_html/</code>
   <?php echo ( isset( $errors['root_path_absolute'] ) )
      ? '<br /><span class="error">' . $errors['root_path_absolute'] . '</span>' : '' ?>
   </td></tr><tr><td>
   <input type="text" size="50" name="root_path_absolute"
   value="<?php echo $root_path_absolute ?>" />
   </td></tr>

   </table><table>

   <tr class="rowshade"><td rowspan="2" valign="top" width="15%"><b>
   Root path (web)
   </b></td><td>
   This is the Web path to the root of your website: note that this is
   different from the root of your collective. This is important if you plan
   to use images (i.e., on joined fanlistings, owned fanlistings, and
   affiliates). This value will replace the value of <i>Root path
   (Absolute)</i> when plugged into IMG tags. Example:
   <code>http://yourdomain.tld/</code> (don't forget the trailing slash).
   <?php echo ( isset( $errors['root_path_web'] ) )
      ? '<br /><span class="error">' . $errors['root_path_web'] . '</span>' : '' ?>
   </td></tr><tr class="rowshade"><td>
   <input type="text" size="50" name="root_path_web"
      value="<?php echo $root_path_web ?>" />
   </td></tr>

   </table><table>

   <tr><td rowspan="2" valign="top" width="15%"><b>
   Date format
   </b></td><td>
   This will be the format of the date in all occurences of it throughout
   the collective. It follows the format of the date() function in PHP.
   </td></tr><tr><td>
   <input type="text" size="50" name="date_format" value="<?php echo $date_format ?>" />
   </td></tr>

   </table><table>

   <tr class="rowshade"><td rowspan="2" valign="top" width="15%"><b>
   Items per page
   </b></td><td>
   This is the number of items to show per page.
   </td></tr><tr class="rowshade"><td>
   <input type="text" size="50" name="per_page" value="<?php echo $per_page ?>" />
   </td></tr>

   </table><table>

   <tr><th colspan="2">
   Mailing settings
   </th></tr>

   <tr><td rowspan="2" valign="top" width="15%"><b>
   Mailer to use
   </b></td><td>
   This sets what mailing mechanism PHP will use. You can probably leave this
   alone unless you know what you are doing.
   <?php echo ( isset( $errors['mail_settings'] ) )
      ? '<br /><span class="error">' . $errors['mail_settings'] . '</span>' : '' ?>
   </td></tr><tr><td>
   <select name="mail_settings">
   <option value="mail" <?php echo ( $mail_settings == 'mail' )
      ? 'selected="selected"' : '' ?>>PHP's native mail() function</option>
   <option value="sendmail" <?php echo ( $mail_settings == 'sendmail' )
      ? 'selected="selected"' : '' ?>>Sendmail</option>
   <option value="smtp" <?php echo ( $mail_settings == 'SMTP' )
      ? 'selected="selected"' : '' ?>>SMTP</option>
   </select>
   </td></tr>

   </table><table>

   <tr class="rowshade"><td rowspan="2" valign="top" width="15%"><b>
   Sendmail path
   </b></td><td>
   <strong>Only needed if using Sendmail above.</strong> This should
   be the path for your host's sendmail; please ask your host for this.
   <?php echo ( isset( $errors['sendmail_path'] ) )
      ? '<br /><span class="error">' . $errors['sendmail_path'] . '</span>' : '' ?>
   </td></tr><tr class="rowshade"><td>
   <input type="hidden" name="sendmail_args" value="" />
   <input type="text" size="50" name="sendmail_path"
   value="<?php echo $sendmail_path ?>" />
   </td></tr>

   </table><table>

   <tr><td rowspan="2" valign="top" width="15%"><b>
   SMTP settings: host and port
   </b></td><td>
   <strong>Only needed if using SMTP above.</strong> This is the SMTP host and
   port for the SMTP server.
   <?php echo ( isset( $errors['smtp_host'] ) )
      ? '<br /><span class="error">' . $errors['smtp_host'] . '</span>' : '' ?>
   <?php echo ( isset( $errors['smtp_port'] ) )
      ? '<br /><span class="error">' . $errors['smtp_post'] . '</span>' : '' ?>
   </td></tr><tr><td>
   <input type="text" size="50" name="smtp_host"
   value="<?php echo $smtp_host ?>" />:<input type="text" size="5" name="smtp_port"
   value="<?php echo $smtp_port ?>" />

   </td></tr>

   </table><table>

   <tr class="rowshade"><td rowspan="4" valign="top" width="15%"><b>
   SMTP authentication settings
   </b></td><td>
   <strong>Only needed if using SMTP above.</strong> This is the authentication
   settings for the SMTP server (if needed).
   <?php echo ( isset( $errors['smtp_auth'] ) )
      ? '<br /><span class="error">' . $errors['smtp_auth'] . '</span>' : '' ?>
   <?php echo ( isset( $errors['smtp_username'] ) )
      ? '<br /><span class="error">' . $errors['smtp_username'] . '</span>' : '' ?>
   <?php echo ( isset( $errors['smtp_password'] ) )
      ? '<br /><span class="error">' . $errors['smtp_password'] . '</span>' : '' ?>
   </td></tr><tr class="rowshade"><td>
   <input type="checkbox" name="smtp_auth" value="yes" <?php echo
      ( $smtp_auth == 'yes' ) ? 'checked="checked"' : ''; ?>/> Yes, SMTP server requires authentication
   </td></tr><tr class="rowshade"><td>
   Username: <input type="text" name="smtp_username" size="50"
   value="<?php echo $smtp_username ?>" />
   </td></tr><tr class="rowshade"><td>
   Password: <input type="password" size="50" name="smtp_password"
   value="<?php echo $smtp_password ?>" />
   </td></tr>

   </table><table>

   <tr><th colspan="2">
   Image folders settings
   </th></tr>

   <tr><td rowspan="2" valign="top" width="15%"><b>
   Affiliates images directory (collective)
   </b></td><td>
   This is the absolute path to your affiliates image directory.
   Do not forget the trailing slash.
   <?php echo ( isset( $errors['affiliates_dir'] ) )
      ? '<br /><span class="error">' . $errors['affiliates_dir'] . '</span>' : '' ?>
   </td></tr><tr><td>
   <input type="text" size="50" name="affiliates_dir"
   value="<?php echo $affiliates_dir ?>" />
   </td></tr>

   </table><table>

   <tr class="rowshade"><td rowspan="2" valign="top" width="15%"><b>
   Joined images directory
   </b></td><td>
   This is the absolute path to your joined listings image directory. Do not
   forget the trailing slash.
   <?php echo ( isset( $errors['joined_images_dir'] ) )
      ? '<br /><span class="error">' . $errors['joined_images_dir'] . '</span>' : '' ?>
   </td></tr><tr class="rowshade"><td>
   <input type="text" size="50" name="joined_images_dir"
   value="<?php echo $joined_images_dir ?>" />
   </td></tr>

   </table><table>

   <tr><td rowspan="2" valign="top" width="15%"><b>
   Owned images directory
   </b></td><td>
   This is the absolute path to your owned listings image directory. Do not
   forget the trailing slash.
   <?php echo ( isset( $errors['owned_images_dir'] ) )
      ? '<br /><span class="error">' . $errors['owned_images_dir'] . '</span>' : '' ?>
   </td></tr><tr><td>
   <input type="text" size="50" name="owned_images_dir"
   value="<?php echo $owned_images_dir ?>" />
   </td></tr>

   </table><table>

   <tr><td colspan="2" class="right">
   <input type="submit" value="Install Enthusiast 3!" />
   <input type="reset" value="Reset settings" />
   </td></tr>

   </table></p>
<?php
}
require_once( 'footer.php' );
?>