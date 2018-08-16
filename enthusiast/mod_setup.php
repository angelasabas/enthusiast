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

/*___________________________________________________________________________*/
function show_step1() {
   require 'config.php';
?>
   <p>
   Welcome to the Setup section of Enthusiast 3. On this section, you will
   be able to add listings to your collective. It will automatically
   create the database tables for you if needed, and you need only to add
   the required <code>config.php</code> file in your listing's directory 
   and insert appropriate PHP snippets in your listing's pages.
   </p>

   <h2>Important notes:</h2>

   <ul>

   <li> Only uncheck the <code>Create database tables</code> checkbox if you
   are just adding the listing to your collective <i>AND the tables
   have already been previously created!</i> This is useful if you wish to
   run a specific listing already installed in another Enth3 admin panel
   and you just need this installation to connect to the other database. </li>

   <li> <b>Make sure you have proper MySQL privileges
   set!</b> Enthusiast 3 requires the privilege to <code>CREATE</code>,
   <code>ALTER</code>, and <code>DROP</code> database tables. When a listing
   is deleted, the database table is deleted as well. </li>

   </ul>

   <form action="setup.php" method="post">
   <input type="hidden" name="step" value="2" />

   <table>
<?php
   $dbserver = $db_server;
   $dbdatabase = $db_database;
   $dbuser = $db_user;
   $dbpassword = '';
   $dbtable = '';
   $catids = array();
   $subject = '';
   $email = '';
   $status = 'current';

   if( isset( $_POST['dbserver'] ) )
      $dbserver = $_POST['dbserver'];
   if( isset( $_POST['dbdatabase'] ) )
      $dbdatabase = $_POST['dbdatabase'];
   if( isset( $_POST['dbuser'] ) )
      $dbuser = $_POST['dbuser'];
   if( isset( $_POST['dbpassword'] ) )
      $dbpassword = $_POST['dbpassword'];
   if( isset( $_POST['dbtable'] ) )
      $dbtable = $_POST['dbtable'];
   if( isset( $_POST['catid'] ) )
      $catids = $_POST['catid'];
   if( isset( $_POST['subject'] ) )
      $subject = $_POST['subject'];
   if( isset( $_POST['email'] ) )
      $email = $_POST['email'];
   if( isset( $_POST['status'] ) )
      $status = $_POST['status'];
?>
   <tr><th colspan="2">Database settings</th></tr>

   <tr><td><b>
   Create database tables?
   </b></td><td>
   Only uncheck the <code>Create database tables</code> checkbox if you
   are just adding the listing to your collective <i>AND the tables
   have already been previously created!</i> This is useful if you wish to
   run a specific listing already installed in another Enth3 admin panel
   and you just need this installation to connect to the other database.
   <br /><br />
   <input type="checkbox" name="createtable" value="yes" checked="checked" />
   Yes, create the tables
   </td></tr>

   <tr class="rowshade"><td><b>
   Database server
   </b></td><td>
   This is the server your MySQL database is running on. The value you have
   set for this collective is, by default, set in the field for you.<br />
   <br />
   <input type="text" name="dbserver" value="<?php echo $dbserver ?>" />
   </td></tr>

   <tr><td><b>
   Database name
   </b></td><td>
   This is the actual name of the database you will be using for this
   listing. The database that you have set for this collective is, by
   default, set in the field for you..<br /><br />
   <input type="text" name="dbdatabase" value="<?php echo $dbdatabase ?>" />
   </td></tr>

   <tr class="rowshade"><td><b>
   Database user
   </b></td><td>
   This is the database username that has access privileges for the database
   set above. The user that you have set for this collective is, by default,
   set in the field for you.<br /><br />
   <input type="text" name="dbuser" value="<?php echo $dbuser ?>" />
   </td></tr>

   <tr><td><b>
   Database password
   </b></td><td>
   This is the password that allows the user above access to the database
   specified. Please type the password twice for verification.<br /><br />
   <input type="password" name="dbpassword" />
   <input type="password" name="dbpasswordv" />
   </td></tr>

   <tr class="rowshade"><td><b>
   Database table
   </b></td><td>
   This is the database table that the fanlisting will use.<br /><br />
   <input type="text" name="dbtable" value="<?php echo $dbtable ?>" />
   </td></tr>

   <tr><th colspan="2">
   Listing information
   </th></tr>

   <tr><td><b>
   Categories
   </b></td><td>
   This are the categories under your collective that you wish this listing
   be listed under.<br /><br />
   <select name="catid[]" multiple="multiple" size="5">
<?php
   $cats = enth_get_categories();
   $options = array();
   foreach( $cats as $cat ) {
      $optiontext = $cat['catname'];
      if( count( $ancestors =
         array_reverse( get_ancestors( $cat['catid'] ) ) ) > 1 ) {
         // get ancestors
         $text = '';
         foreach( $ancestors as $a )
            $text .= get_category_name( $a ) . ' > ';
         $optiontext = rtrim( $text, ' > ' );
         $optiontext = str_replace( '>', '&raquo;', $optiontext );
      }
      $options[] = array( 'text' => $optiontext, 'id' => $cat['catid'] );
   }
   usort( $options, 'category_array_compare' );
   $selected = explode( '|', $info['catid'] );
   foreach( $options as $o ) {
      echo '<option value="' . $o['id'];
      if( in_array( $o['id'], $catids ) )
         echo '" selected="selected';
      echo '">' . $o['text'] . '</option>';
   }
?>
   </select>
   </td></tr>

   <tr class="rowshade"><td><b>
   Subject
   </b></td><td>
   This is the subject of the listing.<br /><br />
   <input type="text" name="subject" value="<?php echo $subject ?>" />
   </td></tr>

   <tr><td><b>
   Email
   </b></td><td>
   This is the email address that all listing emails will come from.
   <br /><br />
   <input type="text" name="email" value="<?php echo $email ?>" />
   </td></tr>

   <tr class="rowshade"><td><b>
   Status
   </b></td><td>
   This is the status of your listing, and is especially useful for
   making 'upcoming' lists. It is set to "current" by default.<br /><br />
   <select name="status">
   <option value="<?php echo $status ?>"><?php echo ucfirst( $status ) ?></option>
   <option value="">--</option>
   <option value="current">Current</option>
   <option value="upcoming">Upcoming</option>
   <option value="pending">Pending Application</option>
   </select>
   </td></tr>

   <tr><td colspan="2" class="right">
   <input type="submit" value="Add and setup this fanlisting!" />
   <input type="reset" value="Reset form values" />
   </td></tr>

   </table>
<?php
} // end of show_step1



