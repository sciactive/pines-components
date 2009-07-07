<?php
defined('D_RUN') or die('Direct access prohibited');

display_notice(stripslashes($_REQUEST['message']));
print_default();
?>