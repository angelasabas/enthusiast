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

// clean function
function clean( $data, $leavehtml = false ) {
   if( $leavehtml )
      $data = trim( $data );
   else
      $data = trim( htmlentities( strip_tags( $data ), ENT_QUOTES ) );

   if( get_magic_quotes_gpc() )
      $data = stripslashes( $data );

   $data = addslashes( $data );

   return $data;
}

// automatically clean inputs
foreach( $_GET as $index => $value ) {
   $_GET[$index] = clean( $value );
}
foreach( $_POST as $index => $value ) {
   // if the index has "template" or "desc" in it, leave it be!
   $leavehtml = false;
   if( substr_count( $index, 'template' ) ||
      substr_count( $index, 'header' ) ||
      substr_count( $index, 'footer' ) ||
      substr_count( $index, 'desc' ) ||
      substr_count( $index, 'content' ) ||
      substr_count( $index, 'emailbody' ) )
      $leavehtml = true;
   if( is_array( $value ) ) {
      foreach( $value as $i => $v ) {
         $value[$i] = clean( $v, $leavehtml );
      }
      $_POST[$index] = $value;
   } else
      $_POST[$index] = clean( $value, $leavehtml );
}
foreach( $_COOKIE as $index => $value ) {
   $_COOKIE[$index] = clean( $value );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title> Enthusiast 3.1 ~ Listing Collective Management System </title>
<meta name="author" content="Angela Maria Protacia M. Sabas" />
<meta http-equiv="content-type" content="application/xhtml+xml; 
charset=iso-8859-1" />
<meta http-equiv="imagetoolbar" content="no" />
<link rel="stylesheet" type="text/css" href="style.css" />
<script src="js.js" type="text/javascript" language="javascript"></script>
</head>
<body>

<div class="header">
<img src="logo.gif" width="190" height="60" alt="" />
<div class="topmenu">
<a href="dashboard.php">Dashboard</a>
<a href="settings.php">Settings</a>
<?php echo ( isset( $_COOKIE['e3login'] ) && isset( $_SESSION['logerrors'] ) &&
   $_SESSION['logerrors'] == 'yes' )
   ? '<a href="errorlog.php">Error Log</a> ' : '' ?>
<a href="logout.php">Logout</a>
</div>
</div>

<div class="container"><div class="content">