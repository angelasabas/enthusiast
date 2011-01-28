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
require_once 'config.php';

require_once( 'mod_errorlogs.php' );
require_once( 'mod_owned.php' );
require_once( 'mod_members.php' );
require_once( 'mod_settings.php' );

// function to clean data
if( !function_exists( 'clean' ) ) {
   function clean( $data ) {
      $data = trim( htmlentities( strip_tags( $data ), ENT_QUOTES ) );

      if( get_magic_quotes_gpc() )
         $data = stripslashes( $data );

      $data = addslashes( $data );

      return $data;
   }
}

// get listing info, start pagination at what index, and member type
$info = get_listing_info( $listing );
$start = ( isset( $_REQUEST['start'] ) && ctype_digit( $_REQUEST['start'] ) )
   ? $_REQUEST['start'] : '0';
$member_type = ( $info['listingtype'] == 'fanlisting' ) ? 'fans' : 'members';

// get selected members (selection is from $_GET array)
$members = array();
$total = 0;

// get sorting criteria
$sort = explode( ',', $info['sort'] );
foreach( $sort as $i => $s ) $sort[$i] = trim( $s );
$sortarray = array();
$sortselectednum = 0;

// create sort array if wanted; otherwise show everything
if( isset( $no_sort ) && $no_sort ) {
   $members = get_members( $listing, 'approved', array(), $start );
   $total = count( get_members( $listing, 'approved' ) );
} else {
   // find out how to sort members
   foreach( $sort as $s ) {
      if( !$s ) continue; // blank, skip this
      if( isset( $_GET[$s] ) ) { // if the field is set
         if( $_GET[$s] == 'all' ) // if "all", use wildcard
            $sortarray[$s] = '%';
         else
            $sortarray[$s] = clean( $_GET[$s] );
         $sortselectednum++;
      } else // use wildcard
         $sortarray[$s] = '%';
   }
   $members = get_members( $listing, 'approved', $sortarray, $start );
   $total = count( get_members( $listing, 'approved', $sortarray ) );
}

// we need to show the showing dropdown/list selection if there are more fields
// to sort than is already selected
if( count( $sortarray ) > $sortselectednum ) {
   $oldsort = $sort; // meh, variable overwriting
   require_once( 'show_sort.php' );
   $sort = $oldsort; // meh, variable overwriting
}

// are we hiding the members until every sorting field has been selected?
// if yes, exit the script now, we don't need to continue
if( isset( $hide_members_until_final ) && $hide_members_until_final )
   if( count( $sortarray ) > $sortselectednum )
      return;

// show the "showing all xxx" line
if( isset( $no_sort ) && $no_sort )
   echo '<p class="show_members_showing_what">Showing all ' . $member_type .
      '...</p>';
else {
   if( true ) {
      echo '<p class="show_members_showing_what">Showing ';
      $showstring = '';
      foreach( $sort as $s ) {
         if( !$s ) continue;
         if( !isset( $_GET[$s] ) ) continue;
         $showstring .= ucwords( str_replace( '_', ' ', $s ) );
         $showstring .= ': ';
         $showstring .= clean( $_GET[$s] );
         $showstring .= ', ';
      }
      $showstring = rtrim( $showstring, ', ' );
      $showstring = str_replace( '%', 'All', $showstring );
      echo "$showstring $member_type...</p>";
   }
}

