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
session_start();
require_once( 'logincheck.inc.php' );
if( !isset( $logged_in ) || !$logged_in ) {
   $_SESSION['message'] = 'You are not logged in. Please log in to continue.';
   $next = '';
   if( isset( $_SERVER['REQUEST_URI'] ) )
      $next = $_SERVER['REQUEST_URI'];
   else if( isset( $_SERVER['PATH_INFO'] ) )
      $next = $_SERVER['PATH_INFO'];
   $_SESSION['next'] = $next;
   header( 'location: index.php' );
   die( 'Redirecting you...' );
}
require( 'config.php' );
require_once( 'header.php' );
require_once( 'mod_errorlogs.php' );
require_once( 'mod_categories.php' );
require_once( 'mod_owned.php' );
require_once( 'mod_settings.php' );
require_once( 'mod_members.php' );
require_once( 'mod_emails.php' );

$show_default = true;
echo '<h1>Manage Members</h1>';
$action = ( isset( $_REQUEST["action"] ) ) ? $_REQUEST['action'] : '';
$listing = ( isset( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : '';

/*_____________________________________________________________DELETE/REJECT_*/
if( $action == 'reject' || $action == 'delete' ) {
   $info = get_listing_info( $listing );
   $success = delete_member( $listing, $_REQUEST['email'] );
   if( $success ) {
      echo '<p class="success">Successfully deleted the member with email ' .
         'address <i>' . $_REQUEST['email'] . '</i> from the <i>' .
         $info['subject'] . ' ' . $info['listingtype'] . '</i>.</p>';
   }
   if( $action == 'reject' )
      $listing = ''; // still approving/rejecting members

   // free up memory
   unset( $info, $success );
}


/*___________________________________________________________________APPROVE_*/
if( $action == 'approve' ) {
   $info = get_listing_info( $listing );
   $success = approve_member( $listing, $_REQUEST['email'] );
   if( $success ) {
      echo '<p class="success">Successfully approved the member with ' .
         'email address <i>' . $_REQUEST['email'] . '</i> from the <i>' .
         $info['subject'] . ' ' . $info['listingtype'] . '</i>.</p>';
      // send approval email?
      if( $info['emailapproved'] ) {
         $to = $_REQUEST['email'];
         $body = parse_email( 'approved', $listing, $to );
         $subject = $info['title'] . ': Added';
         $from = '"' . html_entity_decode( $info['title'], ENT_QUOTES ) .
            '" <' . $info['email'] . '>';

         // use send_email function
         $mail_sent = send_email( $to, $from, $subject, $body );
         if( !$mail_sent ) {
            echo '<p class="error">Approval email sending failed.</p>';
         }
      }
   } else {
      echo '<p class="error">Error approving the member. Try again.</p>';
   }
   $listing = '';

   // free up memory
   unset( $info, $success, $to, $body, $subject, $headers );
}


/*______________________________________________________________MULTIPLE_*/
if( $action == 'multiple' ) {
   $info = get_listing_info( $listing );
   $subject = $info['title'] . ': Added';
   $from = '"' . html_entity_decode( $info['title'], ENT_QUOTES ) .
      '" <' . $info['email'] . '>';
   if( !isset( $_POST['email'] ) || count( $_POST['email'] ) == 0 ) {
      echo '<p class="error">No pending members were checked and no ' .
         'members have been approved or rejected.</p>';
   } else {
      // check which it is
      $selected = $_POST['selected'];
      if( $selected == 'APPROVE' ) {
         require_once( 'Mail.php' );
         foreach( $_POST['email'] as $email ) {
            $success = approve_member( $listing, $email );
            if( !$success )
               echo '<p class="error">Error approving member with ' .
                  'email address <i>' . $_REQUEST['email'] . '</i>.</p>';
            else {
               if( $info['emailapproved'] ) { // send if there is
                  $body = parse_email( 'approved', $listing, $email );
   
                  // use send_email function
                  $mail_sent = send_email( $email, $from, $subject, $body );
               }
            }
         }
         echo '<p class="success">Finished approving selected members.</p>';
      } else if( $selected == 'REJECT' ) {
         foreach( $_POST['email'] as $email ) {
            $success = delete_member( $listing, $email );
            if( !$success )
               echo '<p class="error">Error rejecting member with ' .
                  'email address <i>' . $_REQUEST['email'] . '</i>.</p>';
         }
         echo '<p class="success">Finished rejecting selected members.</p>';
      }
   }
   $listing = '';

   // free up memory
   unset( $info, $subject, $headers, $success, $body );
}

/*______________________________________________________________________EDIT_*/
if( $action == 'edit' ) {
   $info = get_listing_info( $listing );
   $member = get_member_info( $listing, $_REQUEST['email'] );
   $show_default = false;
   $show_edit_form = true;

   if( isset( $_POST['done'] ) ) {
      $success = edit_member_info( $listing, $_REQUEST['email'], $_POST );
      if( $success ) {
         echo '<p class="success">Successfully edited the information of ' .
            'the member with email address <i>' . $_REQUEST['email'] .
            '</i> in the <i>' . $info['subject'] . ' ' . $info['listingtype'] .
            '</i>.</p>';
         $show_edit_form = false;
         $show_default = true;
         // if index "approved" is present, the page is from the pending mem
         // unset $listing
         if( isset( $_REQUEST['approved'] ) )
            $listing = '';
      }
   }

   if( $show_edit_form ) {
      $shade = false;
?>
      <p>You can edit the member's information using the form below, where the
      current information is shown. Once you've finished editing the
      fields, click on "Edit member info".</p>

      <form method="post" action="members.php">
      <input type="hidden" name="action" value="edit" />
      <input type="hidden" name="id" value="<?php echo $info['listingid'] ?>" />
      <input type="hidden" name="email" value="<?php echo $member['email'] ?>" />
      <input type="hidden" name="done" value="yes" />

      <table>

      <tr><th colspan="2">
      <?php echo $info['subject'] ?> <?php echo ucwords( $info['listingtype'] ) ?> Member
      </th></tr>

      <tr><td>
      Name
      </td><td>
      <input type="text" name="name" value="<?php echo htmlentities( $member['name'] ) ?>" />
      </td></tr>

      <tr class="rowshade"><td>
      Email
      </td><td>
      <input type="text" name="email_new" value="<?php echo $member['email'] ?>" />
      </td></tr>
<?php
      if( $info['country'] == 1 ) {
         $shade = true;
?>
         <tr><td>
         Country
         </td><td>
         <select name="country">
         <option value="<?php echo $member['country'] ?>">Current (<?php echo $member['country'] ?>)</option>
         <option value="<?php echo $member['country'] ?>">---</option>
<?php
         include 'countries.inc.php';
?>
         </select>
         </td></tr>
<?php
      }
?>
      <tr <?php echo ( $shade ) ? 'class="rowshade"' : '' ?>><td>
      URL
      </td><td>
      <input type="text" name="url" value="<?php echo $member['url'] ?>" />
      <?php echo ( $member['url'] ) ? '<a href="' . $member['url'] . '"' .
         ' target="' . $info['linktarget'] . '">(visit)</a>' : '' ?>
      </td></tr>
<?php
      // toggle $shade after URL field
      if( $shade ) $shade = false;
      else $shade = true;

      if( $info['additional'] != '' ) {
         foreach( explode( ',', $info['additional'] ) as $field ) {
            if( !$field ) continue;
?>
            <tr <?php echo ( $shade ) ? 'class="rowshade"' : '' ?>><td>
            <?php echo ucwords( str_replace( '_', ' ', $field ) ) ?>
            </td><td>
            <input type="text" name="<?php echo $field ?>" value="<?php echo htmlentities( $member[$field] ) ?>" />
            </td></tr>
<?php
            if( $shade ) $shade = false;
            else $shade = true;
         } // end foreach
      } // end if additional
?>
      <tr <?php echo ( $shade ) ? 'class="rowshade"' : '' ?>><td>
      Show/Hide Website
      </td><td style="text-align: left;">
<?php
      if( $member['showurl'] == 1 ) {
?>
         <input type="radio" name="showurl" value="leave" checked="checked" />
         Leave as is (Show)<br />
         <input type="radio" name="showurl" value="hide" /> Hide<br />
<?php
      } else if( $member['showurl'] == 0 ) {
?>
         <input type="radio" name="showurl" value="leave" checked="checked" />
         Leave as is (Hide)<br />
         <input type="radio" name="showurl" value="show" /> Show<br />
<?php
      }
?>
      </td></tr>
<?php
      // toggle $shade after show/hide
      if( $shade ) $shade = false;
      else $shade = true;
?>
      <tr<?php echo ( $shade ) ? 'class="rowshade"' : '' ?>>
      <td colspan="2" class="right">
<?php
      if( $member['pending'] == 1 ) {
?>
         Approve already?
         <input type="checkbox" name="approved" value="1" />
<?php
      }
?>
      <input type="submit" value="Edit member info" />
      <input type="reset" value="Reset form values" />
      <input type="button" value="Cancel"
         onclick="javascript:window.location='members.php?id=<?php echo $listing
         ?>';" />
      </td></tr>

      </table></form>
<?php
   }

   // free up memory
   unset( $info, $member, $show_edit_form, $success );

}



if( $show_default ) {
?>
   <div class="submenu">
   <?php echo ( $listing )
      ? '<a href="emails.php?action=members&id=' . $listing . '">Email</a>'
      : '' ?>
   </div>

   <form action="members.php" method="post">

   <p class="right"> Manage:
   <select name="id">
   <option value="">All pending members</option>
<?php
   $owned = get_owned( 'current' ); // all current owned
   foreach( $owned as $id ) {
      $info = get_listing_info( $id );
      echo '<option value="' . $id;
      if( $id == $listing )
         echo '" selected="selected';
      echo '">' . $info['subject'] . ' ' . $info['listingtype'] . ' </option>';
   }
?>
   </select>
   <input type="submit" value="Manage" />
   </p>

   </form>

   <p>Via this section, you may manage the members of each listing you run.
   Select which members you would like to manage from the dropdown above.
   </p>
<?php
   if( $listing ) { //////////////////////////////////////////////// MANAGE
      $info = get_listing_info( $listing );
?>
      <form action="members.php" method="get">
      <input type="hidden" name="dosearch" value="now" />
      <input type="hidden" name="id" value="<?php echo $listing ?>" />

      <p class="center">
      <input type="text" name="search" />
      <input type="submit" value="Search" />
      </p>

      </form>
<?php
      $start = ( isset( $_REQUEST['start'] ) ) ? $_REQUEST['start'] : '0';

      $total = 0;
      $members = array();
      if( isset( $_GET['dosearch'] ) ) {
         $members = search_members( $_GET['search'], $listing,
            'approved', $start );
         $total = count( search_members( $_GET['search'], $listing,
            'approved' ) );
      } else {
         $members = get_members( $listing, 'approved', array(), $start,
            'bydate' );
         $total = count( get_members( $listing ) );
      }
?>
      <table>

      <tr><th>Action</th>
      <th>Email</th>
      <?php echo ( $info['country'] ) ? '<th>Country</th>' : '' ?>
      <th>Name</th>
      <th>URL</th>
      <?php echo ( $info['additional'] != '' ) ? '<th>Additional</th>' : '' ?>
      </tr>
<?php
      $shade = false;
      foreach( $members as $member ) {
         $class = ( $shade ) ? ' class="rowshade"' : '';
         $shade = !$shade;
?>
         <tr<?php echo $class ?>><td>
         <a href="members.php?action=edit&id=<?php echo $listing
            ?>&email=<?php echo $member['email'] ?>"><img src="edit.gif" width="42"
            height="19" border="0" alt=" edit" /></a>
         <a href="emails.php?action=directemail&address=<?php echo $member['email']
            ?>&listing=<?php echo $listing ?>"><img src="email.gif"
            width="42" height="19" border="0" alt=" email" /></a>
         <a href="members.php?action=delete&id=<?php echo $listing
            ?>&email=<?php echo $member['email'] ?>" onclick="
            go = confirm('Are you sure you want to delete <?php echo addslashes( $member['name'] ) ?>?'); return go;"><img
            src="delete.gif" width="42" height="19" border="0" alt=" delete"
            /></a>
         </td><td>
         <?php echo $member['email'] ?>
         </td><td>
         <?php echo ( $info['country'] ) ? $member['country'] . '</td><td>' : ''; ?>
         <?php echo $member['name'] ?>
         </td><td>
         <a href="<?php echo $member['url'] ?>" target="<?php echo $info['linktarget'] 
            ?>"><?php echo $member['url']?></a>
         </td>
<?php
         if( $info['additional'] != '' ) {
            echo '<td>';
            foreach( explode( ',', $info['additional'] ) as $field ) {
               if( $member[$field] == '' ) continue;
               if( $field != '' )
                  echo '<b>' . ucwords( str_replace( '_', ' ', $field ) ) .
                     '</b>: ' . $member[$field] . '<br />';
            }
            echo '</td>';
         }
?>
         </tr>
<?php
      }
?>
      </table>
<?php
      $page_qty = $total / get_setting( 'per_page' );
      $url = $_SERVER['REQUEST_URI'];

      $url = 'members.php';
      $connector = '?';
      $req = array_merge( $_GET, $_POST );
      foreach( $req as $key => $value )
         if( $key != 'start' && $key != 'PHPSESSID' ) {
            $url .= $connector . $key . '=' . $value;
            $connector = '&amp;';
         }

      if( $page_qty > 1 )
         echo '<p class="center">Go to page: ';

      $i = 1;
      while( ( $i <= $page_qty + 1 ) && $page_qty > 1 ) {
         $start_link = ( $i - 1 ) * get_setting( 'per_page' );
         echo '<a href="' . $url . $connector . 'start=' . $start_link . '">' .
         $i . '</a> ';
         $i++;
      }

      if( $page_qty > 1 )
         echo '</p>';

   } else { /////////////////////////////////////////////////////// PENDING
      $finalcount = 0;
      foreach( $owned as $id ) {
         $info = get_listing_info( $id );
         $qtycol = 6;
         if( $info['country'] == 0 )
            $qtycol--;
         if( $info['additional'] != '' )
            $qtycol++;
         $pending = get_members( $id, 'pending' );
         if( count( $pending ) > 0 ) {
            $finalcount += count( $pending );
            $approved = count( get_members( $id, 'approved' ) );
?>
            <form action="members.php" method="post" name="listing<?php echo $id ?>">
            <input type="hidden" name="id" value="<?php echo $id ?>" />
            <input type="hidden" name="action" value="multiple" />

            <table style="width: 100%;">

            <tr><th colspan="<?php echo $qtycol ?>">
            <b><?php echo ucwords( $info['subject'] ) ?>
            <?php echo ucwords( $info['listingtype'] ) ?></b>
            <small><a href="<?php echo $info['url'] ?>">(view site)</a></small> -
            <?php echo count( $pending ) ?> pending,
            <?php echo $approved ?> approved
            </th></tr>

            <tr class="subheader"><td>
            <input type="checkbox" onclick="
               checkAll( document.listing<?php echo $id ?>, this, 'email[]' );" />
            </td>
            <td>Action</td>
            <td>Email</td>
            <?php echo ( $info['country'] ) ? '<td>Country</td>' : '' ?>
            <td>Name</td>
            <td>URL</td>
            <?php echo ( $info['additional'] != '' ) ? '<td>Additional</td>' : '' ?>
            </tr>
<?php
            $shade = false;
            foreach( $pending as $member ) {
               $class = '';
               if( $shade ) {
                  $class = ' class="rowshade"';
                  $shade = false;
               } else $shade = true;

               $update = '';
               if( $member['added'] != '' )
                  $update = '<b class="important">*</b>';
?>
               <tr<?php echo $class ?>><td class="center">
               <input type="checkbox" name="email[]" value="<?php echo $member['email'] ?>" />
               </td><td>
               <a href="members.php?action=approve&id=<?php echo $id ?>&email=<?php echo $member['email'] ?>"><img src="approve.gif" width="42"
                  height="19" border="0" alt=" approve" title=" approve" /></a>
               <a href="members.php?action=edit&id=<?php echo $id ?>&email=<?php echo $member['email'] ?>"><img src="edit.gif" width="42"
                  height="19" border="0" alt=" edit" title=" edit" /></a>
               <a href="emails.php?action=directemail&address=<?php echo $member['email'] ?>&listing=<?php echo $id ?>"><img
                  src="email.gif" width="42" height="19" border="0"
                  alt=" email" title=" email" /></a>
               <a href="members.php?action=reject&id=<?php echo $id ?>&email=<?php echo $member['email'] ?>" onclick="
                  go = confirm('Are you sure you want to reject <?php echo addslashes( $member['name'] ) ?>?'); return go;"><img
                  src="reject.gif" width="42" height="19" border="0"
                  alt=" reject" title=" reject" /></a>
               </td><td>
               <?php echo $update ?><?php echo $member['email'] ?>
               </td><td>
               <?php echo ( $info['country'] == 1 )
                  ? $member['country'] . '</td><td>' : '' ?>
               <?php echo $member['name'] ?>
               </td><td>
               <?php echo ( $member['url'] )
                  ? '<a href="' . $member['url'] . '" target="' .
                     $info['linktarget'] . '">' . $member['url'] . '</a>'
                  : '' ?>
               </td>
<?php
               if( $info['additional'] != '' ) {
                  echo '<td>';
                  foreach( explode( ',', $info['additional'] ) as $field ) {
                     if( !$member[$field] ) continue;
                     echo '<b>' . ucwords( str_replace( '_', ' ', $field ) ) .
                        '</b>: ' . $member[$field] . '<br />';
                  }
                  echo '</td>';
               }
?>
               </tr>
<?php
            }
?>
            <tr<?php echo ( $shade ) ? ' class="rowshade"' : '' ?>>
            <td colspan="<?php echo $qtycol ?>" class="right">
               Mass approval:
               <input type="submit" name="selected" value="APPROVE" style="font-weight: bold;" />
               <input type="submit" name="selected" value="REJECT" onclick="
                  go=confirm('Are you sure you want to reject all the checked members?');return go;" />
            </td></tr>
            </table>

            </form>
<?php
         }
      }

      if( $finalcount == 0 ) {
         echo '<p class="success">There are no pending members!</p>';
      }
   }
   unset( $owned, $info, $id, $start, $total, $members, $shade, $class,
   $member, $field, $page_qty, $url, $connector, $start_link,
   $finalcount, $qtycol, $pending );
}
require_once( 'footer.php' );
?>