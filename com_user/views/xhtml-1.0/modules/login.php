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
if (empty($this->title))
	$this->title = "Login to {$pines->config->system_name}";
?>
<?php if ($this->sawasc || ($this->style != 'compact' && $this->style != 'small')) { ?>
<script type="text/javascript">
	// <![CDATA[
	<?php if ($this->sawasc) { ?>
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_user/includes/hash.js");
	<?php } ?>
	pines(function(){
		<?php if ($this->style != 'compact' && $this->style != 'small') { ?>
		$("input[name=username]", "#p_muid_form").focus();
		<?php } if ($this->sawasc) { ?>
		$("#p_muid_form").submit(function(){
			// SAWASC code
			if ($("input[name=login_register][value=register]:checked", "#p_muid_form").length)
				return true;
			var password_box = $("input[name=password]", "#p_muid_form");
			var password = password_box.val();
			var ClientComb = "<?php echo addslashes($_SESSION['sawasc']['ServerCB']); ?>" + md5(password+'7d5bc9dc81c200444e53d1d10ecc420a');
			<?php if ($_SESSION['sawasc']['algo'] == 'whirlpool') { ?>
			var ClientHash = Whirlpool(ClientComb).toLowerCase();
			<?php } else { ?>
			var ClientHash = md5(ClientComb);
			<?php } ?>
			$("input[name=ClientHash]", "#p_muid_form").val(ClientHash);
			password_box.val("");
		});
		<?php } ?>
	});
	// ]]>
</script>
<?php } ?>
<div id="p_muid_form"<?php echo ($this->style == 'compact') ? ' style="display: none;"' : ''; ?>>
	<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url()); ?>">
		<div class="pf-element">
			<label><span class="pf-label">Username</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="username" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Password</span>
				<?php echo ($pines->config->com_user->pw_empty ? '<span class="pf-note">May be blank.</span>' : ''); ?>
				<input class="pf-field ui-widget-content ui-corner-all" type="password" name="password" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
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

					var pass_reenter = $("#p_muid_register_form");
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
			<?php if ($this->style != 'small') { ?>
			<span class="pf-label">Register</span>
			<?php } ?>
			<label><input class="pf-field" type="radio" name="login_register" value="login" checked="checked" /> I have an account.</label>
			<?php echo ($this->style == 'small') ? '<br />' : ''; ?>
			<label><input class="pf-field" type="radio" name="login_register" value="register" /> I'm new.</label>
		</div>
		<br class="pf-clearing" />
		<div id="p_muid_register_form" style="display: none;">
			<div class="pf-element">
				<label><span class="pf-label">Re-enter Password</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="password" name="password2" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
			</div>
		</div>
		<?php } ?>
		<div class="pf-element<?php echo ($this->style == 'small') ? '' : ' pf-buttons'; ?>">
			<input type="hidden" name="option" value="com_user" />
			<input type="hidden" name="action" value="login" />
			<?php if ($this->sawasc) { ?>
			<input type="hidden" name="ClientHash" value="" />
			<?php } if ( isset($this->url) ) { ?>
			<input type="hidden" name="url" value="<?php echo htmlspecialchars($this->url); ?>" />
			<?php } ?>
			<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Login" />
			<?php if ($this->style != 'small') { ?>
			<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="reset" name="reset" value="Reset" />
			<?php } ?>
		</div>
	</form>
</div>
<?php if ($this->style == 'compact') { ?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var notice = $.pnotify({
			pnotify_title: "<?php echo addslashes($this->title); ?>",
			pnotify_text: $("#p_muid_form").detach().show(),
			pnotify_notice_icon: '',
			pnotify_width: 'auto',
			pnotify_hide: false,
			pnotify_history: false,
			pnotify_insert_brs: false,
			pnotify_before_open: function(pnotify){
				// This prevents the notice from displaying when it's created.
				pnotify.pnotify({
					pnotify_before_open: null
				});
				return false;
			}
		});
		$("#p_muid_compact_link").click(function(){
			notice.pnotify_display();
		});
	});
	// ]]>
</script>
<a href="javascript:void(0);" id="p_muid_compact_link"><?php echo htmlspecialchars($this->compact_text); ?></a>
<?php } ?>