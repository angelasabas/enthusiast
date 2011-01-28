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

$show_default = true;
echo '<h1>Enthusiast 3 Settings</h1>';
$action = ( isset( $_REQUEST["action"] ) ) ? $_REQUEST['action'] : '';

/*______________________________________________________________________EDIT_*/
if( $action == 'edit' ) {
   update_settings( $_POST );
   echo '<p class="success">Settings updated.</p>';
}


/*___________________________________________________________________DEFAULT_*/
if( $show_default ) {
   $mail_settings = get_setting( 'mail_settings' );
?>
   <p>Via this page, you can edit the current settings of Enthusiast 3.
   The current value of each setting is shown in the text fields. Edit
   these values and click "Update settings" to change the settings.</p>

   <form action="settings.php" method="post">
   <input type="hidden" name="action" value="edit" />

   <table>

   <tr><th colspan="2">
   General Information
   </th></tr>

   <tr><td>
   Owner name
   </td><td>
   <input type="text" name="owner_name" value="<?php echo get_setting( 'owner_name' ) ?>" />
   </td></tr>

   <tr class="rowshade"><td>
   Owner email
   </td><td>
   <input type="text" name="owner_email" value="<?php echo get_setting( 'owner_email' ) ?>" />
   </td></tr>

   <tr><td>
   Collective Title
   </td><td>
   <input type="text" name="collective_title" value="<?php echo get_setting( 'collective_title' ) ?>" />
   </td></tr>

   <tr class="rowshade"><td>
   Collective URL
   </td><td>
   <input type="text" name="collective_url" value="<?php echo get_setting( 'collective_url' ) ?>" />
   </td></tr>

   <tr><td>
   Password
   </td><td>
   <i>Fill out only if changing your password.</i><br />
   <input type="password" name="password" />
   <input type="password" name="passwordv" />
   </td></tr>

   <tr><th colspan="2">
   Technical Settings
   </th></tr>

   <tr><td>
   Enable error logging
   </td><td>
   <input type="radio" name="log_errors" value="yes" <?php echo ( get_setting( 'log_errors' ) == 'yes' ) ? 'checked="checked" ' : '' ?>/>
   Yes<br />
   <input type="radio" name="log_errors" value="no" <?php echo ( get_setting( 'log_errors' ) != 'yes' ) ? 'checked="checked" ' : '' ?>/>
   No
   </td></tr>

   <tr class="rowshade"><td>
   Installation path
   </td><td>
   <input type="text" name="installation_path" value="<?php echo get_setting( 'installation_path' ) ?>" />
   </td></tr>

   <tr><td>
   Root path (absolute)
   </td><td>
   <input type="text" name="root_path_absolute" value="<?php echo get_setting( 'root_path_absolute' ) ?>" />
   </td></tr>

   <tr class="rowshade"><td>
   Root path (Web)
   </td><td>
   <input type="text" name="root_path_web" value="<?php echo get_setting( 'root_path_web' ) ?>" />
   </td></tr>

   <tr><td>
   Date format
   </td><td>
   <input type="text" name="date_format" value="<?php echo get_setting( 'date_format' ) ?>" />
   </td></tr>

   <tr class="rowshade"><td>
   Items per page
   </td><td>
   <input type="text" name="per_page" value="<?php echo get_setting( 'per_page' ) ?>" />
   </td></tr>

   <tr><th colspan="2">
   Mailing settings
   </th></tr>

   <tr><td>
   Mailer to use
   </td><td>
   <select name="mail_settings">
   <option value="mail" <?php echo ( $mail_settings == 'mail' )
      ? 'selected="selected"' : '' ?>>PHP's native mail() function</option>
   <option value="sendmail" <?php echo ( $mail_settings == 'sendmail' )
      ? 'selected="selected"' : '' ?>>Sendmail</option>
   <option value="smtp" <?php echo ( $mail_settings == 'smtp' )
      ? 'selected="selected"' : '' ?>>SMTP</option>
   </select>

   <tr class="rowshade"><td>
   Sendmail path
   </td><td>
   <input type="text" size="50" name="sendmail_path"
   value="<?php echo get_setting( 'sendmail_path' ); ?>" />
   </td></tr>

   <tr><td>
   SMTP settings: host and port
   </td><td>
   <input type="text" size="50" name="smtp_host"
   value="<?php echo get_setting( 'smtp_host' );
   ?>" />:<input type="text" size="5" name="smtp_port"
   value="<?php echo get_setting( 'smtp_port' ); ?>" />
   </td></tr>

   <tr class="rowshade"><td>
   SMTP authentication settings
   </td><td>
   <input type="checkbox" name="smtp_auth" value="yes" <?php echo
      ( get_setting( 'smtp_auth' ) == 'yes' )
      ? 'checked="checked"' : ''; ?>/> Yes, SMTP server requires authentication<br />

   Username: <input type="text" name="smtp_username" size="50"
   value="<?php echo get_setting( 'smtp_username' ); ?>" />
   <br />
   Password: <input type="password" size="25" name="smtp_password"
   value="<?php echo get_setting( 'smtp_password' ); ?>" />
   </td></tr>

   <tr><th colspan="2">
   Image folders settings
   </th></tr>

   <tr><td>
   Affiliates images directory (collective)
   </td><td>
   <input type="text" name="affiliates_dir" value="<?php echo get_setting( 'affiliates_dir' ) ?>" />
   </td></tr>

   <tr class="rowshade"><td>
   Joined images directory
   </td><td>
   <input type="text" name="joined_images_dir" value="<?php echo get_setting( 'joined_images_dir' ) ?>" />
   </td></tr>

   <tr><td>
   Owned images directory
   </td><td>
   <input type="text" name="owned_images_dir" value="<?php echo get_setting( 'owned_images_dir' ) ?>" />
   </td></tr>

   <tr class="rowshade"><td colspan="2" class="right">
   <input type="submit" value="Update settings" />
   <input type="reset" value="Reset settings" />
   </td></tr>

   </table>
   </form>
<?php
}
require_once( 'footer.php' );
?>