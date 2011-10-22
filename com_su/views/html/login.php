<?php
/**
 * Provides a form for the user to switch users.
 *
 * @package Pines
 * @subpackage com_su
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Switch User';
?>
<style type="text/css">
	/* <![CDATA[ */
	.pf-form.com_su_login_form div.pf-element .pf-label, .pform_custom div.pf-element .pf-note {
		width: 110px; /* Width of labels. */
		text-align: right;
	}
	.pf-form.com_su_login_form div.pf-element .pf-group {
		 margin-left: 110px; /* Same as width of labels. */
	}
	.pf-form.com_su_login_form div.pf-buttons {
		padding-left: 95px; /* Width of labels + margin to inputs - button spacing. */
	}
	.pf-form.com_su_login_form div.pf-buttons.pf-centered {
		padding-left: 0;
	}
	/* ]]> */
</style>
<form class="pf-form com_su_login_form" method="post" action="<?php echo htmlspecialchars(pines_url()); ?>">
	<?php if ($this->pin_login) { ?>
	<div class="pf-element">
		<label><span class="pf-label">PIN</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="password" name="pin" size="15" /></label>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-group"><strong class="pf-field">OR</strong></div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label><span class="pf-label">Username</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="username" size="15" /></label>
	</div>
	<?php if (!$this->hide_password) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Password</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="password" name="password" size="15" /></label>
	</div>
	<?php } ?>
	<div class="pf-element pf-buttons pf-centered">
		<input type="hidden" name="option" value="com_su" />
		<input type="hidden" name="action" value="login" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Switch User" />
	</div>
</form>