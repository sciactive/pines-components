<?php
/**
 * Provides a form to select users.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="pf-form" id="p_muid_form">
	<div class="pf-element">
		<label>
			<span class="pf-label">User</span>
			<span class="pf-note">Hold Ctrl to select multiple.</span>
			<select class="pf-field" name="users" size="8" multiple="multiple">
				<?php foreach ($this->users as $cur_user) { ?>
				<option value="<?php echo (int) $cur_user->guid ?>"><?php echo htmlspecialchars("{$cur_user->name} [{$cur_user->username}]"); ?></option>
				<?php } ?>
			</select>
		</label>
	</div>
</div>