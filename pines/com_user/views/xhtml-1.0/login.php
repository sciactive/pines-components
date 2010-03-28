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
<form class="pform" name="login" method="post" action="<?php echo pines_url(); ?>">
	<div class="element">
		<label><span class="label">Username</span>
			<input class="field ui-widget-content" type="text" name="username" size="24" /></label>
	</div>
	<div class="element">
		<label><span class="label">Password</span>
			<?php echo ($pines->config->com_user->empty_pw ? '<span class="note">May be blank.</span>' : ''); ?>
			<input class="field ui-widget-content" type="password" name="password" size="24" /></label>
	</div>
	<div class="element buttons">
		<input type="hidden" name="option" value="com_user" />
		<input type="hidden" name="action" value="login" />
		<?php if ( isset($_REQUEST['url']) ) { ?>
		<input type="hidden" name="url" value="<?php echo htmlentities($_REQUEST['url']); ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Login" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="reset" name="reset" value="Reset" />
	</div>
</form>