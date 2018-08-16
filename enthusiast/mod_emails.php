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
function get_email_templates() {
   require 'config.php';

   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }

   $query = "SELECT `templateid` FROM `$db_emailtemplate`";

   $result = $db_link->prepare($query);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }

   $templates = array();
   $result->setFetchMode(PDO::FETCH_ASSOC);
   while( $row = $result->fetch() )
      $templates[] = $row['templateid'];
   return $templates;
}


/*___________________________________________________________________________*/
function get_template_info( $id ) {
   require 'config.php';

   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }

   $query = "SELECT * FROM `$db_emailtemplate` WHERE `templateid` = :id";

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
function add_template( $name, $subject, $content ) {
   require 'config.php';

   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }

   $query = "INSERT INTO `$db_emailtemplate` VALUES( " .
      "null, :name, :subject, :content, 1 )";

   $result = $db_link->prepare($query);
   $result->bindParam(':name', $name, PDO::PARAM_STR);
   $result->bindParam(':subject', $subject, PDO::PARAM_STR);
   $result->bindParam(':content', $content, PDO::PARAM_STR);
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
function edit_template( $id, $name, $subject, $content ) {
   require 'config.php';

   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }

   $query = "UPDATE `$db_emailtemplate` SET `templatename` = :name, " .
      "`subject` = :subject, `content` = :content WHERE " .
      "`templateid` = :id";

   $result = $db_link->prepare($query);
   $result->bindParam(':name', $name, PDO::PARAM_STR);
   $result->bindParam(':subject', $subject, PDO::PARAM_STR);
   $result->bindParam(':content', $content, PDO::PARAM_STR);
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
function delete_template( $id ) {
   require 'config.php';

   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }

   $query = "DELETE FROM `$db_emailtemplate` WHERE `templateid` = :id";

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
function parse_template( $templateid, $email, $listing, $affid = 0 ) {
   require 'config.php';

   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }

   $query = "SELECT * FROM `$db_emailtemplate` WHERE " .
      "`templateid` = :templateid";
   $result = $db_link->prepare($query);
   $result->bindParam(':templateid', $templateid, PDO::PARAM_INT);
   $result->execute();
   if( !$result ) {
      log_error( __FILE__ . ':' . __LINE__,
         'Error executing query: <i>' . $result->errorInfo()[2] .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   }
   $result->setFetchMode(PDO::FETCH_ASSOC);
   $row = $result->fetch();
   $subject = $row['subject'];
   $body = $row['content'];

   if( $listing != '' && ctype_digit( $listing ) ) {
      // it's a fanlisting, get listing info
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

      $subject = str_replace( '$$fanlisting_title$$',
         html_entity_decode( $info['title'], ENT_QUOTES ), $subject );
      $subject = str_replace( '$$fanlisting_url$$', $info['url'],
         $subject );
      $subject = str_replace( '$$fanlisting_subject$$',
         html_entity_decode( $info['subject'], ENT_QUOTES ),
         $subject );
      $subject = str_replace( '$$fanlisting_owner$$', get_setting(
         'owner_name' ), $subject );
      $subject = str_replace( '$$fanlisting_email$$', $info['email'],
         $subject );
      $subject = str_replace( '$$fanlisting_update$$', $info['updatepage'],
         $subject );
      $subject = str_replace( '$$fanlisting_join$$', $info['joinpage'],
         $subject );
      $subject = str_replace( '$$fanlisting_list$$', $info['listpage'],
         $subject );
      $subject = str_replace( '$$fanlisting_lostpass$$', $info['lostpasspage'],
         $subject );
      $subject = str_replace( '$$listing_type$$',
         html_entity_decode( $info['listingtype'], ENT_QUOTES ),
         $subject );

      $body = str_replace( '$$fanlisting_title$$',
         html_entity_decode( $info['title'], ENT_QUOTES ),
         $body );
      $body = str_replace( '$$fanlisting_url$$', $info['url'],
         $body );
      $body = str_replace( '$$fanlisting_subject$$',
         html_entity_decode( $info['subject'], ENT_QUOTES ),
         $body );
      $body = str_replace( '$$fanlisting_owner$$', get_setting(
         'owner_name' ), $body );
      $body = str_replace( '$$fanlisting_email$$', $info['email'],
         $body );
      $body = str_replace( '$$fanlisting_update$$', $info['updatepage'],
         $body );
      $body = str_replace( '$$fanlisting_join$$', $info['joinpage'],
         $body );
      $body = str_replace( '$$fanlisting_list$$', $info['listpage'],
         $body );
      $body = str_replace( '$$fanlisting_lostpass$$', $info['lostpasspage'],
         $body );
      $body = str_replace( '$$listing_type$$',
         html_entity_decode( $info['listingtype'], ENT_QUOTES ),
         $body );

      $table = $info['dbtable'];
      $dbserver = $info['dbserver'];
      $dbdatabase = $info['dbdatabase'];
      $dbuser = $info['dbuser'];
      $dbpassword = $info['dbpassword'];

      try {
         $db_link = new PDO('mysql:host=' . $dbserver . ';dbname=' . $dbdatabase . ';charset=utf8', $dbuser, $dbpassword);
         $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
         die( DATABASE_CONNECT_ERROR . $e->getMessage() );
      }

      if( !ctype_digit( $affid ) || $affid == 0 ) {
         // its a member being emailed, get member info
         $query = "SELECT * FROM `$table` WHERE `email` = :email";
         $result = $db_link->prepare($query);
         $result->bindParam(':email', $email, PDO::PARAM_STR);
         $result->execute();
         if( !$result ) {
            log_error( __FILE__ . ':' . __LINE__,
               'Error executing query: <i>' . $result->errorInfo()[2] .
               '</i>; Query is: <code>' . $query . '</code>' );
            die( STANDARD_ERROR );
         }
         $result->setFetchMode(PDO::FETCH_ASSOC);
         $row = $result->fetch();

         $subject = str_replace( '$$fan_email$$', $row['email'], $subject );
         $subject = str_replace( '$$fan_name$$', $row['name'], $subject );
         $subject = str_replace( '$$fan_country$$', $row['country'],
            $subject );
         $subject = str_replace( '$$fan_url$$', $row['url'], $subject );
         if( $info['additional'] != '' )
            foreach( explode( ',', $info['additional'] ) as $field )
               $subject = str_replace( '$$fan_' . $field . '$$',
                  $row[$field], $subject );

         $body = str_replace( '$$fan_email$$', $row['email'], $body );
         $body = str_replace( '$$fan_name$$', $row['name'], $body );
         $body = str_replace( '$$fan_country$$', $row['country'], $body );
         $body = str_replace( '$$fan_url$$', $row['url'], $body );
         if( $info['additional'] != '' )
            foreach( explode( ',', $info['additional'] ) as $field )
               $body = str_replace( '$$fan_' . $field . '$$',
                  $row[$field], $body );
      } else {
         // its an affiliate being emailed, get affiliate info
         $afftable = $table . '_affiliates';
         $query = "SELECT * FROM `$afftable` WHERE affiliateid = :affid";
         $result = $db_link->prepare($query);
         $result->bindParam(':affid', $affid, PDO::PARAM_INT);
         $result->execute();
         if( !$result ) {
            log_error( __FILE__ . ':' . __LINE__,
               'Error executing query: <i>' . $result->errorInfo()[2] .
               '</i>; Query is: <code>' . $query . '</code>' );
            die( STANDARD_ERROR );
         }
         $result->setFetchMode(PDO::FETCH_ASSOC);
         $row = $result->fetch();

         $subject = str_replace( '$$aff_email$$', $row['email'], $subject );
         $subject = str_replace( '$$aff_id$$', $row['affiliateid'], $subject );
         $subject = str_replace( '$$aff_url$$', $row['url'], $subject );
         $subject = str_replace( '$$aff_title$$', html_entity_decode( $row['title'],
            ENT_QUOTES ), $subject );

         $body = str_replace( '$$aff_email$$', $row['email'], $body );
         $body = str_replace( '$$aff_id$$', $row['affiliateid'], $body );
         $body = str_replace( '$$aff_url$$', $row['url'], $body );
         $body = str_replace( '$$aff_title$$', html_entity_decode( $row['title'],
            ENT_QUOTES ), $body );
       }
   } else {
      // it's a collective affiliate we're emailing, probably!
      // get affiliate info
      $query = "SELECT * FROM `$db_affiliates` WHERE `affiliateid` = :affid";
      $result = $db_link->prepare($query);
      $result->bindParam(':affid', $affid, PDO::PARAM_INT);
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
         "`setting` = 'owner_email' OR `setting` = 'owner_name'";
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
            case 'owner_name' :
               $name = $row['value']; break;
            default : break;
         }
      }

      // subject
      $subject = str_replace( '$$site_url$$', $url, $subject );
      $subject = str_replace( '$$site_title$$', html_entity_decode( $title,
         ENT_QUOTES ), $subject );
      $subject = str_replace( '$$site_owner$$', $name, $subject );
      $subject = str_replace( '$$site_email$$', $email, $subject );
      $subject = str_replace( '$$site_aff_url$$', $info['url'], $subject );
      $subject = str_replace( '$$site_aff_title$$',
         html_entity_decode( $info['title'], ENT_QUOTES ), $subject );

      // body
      $body = str_replace( '$$site_url$$', $url, $body );
      $body = str_replace( '$$site_title$$', html_entity_decode( $title,
         ENT_QUOTES ), $body );
      $body = str_replace( '$$site_email$$', $email, $body );
      $body = str_replace( '$$site_aff_url$$', $info['url'], $body );
      $body = str_replace( '$$site_aff_title$$',
         html_entity_decode( $info['title'], ENT_QUOTES ), $body );
   }

   $sendthis = array();
   $sendthis['subject'] = $subject;
   $sendthis['body'] = $body;
   return $sendthis;
}

