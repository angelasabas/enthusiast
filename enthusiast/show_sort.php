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

// get listing info and member type
$info = get_listing_info( $listing );
$member_type = ( $info['listingtype'] == 'fanlisting' ) ? 'fans' : 'members';

// explode the fields for sorting and make sure there are no whitespace
$sortarray = explode( ',', $info['sort'] );
foreach( $sortarray as $i => $s ) $sortarray[$i] = trim( $s );

// what sorting fields have been selected already by the visitor?
// obtain from $_GET array
$set = 0;
$selected = array(); // what have we selected?
$sortfield = $sortarray[$set]; // what field are we currently sorting?
while( isset( $sortarray[$set] ) && isset( $_GET[$sortarray[$set]] ) ) {
   // while the user has selected a value to sort with
   $selected[$sortarray[$set]] = $_GET[$sortarray[$set]];
   if( $selected[$sortarray[$set]] == 'all' )
      $selected[$sortarray[$set]] = '%';
   $set++;
   if( isset( $sortarray[$set] ) )
      $sortfield = $sortarray[$set];
}
//$set--;
$sorters = get_member_sorter( $listing, $set, $selected );

// are we still going to show the sorting?
if( count( $sortarray ) == $set ) // this is the last?
   return;

// create list URL
$list_url = $info['listpage'];
$connector = '?';
if( substr_count( $list_url, '?' ) > 0 )
   if( $info['dropdown'] == 1 )
      $connector = '&';
   else
      $connector = '&amp;';

// get anything that's already in $selected and put it in the URL
$in_url = array();
foreach( $selected as $field => $value ) {
   $field = clean( $field );
   $value = clean( $value );
   if( in_array( $field, $in_url ) )
      continue;
   $list_url .= "$connector$field=$value";
   if( $info['dropdown'] == 1 )
      $connector = '&';
   else
      $connector = '&amp;';
   $in_url[] = $field;
}

// if owner selected the dropdown method
if( $info['dropdown'] == 1 ) {
?>
   <script type="text/javascript">
   <!--
   function change( form ) {
      var dropDown = form.elements[0];
      var myIndex = dropDown.selectedIndex;
      if( dropDown.options[myIndex].value != "0" ) {
         window.open( "<?php echo $list_url . $connector . $sortfield ?>=" +
            dropDown.options[myIndex].value, target = "_self" );
      }
   }
   // end -->
   </script>

   <form method="get" action="<?php echo $list_url ?>" class="show_sort_form">
   <p>
   <select name="<?php echo $sortfield ?>" onchange="change( this.form );">
   <option value="0"> Select sort option</option>
   <option value="all"> All <?php echo $member_type ?></option>
<?php
   foreach( $sorters as $sort )
      if( $sort == '' )
         echo '<option value="none"> None given </option>';
      else
         echo '<option value="' . $sort . '"> ' . $sort . '</option>';
?>
   </select>
   </p>
   </form>
<?php
} else { // bulletted list
   echo '<ul class="show_sort_list">';
   // if there are already selected stuff, don't show this anymore!
   // unless owner chose to hide members until the final list
   if( count( $selected ) == 0 ||
      isset( $hide_members_until_final ) && $hide_members_until_final ) {
      echo '<li> <a href="' . $list_url . $connector . $sortfield .
         '=all">All ' . $member_type . '</a> </li>';
   }
   foreach( $sorters as $sortvalue )
      if( $sortvalue == '' )
         echo '<li> <a href="' . $list_url . $connector . $sortfield .
            '=none">None given</a> </li>';
      else
         echo '<li> <a href="' . $list_url . $connector . $sortfield .
            '=' . $sortvalue . '">' . $sortvalue . '</a> </li>';
   echo '</ul>';
}
?>