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
function get_affiliates( $listing = 'none', $start = 'none' ) {
   require 'config.php';

   $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
   $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   $query = "SELECT `value` FROM `$db_settings` WHERE `setting` = 'per_page'";
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
   $limit = $row['value'];

   $limit_query = '';
   if( $start != 'none' ) {
      $limit_query = " LIMIT $start, $limit";
   }

   $query = "SELECT * FROM `$db_affiliates` ORDER BY `title`";
   if( $listing != '' && $listing != 'collective' && $listing != 'none' ) {
      // get info
      $query = "SELECT * FROM `$db_owned` WHERE `listingid` = :listing";
      $result = $db_link->prepare($query);
      $result->bindParam(':listing', $listing, PDO::PARAM_INT);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }

      $result->setFetchMode(PDO::FETCH_ASSOC);
      $info = $result->fetch();
      if( $info['affiliates'] != 1 )
         return array(); // return blank array if affiliates is not enabled

      $table = $info['dbtable'];
      $dbserver = $info['dbserver'];
      $dbdatabase = $info['dbdatabase'];
      $dbuser = $info['dbuser'];
      $dbpassword = $info['dbpassword'];

      $db_link = new PDO('mysql:host=' . $dbserver . ';dbname=' . $dbdatabase . ';charset=utf8', $dbuser, $dbpassword);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $afftable = $table . '_affiliates';
      $query = "SELECT * FROM `$afftable` ORDER BY `title`";
      }
   $query .= $limit_query;

   $result = $db_link->prepare($query);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $aff = array();
   $result->setFetchMode(PDO::FETCH_ASSOC);
   while( $row = $result->fetch() )
      $aff[] = $row;
   return $aff;
}

/*___________________________________________________________________________*/
function add_affiliate( $url, $title, $email, $listing = 'collective' ) {
   require 'config.php';

   $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
   $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   $query = '';
   if( $listing == 'collective' || $listing == '' )
      $query = "INSERT INTO `$db_affiliates` VALUES( null, :url, :title," .
         " null, :email, CURDATE() )";
   else {
      // get info
      $query = "SELECT * FROM `$db_owned` WHERE `listingid` = :listing";
      $result = $db_link->prepare($query);
      $result->bindParam(':listing', $listing, PDO::PARAM_INT);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $info = $result->fetch();
      $table = $info['dbtable'];
      $dbserver = $info['dbserver'];
      $dbdatabase = $info['dbdatabase'];
      $dbuser = $info['dbuser'];
      $dbpassword = $info['dbpassword'];

      $db_link = new PDO('mysql:host=' . $dbserver . ';dbname=' . $dbdatabase . ';charset=utf8', $dbuser, $dbpassword);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $afftable = $table . '_affiliates';
      $query = "INSERT INTO `$afftable` VALUES( " .
         "null, :url, :title, null, :email, CURDATE() )";
   }

   $result = $db_link->prepare($query);
   $result->bindParam(':url', $url, PDO::PARAM_STR);
   $result->bindParam(':title', $title, PDO::PARAM_STR);
   $result->bindParam(':email', $email, PDO::PARAM_STR);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   return $db_link->lastInsertId();
}


