<?php
/**
 * Provides a form to set up the switcher.
 *
 * @package Components\uasniffer
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
		<label><span class="pf-label">Mobile Link Text</span>
			<input class="pf-field" type="text" name="mobile_text" size="24" value="<?php echo isset($this->mobile_text) ? htmlspecialchars($this->mobile_text) : 'Mobile Version'; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Desktop Link Text</span>
			<input class="pf-field" type="text" name="desktop_text" size="24" value="<?php echo isset($this->desktop_text) ? htmlspecialchars($this->desktop_text) : 'Desktop Version'; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Center Links</span>
			<input class="pf-field" type="checkbox" name="center" value="true"<?php echo !isset($this->center) || ($this->center == 'true') ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Show Both</span>
			<span class="pf-note">Show both links even when one doesn't apply.</span>
			<input class="pf-field" type="checkbox" name="show_both" value="true"<?php echo $this->show_both == 'true' ? ' checked="checked"' : ''; ?> /></label>
	</div>
</div>