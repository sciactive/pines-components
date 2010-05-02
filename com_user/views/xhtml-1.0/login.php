<?php
/**
 * Provides a form for the user to login.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Login to {$pines->config->option_title}";
?>
<form class="pf-form" name="login" id="login_form" method="post" action="<?php echo htmlentities(pines_url()); ?>">
	<div class="pf-element">
		<label><span class="pf-label">Username</span>
			<input class="pf-field ui-widget-content" type="text" name="username" size="24" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Password</span>
			<?php echo ($pines->config->com_user->empty_pw ? '<span class="pf-note">May be blank.</span>' : ''); ?>
			<input class="pf-field ui-widget-content" type="password" name="password" size="24" /></label>
	</div>
	<?php if ($pines->config->com_user->allow_registration) { ?>
	<div class="pf-element">
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				var new_account = false;
				var password = $("#login_form [name=password]");
				var password2 = $("#login_form [name=password2]");
				$("#login_form").submit(function(){
					if (new_account && password.val() != password2.val()) {
						alert("Your passwords do not match.");
						return false;
					}
					return true;
				});
				
				var pass_reenter = $("#login_form [name=pass_reenter]");
				var submit_btn = $("#login_form [name=submit]");
				$("#login_form [name=login_register]").change(function(){
					if ($(this).is(":checked") && $(this).val() == "register") {
						new_account = true;
						pass_reenter.slideDown();
						submit_btn.val("Sign Up");
					} else {
						new_account = false;
						pass_reenter.slideUp();
						submit_btn.val("Login");
					}
				}).change();
				$("#login_form :reset").click(function(){
					new_account = false;
					pass_reenter.slideUp();
					submit_btn.val("Login");
				});
			});
			// ]]>
		</script>
		<span class="pf-label">Register</span>
		<div class="pf-group">
			<label><input class="pf-field ui-widget-content" type="radio" name="login_register" value="login" checked="checked" /> I have an account.</label>
			<label><input class="pf-field ui-widget-content" type="radio" name="login_register" value="register" /> I'm new.</label>
		</div>
	</div>
	<div class="pf-element" name="pass_reenter" style="display: none;">
		<label><span class="pf-label">Re-enter Password</span>
			<input class="pf-field ui-widget-content" type="password" name="password2" size="24" /></label>
	</div>
	<?php } ?>
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