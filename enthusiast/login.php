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
require_once( 'config.php' );

if( !isset( $_POST['login_password'] ) || $_POST['login_password'] == '' ) {
   $_SESSION['message'] = 'You must enter your password in order to log in.';
   header( 'location: index.php' );
   die( 'Redirecting you...' );
}

$remember = 'no';
if( isset( $_POST['rememberme'] ) && $_POST['rememberme'] == 'yes' )
   $remember = 'yes';

if( login( $_POST['login_password'], $remember ) ) {
   $direct = 'dashboard.php';
   if( isset( $_POST['next'] ) && $_POST['next'] != '' )
      $direct = $_POST['next'];
   header( 'location: ' . $direct );
   die( 'Redirecting you...' );
} else {
   $_SESSION['message'] = 'Your password does not match your previously-' .
      'set password. Please try again.';
   header( 'location: index.php' );
   die( 'Redirecting you...' );
}
?>