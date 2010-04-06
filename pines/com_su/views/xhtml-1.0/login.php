<?php
/**
 * Provides a form for the user to switch users.
 *
 * @package Pines
 * @subpackage com_su
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Switch User";
?>
<style type="text/css">
	/* <![CDATA[ */
	.pform.com_su_login_form div.element .label, .pform_custom div.element .note {
		width: 110px; /* Width of labels. */
		text-align: right;
	}
	.pform.com_su_login_form div.element .group {
		 margin-left: 110px; /* Same as width of labels. */
	}
	.pform.com_su_login_form div.buttons {
		padding-left: 95px; /* Width of labels + margin to inputs - button spacing. */
	}
	.pform.com_su_login_form div.buttons.centered {
		padding-left: 0;
	}
	/* ]]> */
</style>
<form class="pform com_su_login_form" name="login" method="post" action="<?php echo htmlentities(pines_url()); ?>">
	<?php if ($this->pin_login) { ?>
	<div class="element">
		<label><span class="label">PIN</span>
			<input class="field ui-widget-content" type="password" name="pin" size="15" /></label>
	</div>
	<div class="element full_width">
		<div class="group"><strong class="field">OR</strong></div>
	</div>
	<?php } ?>
	<div class="element">
		<label><span class="label">Username</span>
			<input class="field ui-widget-content" type="text" name="username" size="15" /></label>
	</div>
	<?php if (!$this->hide_password) { ?>
	<div class="element">
		<label><span class="label">Password</span>
			<input class="field ui-widget-content" type="password" name="password" size="15" /></label>
	</div>
	<?php } ?>
	<div class="element buttons centered">
		<input type="hidden" name="option" value="com_su" />
		<input type="hidden" name="action" value="login" />
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Switch User" />
	</div>
</form>