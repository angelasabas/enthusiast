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
require 'config.php';

require_once( 'mod_errorlogs.php' );
require_once( 'mod_categories.php' );
require_once( 'mod_joined.php' );
require_once( 'mod_owned.php' );
require_once( 'mod_affiliates.php' );
require_once( 'mod_settings.php' );

$all_owned = get_owned( 'current', 'none', 'bydate' );
$all_joined = get_joined( 'approved', 'none', 'id' );

// total number categories (regardless of if there are stuff inside)
$total_cats = count( enth_get_categories() );

// number of joined listings (pending)
$joined_pending = count( get_joined( 'pending' ) );

// number of joined listings (approved)
$joined_approved = count( get_joined( 'approved' ) );

// number of joined listings (pending+approved)
$joined = $joined_pending + $joined_approved;

// number of owned listings (pending)
$owned_pending = count( get_owned( 'pending' ) );

// number of owned listings (upcoming)
$owned_upcoming = count( get_owned( 'upcoming' ) );

// number of owned listings (current)
$owned_current = count( get_owned( 'current' ) );

// number of owned listings (pending+upcoming+current)
$owned = $owned_pending + $owned_upcoming + $owned_current;

// number of collective affiliates
$affiliates_collective = count( get_affiliates() );

// newest owned fanlisting (current)
if( count( $all_owned ) > 0 )
   $owned_newest = get_listing_info( $all_owned[0] );
else
   $owned_newest = array();

// random owned fanlisting (current)
$index = rand( 0, count( $all_owned ) - 1 );
if( count( $all_owned ) > 0 )
   $owned_random = get_listing_info( $all_owned[$index] );
else
   $owned_random = array();

// newest joined fanlisting (current)
if( count( $all_joined ) > 0 )
   $joined_newest = get_joined_info( $all_joined[0] );
else
   $joined_newest = array();

// random joined fanlisting (current)
$index = rand( 0, count( $all_joined ) - 1 );
if( count( $all_joined ) > 0 )
   $joined_random = get_joined_info( $all_joined[$index] );
else
   $joined_random = array();

// collective total fans (approved)
$collective_total_fans_approved = 0;
$ownedarray = get_owned( 'current' );
$query = '';
foreach( $ownedarray as $o ) {
   $info = get_listing_info( $o );
   $table = $info['dbtable'];
   $dbserver = $info['dbserver'];
   $dbdatabase = $info['dbdatabase'];
   $dbuser = $info['dbuser'];
   $dbpassword = $info['dbpassword'];

   if( $dbserver != $db_server || $dbdatabase != $db_database ||
      $dbuser != $db_user || $dbpassword != $db_password ) {
      // if not on same database, get counts NOW except if it can't be accessed; if not, skip this one
      $db_link = new PDO('mysql:host=' . $db_server . ';charset=utf8', $db_user, $db_password);
	  if( $db_link === false )
	  	continue; // if it can't be accessed; if not, skip this one
      $connected = $db_link->query('USE ' . $dbdatabase);
      if( !$connected )
         continue; // if it can't be accessed; if not, skip this one
      $thisone = "SELECT COUNT(*) AS `total` FROM `$table` WHERE " .
         '`pending` = 0';
      $result = $db_link->prepare($thisone);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $row = $result->fetch();
      $collective_total_fans_approved += $row['total'];
   } else {
      $query .= "SELECT COUNT(*) AS `rowcount` FROM `$table` WHERE " .
         '`pending` = 0';
      $query .= " !!! ";
   }
}
$query = rtrim( $query, "! " );
$query = str_replace( '!!!', 'UNION ALL', $query );

try {
   $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
   $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
   die( DATABASE_CONNECT_ERROR . $e->getMessage() );
}
if( $query != '' ) { // if there IS a query
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
      $collective_total_fans_approved += $row['rowcount'];
   }
}

// collective total fans (pending)
$collective_total_fans = 0;
$ownedarray = get_owned( 'current' );
$query = '';
foreach( $ownedarray as $o ) {
   $info = get_listing_info( $o );
   $table = $info['dbtable'];
   $dbserver = $info['dbserver'];
   $dbdatabase = $info['dbdatabase'];
   $dbuser = $info['dbuser'];
   $dbpassword = $info['dbpassword'];

   if( $dbserver != $db_server || $dbdatabase != $db_database ||
      $dbuser != $db_user || $dbpassword != $db_password ) {
      // if not on same database, get counts NOW
      $db_link = new PDO('mysql:host=' . $db_server . ';charset=utf8', $db_user, $db_password);
	  if( $db_link === false )
	  	continue; // if it can't be accessed; if not, skip this one
      $connected = $db_link->query('USE ' . $dbdatabase);
      if( !$connected )
         continue; // if it can't be accessed; if not, skip this one
      $thisone = "SELECT COUNT(`email`) AS `total` FROM `$table`";
      $result = $db_link->prepare($thisone);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         die( STANDARD_ERROR );
      }
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $row = $result->fetch();
      $collective_total_fans += $row['total'];
   } else {
      $query .= "SELECT COUNT(`email`) AS `rowcount` FROM `$table`";
      $query .= " !!! ";
   }
}
$query = rtrim( $query, '! ' ); //echo $query;
$query = str_replace( '!!!', 'UNION ALL', $query );

try {
   $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
   $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
   die( DATABASE_CONNECT_ERROR . $e->getMessage() );
}

if( $query != '' ) {
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
      $collective_total_fans += $row['rowcount'];
   }
}

// collective total fans (pending)
$collective_total_fans_pending = $collective_total_fans -
   $collective_total_fans_approved;

// owned growth rate (current + upcoming)
// get the earliest opened owned listing
$query = "SELECT YEAR( `opened` ) AS `year`, MONTH( `opened` ) AS `month`, " .
   "DAYOFMONTH( `opened` ) AS `day` FROM `$db_owned` WHERE " .
   "`status` != 0 AND `opened` != '0000-00-00' ORDER BY `opened` ASC LIMIT 1";
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
$owned_growth_rate = 0;
$days = 1;
if( $row && count( $row ) > 0 ) {
   $firstyear = $row['year'];
   $firstmonth = $row['month'];
   $firstday = $row['day'];
   $today = getdate();
   $first = getdate( mktime( 0, 0, 0, $firstmonth, $firstday, $firstyear ) );
   $seconds = $today[0] - $first[0];
   $days = round( $seconds / 86400 );
   if( $days == 0 )
      $days = 1;
   $owned_growth_rate = round(($owned_upcoming + $owned_current) / $days, 2);
}

// collective (fans) growth rate (current/approved)
$collective_fans_growth_rate = round( $collective_total_fans_approved / $days,
   2);


// joined growth rate
$query = "SELECT YEAR( `added` ) AS `year`, MONTH( `added` ) AS `month`, " .
   "DAYOFMONTH( `added` ) AS `day` FROM `$db_joined` WHERE " .
   "`added` != '0000-00-00' ORDER BY `added` ASC LIMIT 1";
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
$joined_growth_rate = 0;
$days = 1;
if( $row && count( $row ) > 0 ) {
   $firstyear = $row['year'];
   $firstmonth = $row['month'];
   $firstday = $row['day'];
   $first = getdate( mktime( 0, 0, 0, $firstmonth, $firstday, $firstyear ) );
   $seconds = $today[0] - $first[0];
   $days = round( $seconds / 86400 );
   if( $days == 0 )
      $days = 1;
   $joined_growth_rate = round( $joined / $days, 2);
}
?>
