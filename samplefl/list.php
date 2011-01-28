<?php
require_once( 'header.php' );
?>

<h1>Listed Fans</h1>

<?php
include 'config.php';
//$hide_members_until_final = true;
include ENTH_PATH . 'show_sort.php';
include ENTH_PATH . 'show_members.php';

require_once( 'footer.php' );
?>