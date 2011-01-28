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
require_once( 'mod_affiliates.php' );
require_once( 'mod_owned.php' );
require_once( 'mod_settings.php' );

// make sure fanlisting is set to have affiliates
$info = get_listing_info( $listing );
if( $info['affiliates'] == 0 ) {
   echo '<p>The fanlisting has not been set up to have affiliates.</p>';
   return;
}

// get affiliates and listing info
$affiliates = get_affiliates( $listing );
foreach( $affiliates as $aff )
   echo parse_affiliates_template( $aff['affiliateid'], $listing );
?>