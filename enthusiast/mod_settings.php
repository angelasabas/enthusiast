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

/*___________________________________________________________________________*/
function get_setting( $setting ) {
   include 'config.php';

   $query = "SELECT `value` FROM `$db_settings` WHERE `setting` = '$setting'";

   $db_link = mysql_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   mysql_select_db( $db_database )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   $result = mysql_query( $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysql_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $row = mysql_fetch_array( $result );
   return $row['value'];

} // end of get_setting


/*___________________________________________________________________________*/
function check_password( $password ) {
   include 'config.php';

   $query = "SELECT * FROM `$db_settings` WHERE `setting` = 'password' AND ";
   $query .= "`value` = '$password'";

   $db_link = mysql_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   mysql_select_db( $db_database )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   $result = mysql_query( $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysql_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   if( mysql_num_rows( $result ) > 0 )
      return true;
   else
      return false;
}


/*___________________________________________________________________________*/
function get_setting_title( $setting ) {
   include 'config.php';

   $query = "SELECT `title` FROM `$db_settings` WHERE `setting` = '$setting'";
   $db_link = mysql_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   mysql_select_db( $db_database )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   $result = mysql_query( $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysql_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $row = mysql_fetch_array( $result );
   return $row['title'];

} // end of get_setting_title


/*___________________________________________________________________________*/
function get_setting_desc( $setting ) {
   include 'config.php';

   $query = "SELECT `help` FROM `$db_settings` WHERE `setting` = '$setting'";
   $db_link = mysql_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   mysql_select_db( $db_database )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   $result = mysql_query( $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysql_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $row = mysql_fetch_array( $result );
   return $row['help'];

} // end of get_setting_desc


/*___________________________________________________________________________*/
function get_all_settings() {
   include 'config.php';

   $query = "SELECT * FROM `$db_settings` WHERE `setting` " .
      "NOT LIKE '%template%'";
   $db_link = mysql_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   mysql_select_db( $db_database )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   $result = mysql_query( $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysql_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }

   $settings = array();
   while( $row = mysql_fetch_array( $result ) )
      $settings[] = $row;
   return $settings;

} // end of get_all_settings


/*___________________________________________________________________________*/
function get_all_templates() {
   include 'config.php';

   $query = "SELECT * FROM `$db_settings` WHERE `setting` LIKE '%template%'";
   $db_link = mysql_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   mysql_select_db( $db_database )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   $result = mysql_query( $query );
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . mysql_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $templates = array();
   while( $row = mysql_fetch_array( $result ) )
      $templates[] = $row;
   return $templates;

} // end of get_all_settings


/*___________________________________________________________________________*/
function update_setting( $setting, $value ) {
   include 'config.php';

   $db_link = mysql_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   mysql_select_db( $db_database )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );

   if( $setting != 'password' ) {
      $query = "UPDATE `$db_settings` SET `value` = '$value' WHERE " .
         "`setting` = '$setting'";
      $result = mysql_query( $query );
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . mysql_error() .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
   } else {
      $query = "UPDATE `$db_settings` SET `value` = MD5( '$value' ) " .
         "WHERE `setting` = 'password'";
      $result = mysql_query( $query );
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . mysql_error() .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
   }

} // end of update_setting


/*___________________________________________________________________________*/
function update_settings( $settings ) {
   include 'config.php';
   $db_link = mysql_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   mysql_select_db( $db_database )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );

   foreach( $settings as $field => $value ) {
      $query = "UPDATE `$db_settings` SET `value` = '$value' WHERE " .
         "`setting` = '$field'";
      if( $field == 'password' ) {
         if( $settings['passwordv'] != '' &&
            $value == $settings['passwordv'] ) {
            $query = "UPDATE `$db_settings` SET `value` = MD5( '$value' ) " .
               "WHERE `setting` = 'password'";
         } else
            $query = '';
      }
      if( $query != '' ) {
         $result = mysql_query( $query );
         if( !$result ) {
            log_error( __FILE__ . ':' . __LINE__,
               'Error executing query: <i>' . mysql_error() .
               '</i>; Query is: <code>' . $query . '</code>' );
            die( STANDARD_ERROR );
         }
      }
   }
}
?>