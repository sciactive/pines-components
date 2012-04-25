<?php
/**
 * Provides a form for the user to choose current user module options.
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
?>
<div class="pf-form">
	<div class="pf-element">
		This module will only be shown if there is a user currently logged in.
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Text</span>
			<span class="pf-note">"#name#" and "#username#" will be replaced by the current user's name and username.</span>
			<input class="pf-field" type="text" name="text" size="36" value="Logged in as #name# [#username#]." /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Text Align</span>
			<select class="pf-field" name="text_align">
				<option value="inherit">Don't Change</option>
				<option value="left">Left</option>
				<option value="right">Right</option>
				<option value="center">Center</option>
				<option value="justify">Justify</option>
			</select></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Font Style</span>
			<select class="pf-field" name="font_style">
				<option value="inherit">Don't Change</option>
				<option value="normal">Normal</option>
				<option value="italic">Italic</option>
				<option value="oblique">Oblique</option>
			</select></label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Margin (Pixels)</span>
		<label class="pf-field" style="white-space: nowrap;">Top: <input type="number" name="margin_top" size="5" value="0" /></label>
		<label class="pf-field" style="white-space: nowrap;">Right: <input type="number" name="margin_right" size="5" value="0" /></label>
		<label class="pf-field" style="white-space: nowrap;">Bottom: <input type="number" name="margin_bottom" size="5" value="0" /></label>
		<label class="pf-field" style="white-space: nowrap;">Left: <input type="number" name="margin_left" size="5" value="0" /></label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Padding (Pixels)</span>
		<label class="pf-field" style="white-space: nowrap;">Top: <input type="number" name="padding_top" size="5" value="0" /></label>
		<label class="pf-field" style="white-space: nowrap;">Right: <input type="number" name="padding_right" size="5" value="0" /></label>
		<label class="pf-field" style="white-space: nowrap;">Bottom: <input type="number" name="padding_bottom" size="5" value="0" /></label>
		<label class="pf-field" style="white-space: nowrap;">Left: <input type="number" name="padding_left" size="5" value="0" /></label>
	</div>
</div>