<?php
/**
 * Provides a form to fill in manager credentials.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<?php if (!gatekeeper('com_sales/manager')) { ?>
<form class="pf-form" method="post" action="">
	<div class="pf-element pf-heading">
		<h1>Manager Login</h1>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Username</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="username" size="24" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Password</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="password" name="password" size="24" /></label>
	</div>
</form>
<?php } ?>