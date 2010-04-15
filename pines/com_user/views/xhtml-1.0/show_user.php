<?php
/**
 * Show the currently logged in user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->show_title = false;
?>
<div class="ui-helper-clearfix">
	<div style="float: right; clear: right;">Logged in as <?php echo "{$_SESSION['user']->name} [{$_SESSION['user']->username}]"; ?>.</div>
</div>