<?php
/**
 * Provides a form for the user to edit a thread.
 *
 * @package Pines
 * @subpackage com_notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Editing ['.htmlspecialchars($this->entity->guid).']';
$this->note = 'For the entity '.htmlspecialchars($this->entity->entities[0]->guid.' with the tags '.implode(', ', $this->entity->entities[0]->tags)).'.';
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_notes', 'thread/save')); ?>">
	<div class="pf-element">
		<label><span class="pf-label">Hidden</span>
			<input class="pf-field" type="checkbox" name="hidden" value="ON"<?php echo $this->entity->hidden ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_notes', 'thread/list')); ?>');" value="Cancel" />
	</div>
</form>