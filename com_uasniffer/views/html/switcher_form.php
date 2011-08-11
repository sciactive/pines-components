<?php
/**
 * Provides a form to set up the switcher.
 *
 * @package Pines
 * @subpackage com_uaswitcher
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="pf-form">
	<div class="pf-element">
		<label><span class="pf-label">Mobile Link Text</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="mobile_text" size="24" value="Mobile Version" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Desktop Link Text</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="desktop_text" size="24" value="Desktop Version" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Center Links</span>
			<input class="pf-field" type="checkbox" name="center" value="true" checked="checked" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Show Both</span>
			<span class="pf-note">Show both links even when one doesn't apply.</span>
			<input class="pf-field" type="checkbox" name="show_both" value="true" /></label>
	</div>
</div>