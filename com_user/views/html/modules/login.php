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
$this->check_username = ($pines->config->com_user->allow_registration && $pines->config->com_user->check_username);
if ($this->check_username)
	$pines->icons->load();

// Activate SAWASC support.
$this->sawasc = $pines->com_user->activate_sawasc();

?>
<?php if ($this->check_username) { ?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_username_loading {
		background-position: left;
		background-repeat: no-repeat;
		padding-left: 16px;
		display: none;
	}
	#p_muid_username_message {
		background-position: left;
		background-repeat: no-repeat;
		padding-left: 20px;
		line-height: 16px;
	}
	/* ]]> */
</style>
<?php } ?>
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
				<?php if ($this->style != 'small') { ?>
				<span class="pf-group" style="display: block;">
				<?php } ?>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="username" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" />
					<?php if ($this->check_username) { echo ($this->style == 'compact') ? '<br class="pf-clearing" />' : ''; ?>
					<span class="pf-field picon picon-throbber loader" id="p_muid_username_loading" style="display: none;">&nbsp;</span>
					<span class="pf-field picon" id="p_muid_username_message" style="display: none;"></span>
					<?php } ?>
				<?php if ($this->style != 'small') { ?>
				</span>
				<?php } ?>
			</label>
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
					var username = $("[name=username]", "#p_muid_form");
					var password = $("[name=password]", "#p_muid_form");
					var password2 = $("[name=password2]", "#p_muid_form");
					$("#p_muid_form").submit(function(){
						if (new_account && password.val() != password2.val()) {
							alert("Your passwords do not match.");
							return false;
						}
						return true;
					});

					<?php if ($this->check_username) { ?>
					// Check usernames.
					var un_loading = $("#p_muid_username_loading");
					var un_message = $("#p_muid_username_message");
					username.change(function(){
						if (!new_account) {
							un_loading.hide();
							username.removeClass("ui-state-error");
							un_message.removeClass("picon-task-complete").removeClass("picon-task-attempt").html("").hide();
							return;
						}
						var id = "<?php echo $this->entity->guid; ?>";
						$.ajax({
							url: "<?php echo addslashes(pines_url('com_user', 'checkusername')); ?>",
							type: "POST",
							dataType: "json",
							data: {"id": id, "username": username.val()},
							beforeSend: function(){
								un_loading.show();
								username.removeClass("ui-state-error");
								un_message.removeClass("picon-task-complete").removeClass("picon-task-attempt").html("").hide();
							},
							complete: function(){
								un_loading.hide();
							},
							error: function(){
								username.addClass("ui-state-error");
								un_message.addClass("picon-task-attempt").html("Error checking username. Please check your internet connection.").show();
							},
							success: function(data){
								if (!data) {
									username.addClass("ui-state-error");
									un_message.addClass("picon-task-attempt").html("Error checking username.").show();
									return;
								}
								if (data.result) {
									username.removeClass("ui-state-error");
									un_message.addClass("picon-task-complete").html(data.message).show();
									return;
								}
								username.addClass("ui-state-error");
								un_message.addClass("picon-task-attempt").html(data.message).show();
							}
						});
					}).blur(function(){
						username.change();
					});
					<?php } ?>

					var pass_reenter = $("#p_muid_register_form");
					var recovery = $("#p_muid_recovery");
					var submit_btn = $("[name=submit]", "#p_muid_form");
					$("[name=login_register]", "#p_muid_form").change(function(){
						if ($(this).val() == "register") {
							if ($(this).is(":checked")) {
								new_account = true;
								pass_reenter.slideDown("fast");
								recovery.slideUp("fast");
								submit_btn.val("Sign Up");
								username.change();
							}
						} else {
							if ($(this).is(":checked")) {
								new_account = false;
								pass_reenter.slideUp("fast");
								recovery.slideDown("fast");
								submit_btn.val("Login");
								username.change();
							}
						}
					}).change();
					$(":reset", "#p_muid_form").click(function(){
						new_account = false;
						pass_reenter.slideUp("fast");
						recovery.slideUp("fast");
						submit_btn.val("Login");
						username.change();
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
			<?php if ($pines->config->com_user->referral_codes) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Referral Code</span>
					<span class="pf-note">Optional</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="referral_code" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
			</div>
			<?php } ?>
		</div>
		<?php } if (!$this->hide_recovery && $pines->config->com_user->pw_recovery) { ?>
		<div class="pf-element" id="p_muid_recovery">
			<span class="pf-label" style="height: 1px;">&nbsp;</span>
			<a class="pf-field" href="<?php echo htmlspecialchars(pines_url('com_user', 'recover')); ?>">I can't access my account.</a>
		</div>
		<?php } ?>
		<div class="pf-element<?php echo ($this->style == 'small') ? '' : ' pf-buttons'; ?>">
			<input type="hidden" name="option" value="com_user" />
			<input type="hidden" name="action" value="login" />
			<?php if ($this->sawasc) { ?>
			<input type="hidden" name="ClientHash" value="" />
			<?php } if ( !empty($this->url) ) { ?>
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