/*___________________________________________________________________________*/
function parse_email_text( $subject, $body, $email, $listing, $affid = 0 ) {
   require 'config.php';

   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }

   if( $listing != '' && ctype_digit( $listing ) ) {
      // it's a fanlisting, get listing info
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

      $subject = str_replace( '$$fanlisting_title$$',
         html_entity_decode( $info['title'], ENT_QUOTES ),
         $subject );
      $subject = str_replace( '$$fanlisting_url$$', $info['url'],
         $subject );
      $subject = str_replace( '$$fanlisting_subject$$',
         html_entity_decode( $info['subject'], ENT_QUOTES ),
         $subject );
      $subject = str_replace( '$$fanlisting_owner$$', get_setting(
         'owner_name' ), $subject );
      $subject = str_replace( '$$fanlisting_email$$', $info['email'],
         $subject );
      $subject = str_replace( '$$fanlisting_update$$', $info['updatepage'],
         $subject );
      $subject = str_replace( '$$fanlisting_join$$', $info['joinpage'],
         $subject );
      $subject = str_replace( '$$fanlisting_list$$', $info['listpage'],
         $subject );
      $subject = str_replace( '$$fanlisting_lostpass$$', $info['lostpasspage'],
         $subject );
      $subject = str_replace( '$$listing_type$$',
         html_entity_decode( $info['listingtype'], ENT_QUOTES ),
         $subject );

      $body = str_replace( '$$fanlisting_title$$',
         html_entity_decode( $info['title'], ENT_QUOTES ),
         $body );
      $body = str_replace( '$$fanlisting_url$$', $info['url'],
         $body );
      $body = str_replace( '$$fanlisting_subject$$',
         html_entity_decode( $info['subject'], ENT_QUOTES ),
         $body );
      $body = str_replace( '$$fanlisting_owner$$', get_setting(
         'owner_name' ), $body );
      $body = str_replace( '$$fanlisting_email$$', $info['email'],
         $body );
      $body = str_replace( '$$fanlisting_update$$', $info['updatepage'],
         $body );
      $body = str_replace( '$$fanlisting_join$$', $info['joinpage'],
         $body );
      $body = str_replace( '$$fanlisting_list$$', $info['listpage'],
         $body );
      $body = str_replace( '$$fanlisting_lostpass$$', $info['lostpasspage'],
         $body );
      $body = str_replace( '$$listing_type$$',
         html_entity_decode( $info['listingtype'], ENT_QUOTES ),
         $body );

      $table = $info['dbtable'];
      $dbserver = $info['dbserver'];
      $dbdatabase = $info['dbdatabase'];
      $dbuser = $info['dbuser'];
      $dbpassword = $info['dbpassword'];

      try {
         $db_link = new PDO('mysql:host=' . $dbserver . ';dbname=' . $dbdatabase . ';charset=utf8', $dbuser, $dbpassword);
         $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
         die( DATABASE_CONNECT_ERROR . $e->getMessage() );
      }

      if( !ctype_digit( $affid ) || $affid == 0 ) {
         // its a member being emailed, get member info
         $query = "SELECT * FROM `$table` WHERE `email` = :email";
         $result = $db_link->prepare($query);
         $result->bindParam(':email', $email, PDO::PARAM_STR);
         $result->execute();
         if( !$result ) {
            log_error( __FILE__ . ':' . __LINE__,
               'Error executing query: <i>' . $result->errorInfo()[2] .
               '</i>; Query is: <code>' . $query . '</code>' );
            die( STANDARD_ERROR );
         }
         $result->setFetchMode(PDO::FETCH_ASSOC);
         $row = $result->fetch();

         $subject = str_replace( '$$fan_email$$', $row['email'], $subject );
         $subject = str_replace( '$$fan_name$$',
            html_entity_decode( $row['name'], ENT_QUOTES ), $subject );
         $subject = str_replace( '$$fan_country$$', $row['country'],
            $subject );
         $subject = str_replace( '$$fan_url$$', $row['url'], $subject );
         if( $info['additional'] != '' ) {
            foreach( explode( ',', $info['additional'] ) as $field ) {
               $subject = str_replace( '$$fan_' . $field . '$$',
                  html_entity_decode( $row[$field], ENT_QUOTES ),
                  $subject );
            }
         }

         $body = str_replace( '$$fan_email$$', $row['email'], $body );
         $body = str_replace( '$$fan_name$$',
            html_entity_decode( $row['name'], ENT_QUOTES ), $body );
         $body = str_replace( '$$fan_country$$', $row['country'], $body );
         $body = str_replace( '$$fan_url$$', $row['url'], $body );
         if( $info['additional'] != '' )
            foreach( explode( ',', $info['additional'] ) as $field )
               $body = str_replace( '$$fan_' . $field . '$$',
                  html_entity_decode( $row[$field], ENT_QUOTES ), $body );
      } else {
         // its an affiliate being emailed, get affiliate info
         $afftable = $table . '_affiliates';
         $query = "SELECT * FROM `$afftable` WHERE affiliateid = :affid";
         $result = $db_link->prepare($query);
         $result->bindParam(':affid', $affid, PDO::PARAM_INT);
         $result->execute();
         if( !$result ) {
            log_error( __FILE__ . ':' . __LINE__,
               'Error executing query: <i>' . $result->errorInfo()[2] .
               '</i>; Query is: <code>' . $query . '</code>' );
            die( STANDARD_ERROR );
         }
         $result->setFetchMode(PDO::FETCH_ASSOC);
         $row = $result->fetch();

         $subject = str_replace( '$$aff_email$$', $row['email'], $subject );
         $subject = str_replace( '$$aff_id$$', $row['affiliateid'], $subject );
         $subject = str_replace( '$$aff_url$$', $row['url'], $subject );
         $subject = str_replace( '$$aff_title$$',
            html_entity_decode( $row['title'], ENT_QUOTES ), $subject );

         $body = str_replace( '$$aff_email$$', $row['email'], $body );
         $body = str_replace( '$$aff_id$$', $row['affiliateid'], $body );
         $body = str_replace( '$$aff_url$$', $row['url'], $body );
         $body = str_replace( '$$aff_title$$',
            html_entity_decode( $row['title'], ENT_QUOTES ), $body );
       }
   } else {
      // it's a collective affiliate we're emailing, probably!
      // get affiliate info
      $query = "SELECT * FROM `$db_affiliates` WHERE `affiliateid` = :affid";
      $result = $db_link->prepare($query);
      $result->bindParam(':affid', $affid, PDO::PARAM_INT);
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
         "`setting` = 'owner_email' OR `setting` = 'owner_name'";
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
            case 'owner_name' :
               $name = $row['value']; break;
            default : break;
         }
      }

      // subject
      $subject = str_replace( '$$site_url$$', $url, $subject );
      $subject = str_replace( '$$site_title$$',
         html_entity_decode( $title, ENT_QUOTES ), $subject );
      $subject = str_replace( '$$site_owner$$', $name, $subject );
      $subject = str_replace( '$$site_email$$', $email, $subject );
      $subject = str_replace( '$$site_aff_url$$', $info['url'], $subject );
      $subject = str_replace( '$$site_aff_title$$',
         html_entity_decode( $info['title'], ENT_QUOTES ), $subject );

      // body
      $body = str_replace( '$$site_url$$', $url, $body );
      $body = str_replace( '$$site_title$$',
         html_entity_decode( $title, ENT_QUOTES ), $body );
      $body = str_replace( '$$site_owner$$', $name, $body );
      $body = str_replace( '$$site_email$$', $email, $body );
      $body = str_replace( '$$site_aff_url$$', $info['url'], $body );
      $body = str_replace( '$$site_aff_title$$',
         html_entity_decode( $info['title'], ENT_QUOTES ), $body );
   }

   $sendthis = array();
   $sendthis['subject'] = $subject;
   $sendthis['body'] = $body;
   return $sendthis;
}