// show the actual members list now
// parse list template
foreach( $members as $mem ) {
   $template = $info['listtemplate'];

   // set name
   $formatted = str_replace( '$$fan_name$$', $mem['name'], $template );

   // set country
   if( !in_array( 'country', $sort ) || ( isset( $show_sort_field ) &&
      $show_sort_field ) ) {
      // if country is not set a sorting field
      // or you wanna show the fields anyway
      $formatted = str_replace( '$$fan_country$$', $mem['country'],
         $formatted );
   } else {
      if( ( ( !isset( $_GET['country'] ) || $_GET['country'] == '' ) &&
         isset( $no_sort ) ) || !isset( $_GET['country'] ) ||
         $_GET['country'] == 'all' || $_GET['country'] == '' ) {
         // if you're not sorting, or you don't wanna sort
         // or you're showing all countries
         $formatted = str_replace( '$$fan_country$$', $mem['country'],
            $formatted );
      } else {
         // hide it
         $formatted = str_replace( '$$fan_country$$', '', $formatted );
      }
   } // end setting of country

   // set additional fields
   foreach( explode( ',', $info['additional'] ) as $field ) {
      if( $field != '' ) {
         if( !in_array( $field, $sort ) ||
            ( isset( $show_sort_field ) && $show_sort_field ) ) {
            // you're not sorting by this, or you will show it anyway
            $formatted = @str_replace( '$$fan_' . $field . '$$',
               $mem[$field], $formatted );
         } else {
            // you're sorting by this field; show only if ALL or it's not set
            // show ONLY IF ALL
            if( $_GET[$field] == 'all' ||
               ( ( !isset( $_GET[$field] ) || $_GET[$field] == '' ) &&
                  isset( $no_sort ) ) ) {
               $formatted = @str_replace( '$$fan_' . $field . '$$',
                  $mem[$field], $formatted );
            } else {
               $formatted = @str_replace( '$$fan_' . $field . '$$', '',
                  $formatted );
            }
         }
      }
   }

   if( $mem['showemail'] == 0 ) {
      // member doesn't want to show email
      $email_actual = '<span style="text-decoration: ' .
         'line-through;" class="show_members_no_email">email</span>';
      $email_plain = '';
      $email_generic = '<span style="text-decoration: ' .
         'line-through;" class="show_members_no_email">email</span>';
   } else {
      // show email address on the list
      $cutup = explode( '@', $mem['email'] );
      $email_actual = '<script type="text/javascript">' . "\r\n<!--\r\n" .
         "jsemail = ( '$cutup[0]' + '@' + '$cutup[1]' ); \r\n" .
         "document.write( '<a href=\"mailto:' + jsemail + '\" class=\"" .
         "show_members_email\">' + jsemail " .
         "+ '</' + 'a>' );\r\n" . ' -->' . "\r\n" . '</script>';
      $email_plain = str_replace( '@', ' {at} ', $mem['email'] );
      $email_generic = '<script type="text/javascript">' . "\r\n" .
         "<!--\r\n" .
         "jsemail = ( '$cutup[0]' + '@' + '$cutup[1]' ); \r\n" .
         "document.write( '<a href=\"mailto:' + jsemail + '\" class=\"" .
            'show_members_email">email</\' + \'a>\' ' . ");\r\n" .
         " -->\r\n" .
         '</script>';
   }

   if( $mem['showurl'] == 0 || $mem['url'] == '' ) {
      // there is no url, or owner doesn't want this url shown
      $url_actual = '<span style="text-decoration: ' .
         'line-through;" class="show_members_no_website">website</span>';
      $url_plain = '';
      $url_generic = '<span style="text-decoration: ' .
         'line-through;" class="show_members_no_website">website</span>';
   } else {
      // show the url
      $target = ( $info['linktarget'] )
         ? 'target="' . $info['linktarget'] . '" '
         : '';
      $url_actual = '<a href="' . $mem['url'] . '" ' . $target .
         'class="show_members_website">' . $mem['url'] . '</a>';
      $url_plain = $mem['url'];
      $url_generic = '<a href="' . $mem['url'] . '" ' . $target .
         'class="show_members_website">website</a>';
   }   

   // continue :p
   $formatted = str_replace( '$$fan_email$$', $email_actual, $formatted );
   $formatted = str_replace( '$$fan_email_plain$$', $email_plain, $formatted );
   $formatted = str_replace( '$$fan_email_generic$$', $email_generic,
      $formatted );
   $formatted = str_replace( '$$fan_url$$', $url_actual, $formatted );
   $formatted = str_replace( '$$fan_url_plain$$', $url_plain, $formatted );
   $formatted = str_replace( '$$fan_url_generic$$', $url_generic,
      $formatted );

   // echo the formatted template
   echo $formatted;
}

// pagination schtuff now
$page_qty = $total / $info['perpage'];

// create the URL for pagination
$url = substr( strrchr( $_SERVER['PHP_SELF'], '/' ), 1 );
$connector = '?';
foreach( $_GET as $key => $value )
   if( $key != 'start' && $key != 'PHPSESSID' ) {
      $url .= $connector . clean( $key ) . '=' . clean( $value );
      $connector = '&amp;';
   }

// show actual pagination now
if( $page_qty > 1 ) {
   echo '<p class="show_members_pagination">Go to page: ';
   echo '<a href="' . $url . $connector . 'start=all">All</a> ';
   $i = 1;
   while( ( $i <= $page_qty + 1 ) && $page_qty > 1 ) {
      $start_link = ( $i - 1 ) * $info['perpage'];
      echo '<a href="' . $url . $connector . 'start=' . $start_link . '">' .
      $i . '</a> ';
      $i++;
   }
   echo '</p>';
}
?>