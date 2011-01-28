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
require_once( 'mod_categories.php' );
require_once( 'mod_owned.php' );
require_once( 'mod_settings.php' );
require_once( 'mod_setup.php' );

$show_default = true;

echo '<h1>Setup a New Listing</h1>';
$step = ( isset( $_REQUEST['step'] ) ) ? $_REQUEST['step'] : '1';

switch( $step ) {

   case 1 : // setup required values and create database
      show_step1();
      break;

   case 2 :
      $success = do_step1();
      if( $success )
         show_step2();
      else
         show_step1();
      break;

   default :
?>
      <p>
      Ooops! There is no Step #<?php echo $step ?>. Please hit Back to continue!
      </p>
<?php
      break;

   }
require_once( 'footer.php' );
?>