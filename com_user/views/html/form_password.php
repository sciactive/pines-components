<?php
/**
 * Provides a form to change a password.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Change Your Password';
?>
<script type="text/javascript">
	pines(function(){
		var password = $("#p_muid_form [name=new1]"),
			password2 = $("#p_muid_form [name=new2]");
		$("#p_muid_form").submit(function(){
			if (password.val() != password2.val()) {
				alert("Your passwords do not match.");
				return false;
			}
			return true;
		});
	});
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_user', 'savepassword')); ?>">
	<div class="pf-element pf-heading">
		<h3>Provide Your Current Password</h3>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Current Password <span class="pf-required">*</span></span>
			<input class="pf-field" type="password" name="current" size="24" value="" /></label>
	</div>
	<div class="pf-element pf-heading">
		<h3>Choose Your New Password</h3>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">New Password <span class="pf-required">*</span></span>
			<input class="pf-field" type="password" name="new1" size="24" value="" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Repeat Password <span class="pf-required">*</span></span>
			<input class="pf-field" type="password" name="new2" size="24" value="" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url())); ?>);" value="Cancel" />
	</div>
</form>