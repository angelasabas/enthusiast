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
function log_error( $page, $text, $kill = true ) {
   require 'config.php';
   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }
   // check if we're monitoring errors!
   $query = "SELECT `value` FROM `$db_settings` WHERE " .
      "`setting` = 'log_errors'";
   try {
      $result = $db_link->prepare($query);
      $result->execute();
   } catch (PDOException $e) {
      die( 'Error executing query: ' . $e->getMessage() );
   }
   $result->setFetchMode(PDO::FETCH_ASSOC);
   $row = $result->fetch();
   if( $row['value'] == 'yes' ) {
      $text = addslashes( $text );
      $query = "INSERT INTO `$db_errorlog` VALUES( NOW(), :page, :dtext )"; /* [Lysianthus] Changed :text to :dtext because it is a reserved keyword? */
      try {
         $result = $db_link->prepare($query);
         $result->bindParam(':page', $page, PDO::PARAM_STR);
         $result->bindParam(':dtext', $text, PDO::PARAM_STR); /* [Lysianthus] See above comment. */
         $result->execute();
      } catch (PDOException $e) {
         die( 'Error executing query: ' . $e->getMessage() );
      }
   } else {
      // we're not monitoring, so we just echo the thing :p
      if( $kill ) {
         echo "On $page - $text";
         die();
      }
   }
   return true;
}

/*___________________________________________________________________________*/
function get_logs( $start = 'none', $date = '' ) {
   require 'config.php';
   $query = "SELECT * FROM `$db_errorlog`";
   if( $date )
      $query .= " WHERE `date` = '$date'";
   $query .= ' ORDER BY `date` DESC';
   if( ctype_digit( $start ) )
      $query .= " LIMIT $start, " . get_setting( 'per_page' );
   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }
   $result = $db_link->prepare($query);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $logs = array();
   $result->setFetchMode(PDO::FETCH_ASSOC);
   while( $row = $result->fetch() )
      $logs[] = $row;
   return $logs;
}


/*___________________________________________________________________________*/
function flush_logs() {
   require 'config.php';
   $query = "TRUNCATE `$db_errorlog`";
   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $result = $db_link->prepare($query);
      $result->execute();
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }
   return $result;
}
?>