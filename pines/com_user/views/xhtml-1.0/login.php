<?php
/**
 * Provides a form for the user to login.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Login to {$pines->config->option_title}";
$this->note = 'Please enter your credentials to login.';
?>
<form class="pf-form" name="login" method="post" action="<?php echo htmlentities(pines_url()); ?>">
	<div class="pf-element">
		<label><span class="pf-label">Username</span>
			<input class="pf-field ui-widget-content" type="text" name="username" size="24" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Password</span>
			<?php echo ($pines->config->com_user->empty_pw ? '<span class="pf-note">May be blank.</span>' : ''); ?>
			<input class="pf-field ui-widget-content" type="password" name="password" size="24" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="option" value="com_user" />
		<input type="hidden" name="action" value="login" />
		<?php if ( isset($_REQUEST['url']) ) { ?>
		<input type="hidden" name="url" value="<?php echo htmlentities($_REQUEST['url']); ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Login" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="reset" name="reset" value="Reset" />
	</div>
</form>