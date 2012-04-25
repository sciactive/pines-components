<?php
/**
 * Provides a form to recover a user account password.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Account Recovery';
$this->note = 'You can now set a new password for your user account.';
?>
<script type="text/javascript">
	pines(function(){
		var password = $("[name=password]", "#p_muid_form");
		var password2 = $("[name=password2]", "#p_muid_form");
		$("#p_muid_form").submit(function(){
			if (password.val() != password2.val()) {
				alert("Your passwords do not match.");
				return false;
			}
			return true;
		});
	});
</script>
<form class="pf-form" id="p_muid_form" method="post" action="<?php echo htmlspecialchars(pines_url('com_user', 'recoverpassword')); ?>">
	<div class="pf-element pf-heading">
		<p>To reset your password, type your new password below.</p>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Password</span>
			<input class="pf-field" type="password" name="password" size="24" value="" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Re-enter Password</span>
			<input class="pf-field" type="password" name="password2" size="24" value="" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="form" value="true" />
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid ?>" />
		<input type="hidden" name="secret" value="<?php echo htmlspecialchars($this->secret); ?>" />
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url())); ?>);" value="Cancel" />
	</div>
</form>