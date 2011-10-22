<?php
/**
 * Provides a printable esp form.
 *
 * @package Pines
 * @subpackage com_esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'ESP History ['.$this->entity->guid.']';
?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_esp', 'history')); ?>">
	<div class="pf-element pf-heading">
		<h1><?php echo $this->entity->customer->name;?></h1>
	</div>
	<?php foreach ($this->entity->history as $cur_history) { ?>
	<div class="pf-element">
		<span class="pf-label"><?php echo format_date($cur_history['date']); ?></span>
		<span class="pf-note"><?php echo $cur_history['user']->name; ?></span>
		<span class="pf-field"><?php echo htmlspecialchars($cur_history['note']); ?></span>
	</div>
	<?php } if (isset($this->entity->claim_info)) { ?>
	<div class="pf-element pf-heading">
		<h1>Accidental Claim Information</h1>
	</div>
	<div class="pf-element">
		<span class="pf-label"><?php echo format_date($this->entity->claim_info['date']); ?></span>
		<span class="pf-note"><?php echo $this->entity->claim_info['user']->name; ?></span>
		<span class="pf-field"><?php echo htmlspecialchars($this->entity->claim_info['note']); ?></span>
	</div>
	<?php } ?>
	<div class="pf-element">
		<span class="pf-label">Note</span>
		<span class="pf-note">Comments or Information</span>
		<textarea class="pf-field" name="history_note"></textarea>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_esp', 'list')); ?>');" value="Cancel" />
	</div>
</form>