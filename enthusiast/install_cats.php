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
require_once( 'header.php' );
require_once( 'config.php' );
?>
<h1>Enthusiast 3 Category Installation</h1>
<?php

if( isset( $_POST['install'] ) && $_POST['install'] == 'yes' ) {

   // try to connect
   $db_link = mysql_connect( $db_server, $db_user, $db_password )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );
   mysql_select_db( $db_database )
      or die( DATABASE_CONNECT_ERROR . mysql_error() );

   // create cats array
   $cats = array();
   $cats[] = 'Academia';
   $cats[] = 'Actors';
   $cats[] = 'Actresses';
   $cats[] = 'Adult';
   $cats[] = 'Advertising/TV Channels';
   $cats[] = 'Albums';
   $cats[] = 'Animals';
   $cats[] = 'Animation';
   if( isset( $_POST['tfltafl'] ) ) {
      $cats[] = 'Anime/Manga: Adult';
      $cats[] = 'Anime/Manga: Characters 0-M';
      $cats[] = 'Anime/Manga: Characters N-Z';
      $cats[] = 'Anime/Manga: Companies';
      $cats[] = 'Anime/Manga: Episodes';
      $cats[] = 'Anime/Manga: Fanstuff';
      $cats[] = 'Anime/Manga: General';
      $cats[] = 'Anime/Manga: Items/Locations';
      $cats[] = 'Anime/Manga: Magazines';
      $cats[] = 'Anime/Manga: Manga-ka/Directors';
      $cats[] = 'Anime/Manga: Movies/OVAs';
      $cats[] = 'Anime/Manga: Music';
      $cats[] = 'Anime/Manga: Relationships';
      $cats[] = 'Anime/Manga: Rivalries';
      $cats[] = 'Anime/Manga: Series';
      $cats[] = 'Anime/Manga: Songs';
      $cats[] = 'Anime/Manga: Toys/Collectibles';
      $cats[] = 'Anime/Manga: Websites';
   }
   $cats[] = 'Arts and Design';
   $cats[] = 'Authors/Writers';
   $cats[] = 'Characters: Book/Movie';
   $cats[] = 'Characters: TV';
   $cats[] = 'Comics';
   $cats[] = 'Computer Miscellany and Internet';
   $cats[] = 'Directors/Producers';
   $cats[] = 'Episodes';
   $cats[] = 'Fan Works';
   $cats[] = 'Fashion/Beauty';
   $cats[] = 'Food/Drinks';
   $cats[] = 'Games';
   $cats[] = 'History/Royalty';
   $cats[] = 'Hobbies and Recreation';
   $cats[] = 'Literature';
   $cats[] = 'Magazines/Newspapers';
   $cats[] = 'Miscellaneous';
   $cats[] = 'Models';
   $cats[] = 'Movies';
   $cats[] = 'Music Miscellany';
   $cats[] = 'Musicians: Bands/Groups';
   $cats[] = 'Musicians: Female';
   $cats[] = 'Musicians: Male';
   $cats[] = 'Mythology/Religion';
   $cats[] = 'Nature';
   $cats[] = 'Objects';
   $cats[] = 'People Miscellany';
   $cats[] = 'Places';
   $cats[] = 'Politics and Organisations';
   $cats[] = 'Radio';
   $cats[] = 'Relationships: Book/Movie';
   $cats[] = 'Relationships: Real Life';
   $cats[] = 'Relationships: TV';
   $cats[] = 'Songs: Bands/Groups 0-M';
   $cats[] = 'Songs: Bands/Groups N-Z';
   $cats[] = 'Songs: Female Solo';
   $cats[] = 'Songs: Male Solo';
   $cats[] = 'Songs: Various';
   $cats[] = 'Sports';
   $cats[] = 'Sports Entertainment';
   $cats[] = 'Stage/Theatre';
   $cats[] = 'Toys/Collectibles';
   $cats[] = 'Transportation';
   $cats[] = 'TV/Stage Personalities';
   $cats[] = 'TV Shows';
   $cats[] = 'TV/Movie/Book Miscellany';
   $cats[] = 'Webmasters';
   $cats[] = 'Websites';

   $installed = 0;
   foreach( $cats as $cat ) {
      // create query
      $query = "INSERT INTO `$db_category` VALUES (null, '$cat', '0')";
      $result = mysql_query( $query );
      if( $result )
         $installed++;
   }
?>
   <p><?php echo $installed ?> categories installed successfully.</p>
   <p><a href="index.php">You can now log into Enthusiast 3.</a></p>
<?php
} else {
?>
   <p>
   This page will install the current categories of
   <a href="http://thefanlistings.org">The Fanlistings Network</a> (and
   optionally the <a href="http://animefanlistings.org">The Anime Fanlistings
   Network</a> categories as well) to your Enthusiast 3 installation. <b>If
   you have not yet successfully run the <code>install.php</code> file, <a
   href="install.php">please do that first</a></b>!
   </p>

   <form action="install_cats.php" method="post">
   <input type="hidden" name="install" value="yes" />

   <p class="center"><table style="text-align: center; width: 100%;">

   <tr><td colspan="2">
   <input type="submit" name="tfl" value="Install TFL categories only" />
   <input type="submit" name="tfltafl" value="Install TFL + TAFL categories" />
   </td></tr>

   </table></p>
<?php
}

require_once( 'footer.php' );
?>