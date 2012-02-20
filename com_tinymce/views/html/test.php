<?php
/**
 * Prints a textarea for testing TinyMCE.
 *
 * @package Pines
 * @subpackage com_tinymce
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'TinyMCE Standard Editor Test';
$pines->editor->load();
?>
<div class="pf-form">
	<div class="pf-element">
		<span class="pf-label">Regular Editor</span>
		<div class="pf-group">
			<div class="pf-field"><textarea rows="3" cols="35" class="peditor"></textarea></div>
		</div>
	</div>
	<div class="pf-element">
		<span class="pf-label">Simple Editor</span>
		<div class="pf-group">
			<div class="pf-field"><textarea rows="3" cols="35" class="peditor-simple"></textarea></div>
		</div>
	</div>
	<div class="pf-element">
		<span class="pf-label">Textarea</span>
		<div class="pf-group">
			<div class="pf-field"><textarea rows="3" cols="35"></textarea></div>
		</div>
	</div>
</div>