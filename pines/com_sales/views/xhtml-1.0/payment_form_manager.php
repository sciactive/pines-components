<?php
/**
 * Provides a form to fill in manager credentials.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<?php if (!gatekeeper('com_sales/manager')) { ?>
<form class="pform" method="post" action="">
	<div class="element heading">
		<h1>Manager Login</h1>
	</div>
	<div class="element">
		<label><span class="label">Username</span>
			<input class="field ui-widget-content" type="text" name="username" size="24" /></label>
	</div>
	<div class="element">
		<label><span class="label">Password</span>
			<input class="field ui-widget-content" type="password" name="password" size="24" /></label>
	</div>
</form>
<?php } ?>