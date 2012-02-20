<?php
/**
 * Provides a form for the user to log back in.
 *
 * @package Pines
 * @subpackage com_timeoutnotice
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Login';
?>
<form class="pf-form com_timeoutnotice_login_form" method="post" action="<?php echo htmlspecialchars(pines_url()); ?>">
	<div class="pf-element">
		Your session has timed out.<br />
		Please login again to continue, or <a href="javascript:void(0)" class="already_loggedin">did you already login</a>?
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Username</span>
			<input class="pf-field" type="text" name="username" size="24" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Password</span>
			<input class="pf-field" type="password" name="password" size="24" /></label>
	</div>
	<div class="pf-element pf-buttons pf-centered">
		<input type="hidden" name="option" value="com_timeoutnotice" />
		<input type="hidden" name="action" value="login" />
	</div>
</form>