/*___________________________________________________________________________*/
function do_step1() {
   require 'config.php';

   $dbserver = '';
   $dbdatabase = '';
   $dbuser = '';
   $dbpassword = '';
   $dbpasswordv = '';
   $dbtable = '';
   $catids = array();
   $subject = '';
   $email = '';
   $status = '';

   if( isset( $_POST['dbserver'] ) )
      $dbserver = $_POST['dbserver'];
   if( isset( $_POST['dbdatabase'] ) )
      $dbdatabase = $_POST['dbdatabase'];
   if( isset( $_POST['dbuser'] ) )
      $dbuser = $_POST['dbuser'];
   if( isset( $_POST['dbpassword'] ) )
      $dbpassword = $_POST['dbpassword'];
   if( isset( $_POST['dbpasswordv'] ) )
      $dbpasswordv = $_POST['dbpasswordv'];
   if( isset( $_POST['dbtable'] ) )
      $dbtable = $_POST['dbtable'];
   if( isset( $_POST['catid'] ) )
      $catids = $_POST['catid'];
   if( isset( $_POST['subject'] ) )
      $subject = $_POST['subject'];
   if( isset( $_POST['email'] ) )
      $email = $_POST['email'];
   if( isset( $_POST['status'] ) )
      $status = $_POST['status'];

   if( $dbserver && $dbdatabase && $dbuser && $dbpassword && $dbtable &&
      $dbpasswordv && $catids && $subject && $email && $status ) {

      // check if db password is the same
      if( $dbpassword != $dbpasswordv ) {
         echo '<p class="error">The database password verification ' .
            'does not match.</p>';
         return false;
      }

      // create database if yes
      if( isset( $_POST['createtable'] ) && $_POST['createtable'] == 'yes' ) {
         $query = "CREATE TABLE `$dbtable` ( " .
            '`email` VARCHAR(64) NOT NULL default "", ' .
            '`name` VARCHAR(128) NOT NULL default "", ' .
            '`country` VARCHAR(128) NOT NULL default "", ' .
            '`url` VARCHAR(255) default NULL, ' .
            '`pending` TINYINT(1) NOT NULL default 0, ' .
            '`password` VARCHAR(255) NOT NULL default "", ' .
            '`showemail` TINYINT(1) NOT NULL default 1, ' .
            '`showurl` TINYINT(1) NOT NULL default 1, ' .
            '`added` date default NULL, ' .
            'PRIMARY KEY( email ), ' .
            'FULLTEXT( email, name, country, url ) ' .
            ') ENGINE=MyISAM;';

         try {
            $db_link_list = new PDO('mysql:host=' . $dbserver . ';dbname=' . $dbdatabase . ';charset=utf8', $dbuser, $dbpassword);
            $db_link_list->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         } catch (PDOException $e) {
            die( DATABASE_CONNECT_ERROR . $e->getMessage() );
         }
         $result = $db_link_list->prepare($query);
         $result->execute();
         if( !$result ) {
            log_error( __FILE__ . ':' . __LINE__,
               'Error executing query: <i>' . $result->errorInfo()[2] .
               '</i>; Query is: <code>' . $query . '</code>' );
            echo '<p class="error">Listing database creation failed.</p>';
            die( STANDARD_ERROR );
         }
         $db_link_list = null;
      }

      try {
         $db_link = new PDO('mysql:host=' . $db_server . ';dbname=' . $db_database . ';charset=utf8', $db_user, $db_password);
         $db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
         die( DATABASE_CONNECT_ERROR . $e->getMessage() );
      }

      // setup temp database values
      $stat = '0';
      if( $status == 'upcoming' )
         $stat = 1;
      else if( $status == 'current' )
         $stat = 2;

      // use templates
      $signup = file_get_contents( 'templates/signup.txt' );
      $approval = file_get_contents( 'templates/approval.txt' );
      $update = file_get_contents( 'templates/update.txt' );
      $lostpass = file_get_contents( 'templates/lostpass.txt' );
      $listtemplate = file_get_contents( 'templates/listtemplate.txt' );
      $affiliatestemplate =
         file_get_contents( 'templates/affiliatestemplate.txt' );
      $statstemplate = file_get_contents( 'templates/statstemplate.txt' );

      // prep category id
      $catid = implode( '|', $catids );

      // put into $db_owned database
      $query = "INSERT INTO `$db_owned` ( `listingid`,`dbserver`, `dbuser`, " .
         '`dbpassword`, `dbdatabase`, `dbtable`, `subject`, `email`, ' .
         '`catid`, `status`, `emailsignup`, `emailapproved`, `emailupdate`, ' .
         '`emaillostpass`, `listtemplate`, `affiliatestemplate`, ' .
         '`statstemplate`, `opened` ) ' .
         "VALUES ( null, :dbserver, :dbuser, :dbpassword, " .
         ":dbdatabase, :dbtable, :subject, :email, :catid, :stat, " .
         ":signup, :approval, :update, :lostpass, :listtemplate, " .
         ":affiliatestemplate, :statstemplate, CURDATE() )";
      $result = $db_link->prepare($query);
      $result->bindParam(':dbserver', $dbserver, PDO::PARAM_STR);
      $result->bindParam(':dbuser', $dbuser, PDO::PARAM_STR);
      $result->bindParam(':dbpassword', $dbpassword, PDO::PARAM_STR);
      $result->bindParam(':dbdatabase', $dbdatabase, PDO::PARAM_STR);
      $result->bindParam(':dbtable', $dbtable, PDO::PARAM_STR);
      $result->bindParam(':subject', $subject, PDO::PARAM_STR);
      $result->bindParam(':email', $email, PDO::PARAM_STR);
      $result->bindParam(':catid', $catid, PDO::PARAM_INT);
      $result->bindParam(':stat', $stat, PDO::PARAM_STR);
      $result->bindParam(':signup', $signup, PDO::PARAM_STR);
      $result->bindParam(':approval', $approval, PDO::PARAM_STR);
      $result->bindParam(':update', $update, PDO::PARAM_STR);
      $result->bindParam(':lostpass', $lostpass, PDO::PARAM_STR);
      $result->bindParam(':listtemplate', $listtemplate, PDO::PARAM_STR);
      $result->bindParam(':affiliatestemplate', $affiliatestemplate, PDO::PARAM_STR);
      $result->bindParam(':statstemplate', $statstemplate, PDO::PARAM_STR);
      $result->execute();
      if( !$result ) {
         log_error( __FILE__ . ':' . __LINE__,
            'Error executing query: <i>' . $result->errorInfo()[2] .
            '</i>; Query is: <code>' . $query . '</code>' );
         echo '<p class="error">Listing database creation failed.</p>';
         die( STANDARD_ERROR );
      }

      return true;

   } else { // error!
?>
      <p class="error">
      You have left out some of the fields in this step. All fields on this
      page is required. Please fill them about and try again.
      </p>
<?php
      return false;
   }
} // end of do_step1




/*___________________________________________________________________________*/
function show_step2() {
   require 'config.php';
   $info = get_listing_info( '', $_POST['dbtable'] );
?>
   <p>
   <b>Congratulations!</b> Your listing information and database has been
   set up successfully. Your new listing has the Listing ID <b><?php echo $info['listingid'] ?></b>. You can now continue on to
   <a href="owned.php?action=edit&id=<?php echo $info['listingid'] ?>">this page</a>
   to continue customizing the listing.
   </p>
<?php
} // end of show_step2

?>
