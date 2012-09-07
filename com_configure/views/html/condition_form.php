<?php
/**
 * Provides a form for the user to edit a condition.
 *
 * @package Components\configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Conditional Configuration' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide condition details in this form.';
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_configure', 'condition/save')); ?>">
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
			<input class="pf-field" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element pf-heading">
		<h3>Conditional Configuration</h3>
		<p>Configuration for this will only be applied if all these conditions are met.</p>
	</div>
	<div class="pf-element pf-full-width">
		<?php
		$module = new module('system', 'conditions');
		$module->conditions = $this->entity->conditions;
		echo $module->render();
		unset($module);
		?>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($this->entity->guid); ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_configure', 'list', array('percondition' => '1')))); ?>);" value="Cancel" />
	</div>
</form>