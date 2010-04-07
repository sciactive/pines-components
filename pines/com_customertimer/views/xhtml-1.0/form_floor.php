<?php
/**
 * Provides a form for the user to edit a floor.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Floor' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide floor details in this form.';
?>
<form class="pform" method="post" id="floor_details" action="<?php echo htmlentities(pines_url('com_customertimer', 'savefloor')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		$(function(){
			var layout = $("#layout");

			function update_layout() {
				
			}

			$("#floor_tabs").tabs();
			update_layout();
		});
		// ]]>
	</script>
	<div id="floor_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_layout">Layout</a></li>
		</ul>
		<div id="tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
					<?php if (isset($this->entity->uid)) { ?>
				<span>Created By: <span class="date"><?php echo $pines->user_manager->get_username($this->entity->uid); ?></span></span>
				<br />
					<?php } ?>
				<span>Created On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_cdate); ?></span></span>
				<br />
				<span>Modified On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_mdate); ?></span></span>
			</div>
			<?php } ?>
			<div class="element">
				<label><span class="label">Name</span>
					<input class="field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Enabled</span>
					<input class="field ui-widget-content" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="element">
				<span class="label">Description</span>
				<textarea class="field ui-widget-content" rows="3" cols="35" name="description"><?php echo $this->entity->description; ?></textarea>
			</div>
			<?php if (isset($this->entity->background)) { ?>
			<div class="element">
				<span class="label">Current Background</span>
				<div class="group">
					<span class="field"><img src="<?php echo $this->entity->get_background(); ?>" alt="Floor Background" /></span>
					<br />
					<label><span class="field"><input class="field ui-widget-content" type="checkbox" name="remove_background" value="ON" />Remove this background.</span></label>
				</div>
			</div>
			<?php } ?>
			<div class="element">
				<label><span class="label">Change Background</span>
					<input class="field ui-widget-content" type="file" name="background" /></label>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_layout">
			<br class="spacer" />
		</div>
	</div>
	<br />
	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_customertimer', 'listfloors')); ?>');" value="Cancel" />
	</div>
</form>