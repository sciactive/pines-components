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
$this->title = 'Editing Thread ['.htmlspecialchars($this->entity->guid).']';
$this->note = 'For the entity '.htmlspecialchars($this->entity->entities[0]->guid.' with the tags '.implode(', ', $this->entity->entities[0]->tags)).'.';
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_notes', 'thread/save')); ?>">
	<div class="pf-element pf-heading">
		<h3>Options</h3>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Hidden</span>
			<input class="pf-field" type="checkbox" name="hidden" value="ON"<?php echo $this->entity->hidden ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Privacy</span>
			<select class="pf-field" name="privacy">
				<option value="only-me"<?php echo (!$this->entity->ac->other && !$this->entity->ac->group) ? ' selected="selected"' : ''; ?>>Only the Author</option>
				<option value="my-group"<?php echo (!$this->entity->ac->other && $this->entity->ac->group) ? ' selected="selected"' : ''; ?>>The Author's Group</option>
				<option value="everyone"<?php echo $this->entity->ac->other ? ' selected="selected"' : ''; ?>>Everyone</option>
			</select></label>
	</div>
	<div class="pf-element pf-heading">
		<h3>Notes</h3>
	</div>
	<div id="p_muid_notes_link" class="pf-element">
		<a href="javascript:void(0);" onclick="$('#p_muid_notes_link, #p_muid_notes').toggle();">Edit notes.</a>
	</div>
	<div id="p_muid_notes" style="display: none;">
		<style type="text/css" scoped="scoped">
			#p_muid_notes .note {
				padding: .5em;
				clear: left;
				margin-bottom: .2em;
			}
			#p_muid_notes .note .pf-element {
				padding: 0;
			}
		</style>
		<script type="text/javascript">
			pines(function(){
				$("#p_muid_notes").delegate(":checkbox", "click", function(){
					var checkbox = $(this);
					if (checkbox.is(":checked"))
						checkbox.closest(".note").addClass("ui-state-error ui-corner-all");
					else
						checkbox.closest(".note").removeClass("ui-state-error ui-corner-all");
				});
			});
		</script>
		<?php foreach ($this->entity->notes as $key => $cur_note) { ?>
		<div class="note ui-helper-clearfix">
			<div class="pf-element pf-full-width">
				<span class="pf-label"><?php echo htmlspecialchars($cur_note['user']->name); ?></span>
				<span class="pf-note"><?php echo htmlspecialchars(format_date($cur_note['date'], 'full_med')); ?></span>
				<span class="pf-note">
					<label><input type="checkbox" name="delete_note[]" value="<?php echo htmlspecialchars($key); ?>" /> Delete this note.</label>
				</span>
				<div class="pf-group" style="overflow: auto;">
					<div class="pf-field"><?php echo htmlspecialchars($cur_note['text']); ?></div>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_notes', 'thread/list')); ?>');" value="Cancel" />
	</div>
</form>