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
require_once( 'config.php' );

require_once( 'mod_errorlogs.php' );
require_once( 'mod_categories.php' );
require_once( 'mod_joined.php' );
require_once( 'mod_settings.php' );

// function to show category list
function show_joined_category_list( $dropdown = false, $intro = true ) {
   global $cats, $page, $connector;
   if( $dropdown ) { // show dropdown
      if( $intro ) {
?>
         <p class="show_joined_intro_dropdown">
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

      <form method="get" action="<?php echo $page ?>" class="show_joined_select_form">
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
         class="show_joined_select_form">
      <option value="0"> Select sort option</option>
<?php
      if( $intro )
         echo '<option value="all"> All listings (' .
            count( get_joined( 'approved' ) ) . ')</option>';

      // are there pending listings?
      if( $intro && count( get_joined( 'pending' ) ) > 0 )
         echo '<option value="pending"> All pending approval</option>';

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
         <p class="show_joined_intro_list">Select the listing category you
         want to see below. The list only shows categories with listings
         listed under them.</p>
<?php
      }

      echo '<ul class="show_joined_list_items">';

      if( $intro )
         echo '<li> <a href="' . $page . $connector . 'cat=all">All ' .
            'listings (' . count( get_joined( 'approved' ) ) . ')</a> </li>';

      // are there pending listings?
      if( $intro && count( get_joined( 'pending' ) ) > 0 )
         echo '<li> <a href="' . $page . $connector .
            'cat=pending">All Pending approval</a> </li>';

      foreach( $cats as $cat )
         echo '<li> <a href="' . $page . $connector . 'cat=' . $cat['catid'] .
            '">' . $cat['text'] . '</a> </li>';
      echo '</ul>';
   }
} // end function

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



// get all categories where there are listings joined under it
$joinedcats = get_joined_cats();
$cats = array();
$skipids = array();
foreach( $joinedcats as $cat ) { // also add parents now
   // get ancestors
   $ancestors = array_reverse( get_ancestors( $cat ) );
   foreach( $ancestors as $a ) {
      if( !in_array( $a, $skipids ) ) {
         $cats[] = array( 'catid' => $a, 'text' => get_category_name( $a ),
	         'parent' => get_category_parent( $a ),
            'qty' => count( get_joined_by_category( $a ) ) );
         $skipids[] = $a;
      }
   }
}

// fix the cats array: remove children OR fix text for ancestors
foreach( $cats as $index => $cat ) {
   // if user wants to keep subcats on the root list
   if( !isset( $show_subcats_in_main_list ) || !$show_subcats_in_main_list ) {
      if( $cat['parent'] != '0' && $cat['parent'] != '' ) {
         // has parent, remove it from this list of root cats
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

// start showing the list of categories
if( ( !isset( $show_list ) || !$show_list ) ) { // use dropdown
   show_joined_category_list( true );
} else if( ( isset( $show_list ) && $show_list ) &&
   ( !isset( $_GET['cat'] ) || $_GET['cat'] == '' ) ) {
   show_joined_category_list();
}

// show listings
if( isset( $_GET['cat'] ) && $_GET['cat'] != '' ) {

   // "where you are" text
   if( $_GET['cat'] == 'all' || $_GET['cat'] == 'pending' )
      echo '<p class="show_joined_where_you_are">Showing ' .
         clean( $_GET['cat'] ) . ' listings...</p>';
   else {
      $getcat = clean( $_GET['cat'] );
      if( $ancestors = array_reverse( get_ancestors( $getcat ) ) ) {
         // get ancestors
         $text = '';
         foreach( $ancestors as $a )
            $text .= get_category_name( $a ) . ' > ';
      } else
         $text = get_category_name( $getcat );
      echo '<p class="show_joined_where_you_are">Showing listings ' .
         'under the <i>' . str_replace( '>', '&raquo;',
         rtrim( $text, ' > ' ) ) . '</i> category...</p>';
   }

   if( !isset( $show_subcats_in_main_list ) || !$show_subcats_in_main_list ) {
      // we then have to show the children of this category, if there are
      $children = get_enth_category_children( clean( $_GET['cat'] ) );
      $cats = array();
      foreach( $children as $cat )
         $cats[] = array( 'catid' => $cat['catid'],
            'text' => $cat['catname'],
	         'parent' => get_category_parent( $cat['catid'] ),
            'qty' => count( get_joined_by_category( $cat['catid'] ) ) );
      // check for empty categories!
      foreach( $cats as $index => $cat )
         if( $cat['qty'] == 0 )
            unset( $cats[$index] );
      // if there are subcats, then show these:
      if( count( $cats ) ) {
         if( !isset( $show_list ) || !$show_list ) { // use dropdown
            show_joined_category_list( true, false );
         } else if( isset( $show_list ) && $show_list ) {
            show_joined_category_list( false, false );
         }
      }
   }

   $ids = array();
   if( $_GET['cat'] == 'all' )
      $ids = get_joined();
   else if( $_GET['cat'] == 'pending' )
      $ids = get_joined( 'pending' );
   else
      $ids = get_joined_by_category( clean( $_GET['cat'] ) );

   echo get_setting( 'joined_template_header' );
   foreach( $ids as $id )
      echo parse_joined_template( $id );
   echo get_setting( 'joined_template_footer' );
   }

// show notification of having no listings to show if applicable
if( isset( $ids ) && count( $ids ) == 0 )
   echo '<p class="show_joined_no_listings_here">There are no listings ' .
      'that fall under this criteria.</p>';

// show way to go back if using the list
if( ( isset( $show_list ) && $show_list ) &&
   ( isset( $_GET['cat'] ) && $_GET['cat'] != '' ) &&
   ( !isset( $ids ) || count( $ids ) != 0 ) )
   echo '<p class="show_joined_go_back">' .
      '<a href="javascript:history.back()">Go back?</a></p>';

// show link back to Indiscripts
?>
<p class="show_joined_credits">
<a href="http://scripts.indisguise.org">Powered by Enthusiast
<?php include ENTH_PATH . 'show_enthversion.php' ?></a>
</p>