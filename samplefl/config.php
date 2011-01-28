<?php
/******************************************************************************
 DATABASE VARIABLES
 These are the variables for your listing collective.
  $db_server   - database server, usually localhost
  $db_user     - username for your database
  $db_password - password for your database
  $db_database - actual database name
******************************************************************************/
$db_server = 'localhost';
$db_user = 'username';
$db_password = 'password';
$db_database = 'databasename';



/******************************************************************************
 DATABASE TABLE VARIABLES
 These are the variables for your listing collective's tables. You can leave
 these as they are unless you already use one of the default values as a
 table name in your database previously.
  $db_settings      - table for Enth3 settings
  $db_category      - table for the categories
  $db_joined        - table for your joined listings
  $db_owned         - table for storing information about your owned listings
  $db_affiliates    - table for your *collective* affiliates
  $db_emailtemplate - table for your email templates
  $db_errorlog      - table for your error logs (required even if not set)
******************************************************************************/
$db_settings = 'settings';
$db_category = 'category';
$db_joined = 'joined';
$db_owned = 'owned';
$db_affiliates = 'affiliates';
$db_emailtemplate = 'emailtemplate';
$db_errorlog = 'errorlog';


/******************************************************************************
 DO NOT EDIT ANYTHING BELOW THIS LINE UNTIL THE NEXT SIMILAR NOTE!
******************************************************************************/
if( !defined( 'DATABASE_CONNECT_ERROR' ) )
   define( 'DATABASE_CONNECT_ERROR', 'Cannot connect to the database. ' .
      'Check your config file and try again. MySQL said: ' );
if( !defined( 'STANDARD_ERROR' ) )
   define( 'STANDARD_ERROR', '<p class="error">Error executing query. ' .
      'Please see the error logs.' );
// get installation path
$query = "SELECT `value` FROM `$db_settings` WHERE `setting` = " .
   '"installation_path"';
$db_link = mysql_connect( $db_server, $db_user, $db_password )
   or die( DATABASE_CONNECT_ERROR . mysql_error() );
mysql_select_db( $db_database )
   or die( DATABASE_CONNECT_ERROR . mysql_error() );
$result = mysql_query( $query );
if( !$result ) {
   if( function_exists( 'log_error' ) ) {
      log_error( 'config.php',
         'Error executing query: <i>' . mysql_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
      die( STANDARD_ERROR );
   } else {
      die( 'Error executing query: <i>' . mysql_error() .
         '</i>; Query is: <code>' . $query . '</code>' );
   }
}
$row = mysql_fetch_array( $result );
$path = $row['value'];
if( !defined( 'ENTH_PATH' ) ) {
   define( 'ENTH_PATH', $row['value'] );
}
/******************************************************************************
 END OF THE SENSITIVE LINES
******************************************************************************/




/******************************************************************************
 LISTING ID VARIBLE
 This variable is for the listing ID of the fanlisting this config file is
 for. When this file is in the collective directory, it should be commented
 (must have '//' before the line). Otherwise, it MUST be uncommented (no
 '//' before the line) and the proper listing ID should be set.
******************************************************************************/
$listing = 1;
?>
