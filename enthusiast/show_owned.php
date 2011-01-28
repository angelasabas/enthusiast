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
require_once( 'mod_members.php' );
require_once( 'mod_owned.php' );
require_once( 'mod_settings.php' );

// function to show category list
if( !function_exists( 'show_owned_category_list' ) ) {
   function show_owned_category_list( $dropdown = false, $intro = true ) {
      global $cats, $page, $connector;
      if( $dropdown ) { // show dropdown
         if( $intro ) {
?>
            <p class="show_owned_intro_dropdown">
            Select the listing category you want to see below. The dropdown
            only shows categories with listings listed under them.
            </p>
<?php
         }
?>
         <script type="text/javascript">
         <!-- Begin
         function change(form) {
            var myindex = form.cat.selectedIndex
            if (form.cat.options[myindex].value != "0") {
               window.open( "<?php echo $page . $connector
               ?>cat=" + form.cat.options[myindex].value, target="_self");
            }
         }
         // end -->
         </script>

         <form method="get" action="<?php echo $page ?>" class="show_owned_select_form">
         <p>
<?php
         // show other possible $_GET values
         if( isset( $_GET ) )
            foreach( $_GET as $get => $value )
               if( $get != 'cat' )
                  echo '<input type="hidden" name="' . clean( $get ) .
                     '" value="' . clean( $value ) . '" />' . "\r\n";
?>
         <select name="cat" onchange="change(this.form)"
            class="show_owned_select_form">
         <option value="0"> Select sort option</option>
<?php
         if( $intro )
            echo '<option value="all"> All listings</option>';

         foreach( $cats as $cat )
            echo '<option value="' . $cat['catid'] . '">' . $cat['text']  .
               '</option>';
?>
         </select>
         </p>
         </form>
<?php
      } else {
         // show bulletted list instead
         if( $intro ) {
?>
            <p class="show_owned_intro_list">Select the listing category you
            want to see below. The list only shows categories with listings
            listed under them.</p>
<?php
         }

         echo '<ul class="show_owned_list_items">';

         if( $intro )
            echo '<li> <a href="' . $page . $connector . 'cat=all">All ' .
               'categories</a> </li>';

         foreach( $cats as $cat )
            echo '<li> <a href="' . $page . $connector . 'cat=' . $cat['catid'] .
               '">' . $cat['text'] . '</a> </li>';
         echo '</ul>';
      }
   } // end function
}

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


// get all categories where there are fanlistings owned under it
$ownedcats = get_owned_cats( $status );
$cats = array();
$skipids = array();
foreach( $ownedcats as $cat ) { // also add parents now
   // get ancestors
   $ancestors = array_reverse( get_ancestors( $cat ) );
   foreach( $ancestors as $a ) {
      if( !in_array( $a, $skipids ) ) {
         $cats[] = array( 'catid' => $a, 'text' => get_category_name( $a ),
	         'parent' => get_category_parent( $a ),
            'qty' => count( get_owned_by_category( $a ) ) );
         $skipids[] = $a;
      }
   }
}

// fix the cats array: remove children OR fix text for ancestors
foreach( $cats as $index => $cat ) {
   if( !isset( $show_subcats_in_main_list ) || !$show_subcats_in_main_list ) { 
      if( $cat['parent'] != '0' && $cat['parent'] != '' ) {
         unset( $cats[$index] );
      }
   } else { 
      if( $cat['parent'] != '0' && $cat['parent'] != '' ) {
         if( $cat['qty'] == 0 ) {
            unset( $cats[$index] );
            continue;
         }
         // get ancestors
         $ancestors = array_reverse( get_ancestors( $cat['catid'] ) );
         $text = '';
         foreach( $ancestors as $a )
            $text .= get_category_name( $a ) . ' > ';
         $text = rtrim( $text, ' > ' );
         $text = str_replace( '>', '&raquo;', $text );
         $cats[$index]['text'] = $text;
      }
   }
}
usort( $cats, 'category_array_compare' );

// set up page url
$pageinfo = pathinfo( $_SERVER['PHP_SELF'] );
$page = $pageinfo['basename'];
$connector = '?';
if( isset( $_GET ) ) {
   foreach( $_GET as $get => $value ) {
      if( $get != 'cat' ) {
         $page .= $connector . clean( $get ) . '=' . clean( $value );
         $connector = '&amp;';
      }
   }
}

if( ( !isset( $hide_dropdown ) || $hide_dropdown == false ) &&
   ( !isset( $show_list ) || $show_list == false ) ) {
   show_owned_category_list( true );
} else if( ( !isset( $hide_dropdown ) || $hide_dropdown == false ) &&
   ( isset( $show_list ) && $show_list ) &&
   ( !isset( $_GET['cat'] ) || $_GET['cat'] == '' ) &&
   ( !isset( $show ) || $show == '' ) ) {
   show_owned_category_list();
}

if( !isset( $status ) ) {
	echo '<p style="color: red;">ERROR: <code>$status</code> is not set.</p>';
   return;
}

if( ( isset( $_GET['cat'] ) && $_GET['cat'] != '' ) ||
   ( isset( $show ) && $show != '' ) ) {

   // get fanlistings under that category, and depending on $status
   $ids = array();
   if( ( isset( $_GET['cat'] ) && $_GET['cat'] == 'all' ) ||
      ( isset( $show ) && $show == 'all' ) ) {
      echo '<p class="show_owned_where_you_are">Showing all ' . $status .
         ' fanlistings...</p>';
      $ids = get_owned( $status );

   } else if( ( isset( $_GET['cat'] ) && $_GET['cat'] != 'all' ) ||
      ( isset( $show ) && $show != 'all' ) ) {
      $catid = 0;
      if( isset( $_GET['cat'] ) && ctype_digit( $_GET['cat'] ) )
         $catid = clean( $_GET['cat'] );
      else if( isset( $show ) )
         $catid = $show;

      if( $ancestors = array_reverse( get_ancestors( $catid ) ) ) {
         // get ancestors
         $text = '';
         foreach( $ancestors as $a )
            $text .= get_category_name( $a ) . ' > ';
      } else $text = get_category_name( $catid );
      echo '<p class="show_owned_where_you_are">Showing fanlistings ' .
         'under the <i>' . str_replace( '>', '&raquo;', rtrim( $text, ' > ' ) ) .
         '</i> category...</p>';

      if( $status == 'pending' )
         $ids = get_owned_by_category( $catid, '0' );
      else if( $status == 'upcoming' )
         $ids = get_owned_by_category( $catid, 1 );
      else
         $ids = get_owned_by_category( $catid, 2 );
   }

   if( ( !isset( $show_subcats_in_main_list ) || !$show_subcats_in_main_list )
      && isset( $_GET['cat'] ) ) {
      // we then have to show the children of this category, if there are
      $children = get_enth_category_children( clean( $_GET['cat'] ) );
      $cats = array();
      $status_number = ( $status == 'pending' ) ? '0' : ( ( $status == 'upcoming' ) ? 1 : 2 );
      foreach( $children as $cat )
         $cats[] = array( 'catid' => $cat['catid'],
            'text' => $cat['catname'],
	         'parent' => get_category_parent( $cat['catid'] ),
            'qty' => count( get_owned_by_category( $cat['catid'], $status_number ) ) );
      // check for empty categories!
      foreach( $cats as $index => $cat ) {
         $children = get_enth_category_children( $cat['catid'] );
         $childqty = 0;
         foreach( $children as $c )
            $childqty += count( get_owned_by_category( $c['catid'], $status_number ) );
         if( $cat['qty'] == 0 && $childqty == 0 )
            unset( $cats[$index] );
      }
      if( count( $cats ) && ( !isset( $show_list ) || !$show_list ) ) { // use dropdown
         show_owned_category_list( true, false );
      } else if( count( $cats ) && isset( $show_list ) && $show_list ) {
         show_owned_category_list( false, false );
      }
   }

   echo get_setting( 'owned_template_header' );
   foreach( $ids as $id )
      echo parse_owned_template( $id );
   echo get_setting( 'owned_template_footer' );
}

// show notification of having no listings to show if applicable
if( isset( $ids ) && count( $ids ) == 0 )
   echo '<p class="show_owned_no_listings_here">There are no listings ' .
      'that fall under this criteria.</p>';

// show way to go back if using the list
if( ( !isset( $hide_dropdown ) || $hide_dropdown == false ) &&
   ( isset( $show_list ) && $show_list ) &&
   ( isset( $_GET['cat'] ) && $_GET['cat'] != '' ) ||
   ( isset( $show ) && $show != '' ) &&
   ( !isset( $ids ) || count( $ids ) != 0 ) &&
   $show != 'all' )
   echo '<p class="show_owned_go_back"><a href="javascript:history.back()">Go ' .
      'back?</a></p>';

// show link back to Indiscripts
?>
<p class="show_owned_credits">
<a href="http://scripts.indisguise.org">Powered by Enthusiast
<?php include ENTH_PATH . 'show_enthversion.php' ?></a>
</p>