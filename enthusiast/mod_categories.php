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
function enth_get_categories( $search = '', $start = 'none' ) {
   require 'config.php';
   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }

   $query = "SELECT * FROM `$db_category` ORDER BY `catname`";

   if( $search )
      $query = "SELECT * FROM `$db_category` WHERE `catname` LIKE '%" .
         $search . "%' ORDER BY `catname`";

   if( $start != 'none' && ctype_digit( $start ) ) {
      $settingq = "SELECT `value` FROM `$db_settings` " .
         "WHERE `setting` = 'per_page'";
      $result = $db_link->prepare($settingq);
      $result->execute();
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $row = $result->fetch();
      $limit = $row['value'];
      $query .= " LIMIT $start, $limit";
   }

   $result = $db_link->prepare($query);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $cats = array();
   $result->setFetchMode(PDO::FETCH_ASSOC);
   while( $row = $result->fetch() )
      $cats[] = $row;

   // get children, if there is a search
   $finalcats = $cats;
   if( $search ) {
      foreach( $cats as $cat ) {
         $finalcats = array_merge( $finalcats,
            get_enth_category_children( $cat['catid'] ) );
      }
   }
   return $finalcats;
}

/*___________________________________________________________________________*/
function add_category( $cat, $parent = 0 ) {
   require 'config.php';
   $query = "INSERT INTO `$db_category` ( `catid`, `catname`, `parent` ) " .
      "VALUES( null, :cat, :parent )";
   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }
   $result = $db_link->prepare($query);
   $result->bindParam(':cat', $cat, PDO::PARAM_STR);
   $result->bindParam(':parent', $parent, PDO::PARAM_INT);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   return $result;
}


/*___________________________________________________________________________*/
function get_category_name( $id ) {
   require 'config.php';
   $query = "SELECT `catname` FROM `$db_category` WHERE `catid` = :id";
   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }
   $result = $db_link->prepare($query);
   $result->bindParam(':id', $id, PDO::PARAM_INT);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $result->setFetchMode(PDO::FETCH_ASSOC);
   $row = $result->fetch();
   return $row['catname'];
}


/*___________________________________________________________________________*/
function delete_category( $id ) {
   require 'config.php';
   $query = "DELETE FROM `$db_category` WHERE `catid` = :id";
   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }
   $result = $db_link->prepare($query);
   $result->bindParam(':id', $id, PDO::PARAM_INT);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   return $result;
}


/*___________________________________________________________________________*/
function edit_category( $id, $catname, $parent ) {
   require 'config.php';
   $query = "UPDATE `$db_category` SET `catname` = :catname";
   if( $parent )
      $query .= ", `parent` = '$parent' ";
   else
      $query .= ", `parent` = 0 ";
   $query .= "WHERE `catid` = '$id'";
   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }
   $result = $db_link->prepare($query);
   $result->bindParam(':catname', $catname, PDO::PARAM_STR);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   return $result;
}

/*___________________________________________________________________________*/
function get_enth_category_children( $id ) {
   require 'config.php';
   if( !is_numeric( $id ) )
      return array(); // return empty array in case id is not actual id
   $query = "SELECT * FROM `$db_category` WHERE `parent` = :id";
   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }
   $result = $db_link->prepare($query);
   $result->bindParam(':id', $id, PDO::PARAM_INT);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $cats = array();
   $result->setFetchMode(PDO::FETCH_ASSOC);
   while( $row = $result->fetch() )
      $cats[] = $row;
   return $cats;
}

/*___________________________________________________________________________*/
function get_category_parent( $id ) {
   require 'config.php';
   $query = "SELECT `parent` FROM `$db_category` WHERE `catid` = :id";
   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }
   $result = $db_link->prepare($query);
   $result->bindParam(':id', $id, PDO::PARAM_INT);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $result->setFetchMode(PDO::FETCH_ASSOC);
   if( $row = $result->fetch() )
      return $row['parent'];
   else
      return 0;
}


/*___________________________________________________________________________*/
function get_ancestors( $id ) {
   require 'config.php';
   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }

   $family = array();
   $family[] = $id;
   $query = "SELECT `parent` FROM `$db_category` WHERE `catid` = :id";
   $result = $db_link->prepare($query);
   $result->bindParam(':id', $id, PDO::PARAM_INT);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $result->setFetchMode(PDO::FETCH_ASSOC);
   $row = $result->fetch(); $i = 0;
   while( $row['parent'] != 0 && $row['parent'] != '' ) {
      $family[] = $row['parent'];
      $query = "SELECT `parent` FROM `$db_category` WHERE `catid` = '" .
         $row['parent'] . '\'';
      $result = $db_link->prepare($query);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $row = $result->fetch();
   }
   return $family;
}


/*___________________________________________________________________________*/
function category_array_compare( $one, $two ) {
   if( $one['text'] == $two['text'] )
      return 0;
   return( $one['text'] < $two['text'] ) ? -1 : 1;
}

?>