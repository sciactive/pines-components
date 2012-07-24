<?php
/**
 * Provides a form for the user to choose login form options.
 *
 * @package Components\user
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
		<label><span class="pf-label">Style</span>
			<span class="pf-note">Use small to put the form in a very narrow column. Use compact to provide a link to bring up the form in a dialog.</span>
			<select class="pf-field" name="style">
				<option value="normal"<?php echo $this->style == 'normal' ? ' selected="selected"' : ''; ?>>Normal</option>
				<option value="small"<?php echo $this->style == 'small' ? ' selected="selected"' : ''; ?>>Small</option>
				<option value="compact"<?php echo $this->style == 'compact' ? ' selected="selected"' : ''; ?>>Compact</option>
			</select></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Compact Link Text</span>
			<span class="pf-note">Only applicable for "Compact" style.</span>
			<input class="pf-field" type="text" name="compact_text" size="24" value="<?php echo isset($this->compact_text) ? htmlspecialchars($this->compact_text) : 'Login/Register'; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Hide Recovery Link</span>
			<span class="pf-note">Hide the password recovery link. (Only appears when password recovery is on.)</span>
			<input class="pf-field" type="checkbox" name="hide_recovery" value="true"<?php echo $this->hide_recovery == 'true' ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">URL</span>
			<span class="pf-note">URL to which to redirect the user after they log in.</span>
			<input class="pf-field" type="text" name="url" size="24" value="<?php echo htmlspecialchars($this->url); ?>" /></label>
	</div>
</div>