// simple function to handle mail sending talaga :p
function send_email( $to, $from, $subject, $body ) {
   require 'config.php';
   if( !class_exists( 'Mail' ) ) {
      include_once( 'Mail.php' );
   }

   try {
      $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
      $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
      die( DATABASE_CONNECT_ERROR . $e->getMessage() );
   }

   // get email settings
   $settingq = "SELECT `value` FROM `$db_settings` WHERE `setting` = " .
      "'mail_settings'";
   $result = $db_link->prepare($settingq);
   $result->execute();
   $result->setFetchMode(PDO::FETCH_ASSOC);
   $row = $result->fetch();
   $use_mailer = ( count( $row ) ) ? $row['value'] : 'php';

   // php: use native php
   // sendmail: use sendmail
   // smtp: use smtp

   $mail_sent = false;   
   if( $use_mailer == 'sendmail' ) {
      // get sendmail settings
      $settingq = "SELECT `value` FROM `$db_settings` WHERE `setting` = " .
         "'sendmail_path'";
      $result = $db_link->prepare($settingq);
      $result->execute();
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $row = $result->fetch();
      $sendmail_path = ( count( $row ) ) ? $row['value'] : '/usr/bin/sendmail';
      
      // setup pear mail
      $headers = array( 'From' => $from,
         'To' => $to,
         'Subject' => $subject,
         'X-Mailer' => 'PHP 4.x',
         'Content-type' => 'text/plain; charset=iso-8859-1' );
      $mailparams['sendmail_path'] = $sendmail_path;
      $mail =& Mail::factory( 'sendmail', $mailparams );

      $emailed = $mail->send( $to, $headers, $body );
      if( $emailed !== true ) {
         // PEAR Mail didn't go through! We have to log this, and then
         // attempt to send an email through the native mail() method
         log_error( __FILE__ . ':' . __LINE__,
            "Email sending to $to failed. PEAR Mail returned this " .
            'error: <i>' . $emailed->message . '</i>', false );
      } else {
         $mail_sent = true;
      }

   } else if( $use_mailer == 'smtp' ) {
      // get smtp settings
      $settingq = "SELECT `setting`, `value` FROM `$db_settings` WHERE " .
         "`setting` LIKE 'smtp_%'";
      $result = $db_link->prepare($settingq);
      $result->execute();
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $smtp_host = '';
      $smtp_port = '';
      $smtp_auth = '';
      $smtp_username = '';
      $smtp_password = '';
      while( $row = $result->fetch() ) {
         $$row['setting'] = $row['value'];
      }
      
      // setup pear mail
      $headers = array( 'From' => $from,
         'To' => $to,
         'Subject' => $subject,
         'X-Mailer' => 'PHP 4.x',
         'Content-type' => 'text/plain; charset=iso-8859-1' );
      $mailparams['host'] = $smtp_host;
      $mailparams['post'] = $smtp_port;
      $mailparams['auth'] = ( $smtp_auth == 'yes' ) ? true : false;
      $mailparams['username'] = $smtp_username;
      $mailparams['password'] = $smtp_password;
      $mail =& Mail::factory( 'smtp', $mailparams );

      $emailed = $mail->send( $to, $headers, $body );
      if( $emailed !== true ) {
         // PEAR Mail didn't go through! We have to log this, and then
         // attempt to send an email through the native mail() method
         log_error( __FILE__ . ':' . __LINE__,
            "Email sending to $to failed. PEAR Mail returned this " .
            'error: <i>' . $emailed->message . '</i>', false );
      } else {
         $mail_sent = true;
      }
   } // end if sendmail or smtp
   
   if( !$mail_sent || $use_mailer == 'php' ) {
      $headers = "From: $from\r\n";
      $success = @mail( $to, $subject, $body, $headers );
      if( !$success ) {
         // We're still having an error sending through mail()!
         log_error( __FILE__ . ':' . __LINE__,
            "Email sending to $to failed using native mail().", false );
      } else {
         $mail_sent = true;
      }
   }
   
   return $mail_sent;
}
?>