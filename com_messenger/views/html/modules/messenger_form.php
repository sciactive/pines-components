<?php
/**
 * Display the instant messenger options form.
 *
 * @package Components\messenger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="pf-form">
	<div class="pf-element">
		<label><span class="pf-label">Title</span>
			<span class="pf-note">"#name#" and "#username#" will be replaced by the current user's name and username.</span>
			<input class="pf-field" type="text" name="widget_title" size="36" value="<?php echo isset($this->widget_title) ? htmlspecialchars($this->widget_title) : 'Chat #name# [#username#]'; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Interface</span>
			<select class="pf-field" name="interface">
				<option value="inline"<?php echo $this->interface == 'inline' ? ' selected="selected"' : ''; ?>>Inline (Where the module is.)</option>
				<option value="floating"<?php echo $this->interface == 'floating' ? ' selected="selected"' : ''; ?>>Floating (In the bottom right.)</option>
			</select></label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Status Box</span>
		<label class="pf-field"><input name="hide_status_box" type="checkbox" value="true"<?php echo $this->hide_status_box == 'true' ? ' checked="checked"' : ''; ?>> Hide the status input box.</label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Guest</span>
		<?php if ($pines->config->com_messenger->guest_access) { ?>
		<label class="pf-field"><input name="guest" type="checkbox" value="true"<?php echo $this->guest == 'true' ? ' checked="checked"' : ''; ?>> Login to chat as a guest.</label>
		<?php } else { ?>
		<span class="pf-field">Guest access is disabled.</span>
		<?php } ?>
	</div>
</div>