/*___________________________________________________________________________*/
function edit_affiliate( $id, $listing = '', $image = '', $url = '',
   $title = '', $email = '' ) {
   require 'config.php';

   $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
   $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   $query = '';
   if( $listing == '' || $listing == 'collective' ) {
      $query = "UPDATE `$db_affiliates` SET ";
      if( $url )
         $query .= "`url` = :url, ";
      if( $title )
         $query .= "`title` = :title, ";
      if( $email )
         $query .= "`email` = :email, ";
      if( $image != '' && $image != 'null' )
         $query .= "`imagefile` = :image, ";
      else if( $image == 'null' )
         $query .= '`imagefile` = null, ';
      $query .= "`added` = CURDATE() WHERE `affiliateid` = :id";
      }
   else {
      // get info
      $query = "SELECT * FROM `$db_owned` WHERE `listingid` = :listing";
      $result = $db_link->prepare($query);
      $result->bindParam(':listing', $listing, PDO::PARAM_INT);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $info = $result->fetch();
      $table = $info['dbtable'];
      $dbserver = $info['dbserver'];
      $dbdatabase = $info['dbdatabase'];
      $dbuser = $info['dbuser'];
      $dbpassword = $info['dbpassword'];

      $db_link = new PDO('mysql:host=' . $dbserver . ';dbname=' . $dbdatabase . ';charset=utf8', $dbuser, $dbpassword);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $afftable = $table . '_affiliates';
      $query = "UPDATE `$afftable` SET ";
      if( $url )
         $query .= "`url` = :url, ";
      if( $title )
         $query .= "`title` = :title, ";
      if( $email )
         $query .= "`email` = :email, ";
      if( $image != '' && $image != 'null' )
         $query .= "`imagefile` = :image, ";
      else if( $image == 'null' )
         $query .= '`imagefile` = null, ';
      $query .= "`added` = CURDATE() WHERE `affiliateid` = :id";
   }

   $result = $db_link->prepare($query);
   $result->bindParam(':url', $url, PDO::PARAM_STR);
   $result->bindParam(':title', $title, PDO::PARAM_STR);
   $result->bindParam(':email', $email, PDO::PARAM_STR);
   $result->bindParam(':image', $image, PDO::PARAM_STR);
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
function delete_affiliate( $id, $listing = '' ) {
   require 'config.php';
   $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
   $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   $query = "DELETE FROM `$db_affiliates` WHERE `affiliateid` = :id";
   if( $listing != '' && $listing != 'collective' ){
      // get info
      $query = "SELECT * FROM `$db_owned` WHERE `listingid` = :listing";
      $result = $db_link->prepare($query);
      $result->bindParam(':listing', $listing, PDO::PARAM_INT);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $info = $result->fetch();
      $table = $info['dbtable'];
      $dbserver = $info['dbserver'];
      $dbdatabase = $info['dbdatabase'];
      $dbuser = $info['dbuser'];
      $dbpassword = $info['dbpassword'];

      $db_link = new PDO('mysql:host=' . $dbserver . ';dbname=' . $dbdatabase . ';charset=utf8', $dbuser, $dbpassword);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $afftable = $table . '_affiliates';
      $query = "DELETE FROM `$afftable` WHERE `affiliateid` = :id";
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
function get_affiliate_info( $id, $listing = 'collective' ) {
   require 'config.php';

   $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
   $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   $query = 'SELECT `url`, `title`, `imagefile`, `email` ' .
      "FROM `$db_affiliates` WHERE `affiliateid` = :id";
   if( $listing && $listing != 'collective' ) {
      // get info
      $query = "SELECT * FROM `$db_owned` WHERE `listingid` = :listing";
      $result = $db_link->prepare($query);
      $result->bindParam(':listing', $listing, PDO::PARAM_INT);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $info = fetch();
      $table = $info['dbtable'];
      $dbserver = $info['dbserver'];
      $dbdatabase = $info['dbdatabase'];
      $dbuser = $info['dbuser'];
      $dbpassword = $info['dbpassword'];

      $db_link = new PDO('mysql:host=' . $dbserver . ';dbname=' . $dbdatabase . ';charset=utf8', $dbuser, $dbpassword);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $afftable = $table . '_affiliates';
      $query = "SELECT `url`, `title`, `imagefile`, `email` FROM `$afftable`" .
         " WHERE `affiliateid` = :id";
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
   return $result->fetch();
}


/*___________________________________________________________________________*/
function parse_affiliate_add_email( $id, $listing = '' ) {
   require 'config.php';

   $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
   $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   // get owner name
   $query = "SELECT `value` FROM `$db_settings` WHERE `setting` = " .
      "'owner_name'";
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
   $name = $row['value'];

   // get template
   $query = "SELECT * FROM `$db_emailtemplate` WHERE `templateid` = 1";
   $result = $db_link->prepare($query);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $result->setFetchMode(PDO::FETCH_ASSOC);
   $template = $result->fetch();

   $info = array();
   $title = '';
   $url = '';
   $email = '';
   if( $listing == '' || $listing == 'collective' ) {
      // get affiliate info
      $query = "SELECT * FROM `$db_affiliates` WHERE `affiliateid` = :id";
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
      $info = $result->fetch();

      // get collective values
      $query = "SELECT `setting`, `value` FROM `$db_settings` WHERE " .
         "`setting` = 'collective_title' OR `setting` = 'collective_url' OR " .
         "`setting` = 'owner_email'";
      $result = $db_link->prepare($query);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
      $result->setFetchMode(PDO::FETCH_ASSOC);
      while( $row = $result->fetch() ) {
         switch( $row['setting'] ) {
            case 'collective_title' :
               $title = $row['value']; break;
            case 'collective_url' :
               $url = $row['value']; break;
            case 'owner_email' :
               $email = $row['value']; break;
            default : break;
         }
      }
   } else {
      // get info
      $query = "SELECT * FROM `$db_owned` WHERE `listingid` = :listing";
      $result = $db_link->prepare($query);
      $result->bindParam(':listing', $listing, PDO::PARAM_INT);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $listinginfo = $result->fetch();

      $table = $listinginfo['dbtable'];
      $afftable = $table . '_affiliates';
      $dbserver = $listinginfo['dbserver'];
      $dbdatabase = $listinginfo['dbdatabase'];
      $dbuser = $listinginfo['dbuser'];
      $dbpassword = $listinginfo['dbpassword'];
      $title = $listinginfo['title'];
      $url = $listinginfo['url'];
      $email = $listinginfo['email'];

      $db_link = new PDO('mysql:host=' . $dbserver . ';dbname=' . $dbdatabase . ';charset=utf8', $dbuser, $dbpassword);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $query = "SELECT * FROM `$afftable` WHERE `affiliateid` = :id";
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
      $info = $result->fetch();
   }

   // parse body email template
   // your own site info
   $formatted = str_replace( '$$site_url$$', $url,
      $template['content'] );
   $formatted = str_replace( '$$site_title$$', $title, $formatted );
   $formatted = str_replace( '$$site_owner$$', $name, $formatted );
   $formatted = str_replace( '$$site_email$$', $email, $formatted );

   // your affiliate's site info
   $formatted = str_replace( '$$site_aff_url$$', $info['url'],
      $formatted );
   $formatted = str_replace( '$$site_aff_title$$', $info['title'],
      $formatted );

   // parse subject email template
   // your own site info
   $subject = str_replace( '$$site_url$$', $url, $template['subject'] );
   $subject = str_replace( '$$site_title$$', $title, $subject );
   $subject = str_replace( '$$site_owner$$', $name, $subject );
   $subject = str_replace( '$$site_email$$', $email, $subject );

   // your affiliate's site info
   $subject = str_replace( '$$site_aff_url$$', $info['url'], $subject );
   $subject = str_replace( '$$site_aff_title$$', $info['title'],
      $subject );

   $email = array();
   $email['subject'] = $subject;
   $email['body'] = $formatted;

   return $email;
}


/*___________________________________________________________________________*/
function parse_affiliates_template( $id, $listing = '' ) {
   require 'config.php';

   $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
   $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   // get template
   $query = "SELECT `value` FROM `$db_settings` WHERE `setting` = " .
      "'affiliates_template'";
   $result = $db_link->prepare($query);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $result->setFetchMode(PDO::FETCH_ASSOC);
   $setting = fetch();
   $template = $setting['value'];

   $listing = trim( $listing );

   // get affiliate info
   if( $listing != '' && ctype_digit( $listing ) ) {
      // get info
      $query = "SELECT * FROM `$db_owned` WHERE `listingid` = :listing";
      $result = $db_link->prepare($query);
      $result->bindParam(':listing', $listing, PDO::PARAM_INT);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $listinginfo = $result->fetch();

      $table = $listinginfo['dbtable'];
      $afftable = $table . '_affiliates';
      $dbserver = $listinginfo['dbserver'];
      $dbdatabase = $listinginfo['dbdatabase'];
      $dbuser = $listinginfo['dbuser'];
      $dbpassword = $listinginfo['dbpassword'];
      $template = $listinginfo['affiliatestemplate'];

      if( $dbserver != $db_server || $dbdatabase != $db_database ||
         $dbuser != $db_user || $dbpassword != $db_password ) {
         $db_link = new PDO('mysql:host=' . $dbserver . ';dbname=' . $dbdatabase . ';charset=utf8', $dbuser, $dbpassword);
      }

      $query = "SELECT * FROM `$afftable` WHERE `affiliateid` = :id";
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
      $info = $result->fetch();

      // reconnect to original database now
      if( $dbserver != $db_server || $dbdatabase != $db_database ||
         $dbuser != $db_user || $dbpassword != $db_password ) {
         $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      }

   } else {
      $query = "SELECT * FROM `$db_affiliates` WHERE `affiliateid` = :id";
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
      $info = $result->fetch();
   }

   // get affiliates dir
   $query = "SELECT `setting`, `value` FROM `$db_settings` WHERE `setting` =" .
      ' "affiliates_dir" OR `setting` = "root_path_absolute" OR `setting` = ' .
      '"root_path_web"';
   if( $listing != '' && $listing != 'collective' )
      $query = "SELECT `affiliatesdir` FROM `$db_owned` WHERE " .
         "`listingid` = :listing";
   $result = $db_link->prepare($query);
   $result->bindParam(':listing', $listing, PDO::PARAM_INT);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $dir = '';
   $root_web = '';
   $root_abs = '';
   $result->setFetchMode(PDO::FETCH_ASSOC);
   while( $row = $result->fetch() ) {
      if( $listing )
         $dir = $row['affiliatesdir'];
      else {
         if( $row['setting'] == 'affiliates_dir' )
            $dir = $row['value'];
         else if( $row['setting'] == 'root_path_absolute' )
            $root_abs = $row['value'];
         else
            $root_web = $row['value'];
      }
   }

   if( $root_web == '' && $root_abs == '' ) {
      $query = "SELECT `setting`, `value` FROM `$db_settings` WHERE " .
         '`setting` = "root_path_absolute" OR `setting` = "root_path_web"';
      $result = $db_link->prepare($query);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
      $result->setFetchMode(PDO::FETCH_ASSOC);
      while( $row = $result->fetch() )
         if( $row['setting'] == 'root_path_absolute' )
            $root_abs = $row['value'];
         else
            $root_web = $row['value'];
   }
   $dir_web = str_replace( $root_abs, $root_web, $dir );
   $dir_web = str_replace( '\\', '/', $dir_web );

   if( $info['imagefile'] && @is_file( $dir . $info['imagefile'] ) )
      $image = @getimagesize( $dir . $info['imagefile'] );
   else
      $image = array();
   // make sure $image is an array, in case getimagesize() failed
   if( !is_array( $image ) ) 
      $image = array();

   // collective
   if( $listing == '' || !ctype_digit( $listing ) ) {
      $formatted = str_replace( 'enth3-url', $info['url'], $template );
      $formatted = str_replace( 'enth3-title', $info['title'], $formatted );
      $formatted = str_replace( 'enth3-image', $dir_web . $info['imagefile'],
         $formatted );
      if( count( $image ) > 0 ) {
         $formatted = str_replace( 'enth3-width', $image[0], $formatted );
         $formatted = str_replace( 'enth3-height', $image[1], $formatted );
      }
   } else {
      $formatted = str_replace( '$$aff_url$$', $info['url'], $template );
      $formatted = str_replace( '$$aff_title$$', $info['title'], $formatted );
      $formatted = str_replace( '$$aff_image$$', $dir_web . $info['imagefile'],
         $formatted );
      if( count( $image ) > 0 ) {
         $formatted = str_replace( '$$aff_width$$', $image[0], $formatted );
         $formatted = str_replace( '$$aff_height$$', $image[1], $formatted );
      }
   }

   return $formatted;
}
?>