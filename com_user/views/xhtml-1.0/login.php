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
$this->title = "Login to {$pines->config->system_name}";
?>
<form class="pf-form" name="login" id="p_muid_form" method="post" action="<?php echo htmlentities(pines_url()); ?>">
	<div class="pf-element">
		<label><span class="pf-label">Username</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="username" size="<?php echo ($this->position == 'content') ? '24' : '10'; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Password</span>
			<?php echo ($pines->config->com_user->empty_pw ? '<span class="pf-note">May be blank.</span>' : ''); ?>
			<input class="pf-field ui-widget-content ui-corner-all" type="password" name="password" size="<?php echo ($this->position == 'content') ? '24' : '10'; ?>" /></label>
	</div>
	<?php if ($pines->config->com_user->allow_registration) { ?>
	<div class="pf-element">
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				var new_account = false;
				var password = $("[name=password]", "#p_muid_form");
				var password2 = $("[name=password2]", "#p_muid_form");
				$("#p_muid_form").submit(function(){
					if (new_account && password.val() != password2.val()) {
						alert("Your passwords do not match.");
						return false;
					}
					return true;
				});
				
				var pass_reenter = $("#p_muid_pass_reenter");
				var submit_btn = $("[name=submit]", "#p_muid_form");
				$("[name=login_register]", "#p_muid_form").change(function(){
					if ($(this).val() == "register") {
						if ($(this).is(":checked")) {
							new_account = true;
							pass_reenter.slideDown();
							submit_btn.val("Sign Up");
						}
					} else {
						if ($(this).is(":checked")) {
							new_account = false;
							pass_reenter.slideUp();
							submit_btn.val("Login");
						}
					}
				}).change();
				$(":reset", "#p_muid_form").click(function(){
					new_account = false;
					pass_reenter.slideUp();
					submit_btn.val("Login");
				});
			});
			// ]]>
		</script>
		<span class="pf-label">Register</span>
		<label><input class="pf-field" type="radio" name="login_register" value="login" checked="checked" /> I have an account.</label>
		<?php echo ($this->position == 'content') ? '' : '<br />'; ?>
		<label><input class="pf-field" type="radio" name="login_register" value="register" /> I'm new.</label>
	</div>
	<div class="pf-element" id="p_muid_pass_reenter" style="display: none;">
		<label><span class="pf-label">Re-enter Password</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="password" name="password2" size="<?php echo ($this->position == 'content') ? '24' : '10'; ?>" /></label>
	</div>
	<?php } ?>
	<div class="pf-element<?php echo ($this->position == 'content') ? ' pf-buttons' : ''; ?>">
		<input type="hidden" name="option" value="com_user" />
		<input type="hidden" name="action" value="login" />
		<?php if ( isset($this->url) ) { ?>
		<input type="hidden" name="url" value="<?php echo htmlentities($this->url); ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Login" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="reset" name="reset" value="Reset" />
	</div>
</form>