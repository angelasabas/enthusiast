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
?>
</div></div>

<div class="sidebar">
<?php
if( isset( $logged_in ) && $logged_in ) {
?>
   <a href="joined.php">Joined</a>
   <a href="owned.php">Owned</a>
   <a href="members.php">Members</a>
   <a href="affiliates.php">Affiliates</a>
   <a href="emails.php">Emails</a>
   <a href="categories.php">Categories</a>
<?php
} else {
?>
   <p class="important">
   <?php echo ( isset( $login_message ) ) ? $login_message : '' ?>
   </p>
   <form action="login.php" method="post">
<?php
   if( isset( $_SESSION['next'] ) && $_SESSION['next'] != '' ) {
      echo '<input type="hidden" name="next" value="' . $_SESSION['next'] . '" />';
   }
?>
   <table class="loginbox">
   <tr><td colspan="2"><h1>Password</h1>
	<input type="password" name="login_password" />
	</td></tr>
	<tr><td class="right">
   Remember me?
	</td><td>
	<input type="checkbox" name="rememberme" value="yes" />
	</td></tr>
   <tr><td style="text-align: right;" colspan="2">
   <input type="submit" value="Log in" />
   </td></tr>
   </table>
   </form>
<?php
}
?>
</div>

<div class="footer">
Enthusiast 3 copyright &copy; 2004 - <?php echo date('Y'); ?> by Angela Sabas.<br />
<a href="http://indisguise.org/">Indisguise</a> |
<a href="http://scripts.indisguise.org/">Indiscripts</a>
</div>

</body>
</html>