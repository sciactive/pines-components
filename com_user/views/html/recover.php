<?php
/**
 * Provides a form to recover a user account.
 *
 * @package Components
 * @subpackage user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Account Recovery';
$this->note = 'If you\'ve forgotten your username or password, you can use this form to recover your account.';
?>
<script type="text/javascript">
	pines(function(){
		var form = $("#p_muid_form");
		$("input[name=type]", "#p_muid_form").change(function(){
			var box = $(this);
			if (box.is(":checked"))
				form.find(".toggle").hide().filter("."+box.val()).show();
		}).change();
	});
</script>
<form class="pf-form" id="p_muid_form" method="post" action="<?php echo htmlspecialchars(pines_url('com_user', 'recover')); ?>">
	<div class="pf-element">
		<span class="pf-label">Recovery Type</span>
		<label><input class="pf-field" type="radio" name="type" value="password" checked="checked" /> I forgot my password.</label>
		<label><input class="pf-field" type="radio" name="type" value="username" /> I forgot my username.</label>
	</div>
	<div class="pf-element pf-heading">
		<p class="toggle password">To reset your password, type your username you use to sign in below.</p>
		<p class="toggle username" style="display: none;">To retrieve your username, type your full email address exactly as you entered it when creating your account below.</p>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label toggle password">Username</span>
			<span class="pf-label toggle username" style="display: none;">Email Address</span>
			<input class="pf-field" type="text" name="account" size="24" value="" />
		</label>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url())); ?>);" value="Cancel" />
	</div>
</form>