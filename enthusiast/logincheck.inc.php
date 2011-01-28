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
require_once( 'mod_settings.php' );
if( isset( $_COOKIE['e3login'] ) ) {

   // login security fix code thanks to boyzie
   // (http://codegrrl.com/forums/index.php?showuser=3674)

   if( !( get_magic_quotes_gpc() ) ) {
      // Note, we won't be connected to the database until the check_password() function
      // is executed, so we'll use addslashes instead of mysql_real_escape_string().
      $_COOKIE['e3login'] = addslashes( $_COOKIE['e3login'] );
   }

   // Is the password hash valid.
   if( check_password( $_COOKIE['e3login'] ) ) {
      $logged_in = true;
      $_SESSION['logerrors'] = get_setting( 'log_errors' );

   // If not, the user could have simply changed their password in the admin panel,
   // or it could be a hacking attempt.
   } else {
      // Delete the password cookie
      setcookie( 'e3login', '', time() - ( 60 * 60 * 24 ) );
      $_SESSION['message'] = 'Your session has ended. Please login again.';
      header( 'location: index.php' );
      die( 'Redirecting you...' );
   }

} else if( substr_count( $_SERVER['PHP_SELF'], 'index.php' ) == 0 &&
   substr_count( $_SERVER['PHP_SELF'], 'login.php' ) == 0 ) {
   $_SESSION['message'] = 'You are not logged in. Please log in to continue.';

   $next = pathinfo( $_SERVER['REQUEST_URI'] );
   $_SESSION['next'] = $next['basename'];
   header( 'location: index.php' );
   die( 'Redirecting you...' );
}

function login( $attempt, $remember = 'no' ) {
   require( 'config.php' );
   require_once( 'mod_settings.php' );
   $set = false;

   if( check_password( md5( $attempt ) ) ) {
      session_regenerate_id();
      if( $remember == 'yes' )
         $set = setcookie( 'e3login', md5( $attempt ),
            time()+60*60*24*7 ); // just one week
      else
         $set = setcookie( 'e3login', md5( $attempt ) );
   }
   $_SESSION['logerrors'] = get_setting( 'log_errors' );
   return $set;
}
?>