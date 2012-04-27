<?php
/**
 * Display a form to edit configuration settings.
 *
 * @package Components\configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars("Editing Configuration for {$this->entity->info->name} {$this->entity->info->version} ({$this->entity->name})");
if ($this->entity->per_user) {
	if ($this->entity->user->is_com_configure_condition)
		$this->note = 'For conditional configuration <a data-entity="'.htmlspecialchars($this->entity->user->guid).'" data-entity-context="com_configure_condition">'.htmlspecialchars($this->entity->user->name).'</a>.';
	elseif ($this->entity->type == 'user')
		$this->note = "For {$this->entity->type} <a data-entity=\"".htmlspecialchars($this->entity->user->guid)."\" data-entity-context=\"user\">".htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]").'</a>.';
	elseif ($this->entity->type == 'group')
		$this->note = "For {$this->entity->type} <a data-entity=\"".htmlspecialchars($this->entity->user->guid)."\" data-entity-context=\"group\">".htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->groupname}]").'</a>.';
}
$pines->com_ptags->load();
?>
<style type="text/css">
	#p_muid_form .setting .ui-ptags {
		display: inline-block;
	}
	#p_muid_form .default .pf-field {
		display: inline-block;
		padding: .2em;
		margin-right: .3em;
	}
</style>
<script type="text/javascript">
	pines(function(){
		$(".p_muid_do_tags", "#p_muid_form").ptags({ptags_delimiter: ';;', ptags_sortable: true});
		$("#p_muid_form").delegate(".p_muid_default_checkbox", "change", function(){
			var checkbox = $(this);
			if (checkbox.attr("checked")) {
				checkbox.closest("div.pf-element").children("div.default").hide().end().children("div.setting").show();
			} else {
				checkbox.closest("div.pf-element").children("div.setting").hide().end().children("div.default").show();
			}
		}).find(".p_muid_default_checkbox").change();

		$(".setting-label", "#p_muid_form").each(function(){
			var label = $(this);
			label.popover({
				title: label.find(".cname").text(),
				content: "<p>"+label.find(".description").html()+"<br /><br /><strong>Default:</strong> "+label.nextAll(".default").children().text()+"</p>",
				selector: ".cname"
			})
		});
	});
</script>
<form id="p_muid_form" class="pf-form" action="<?php echo htmlspecialchars(pines_url('com_configure', 'save')); ?>" method="post">
	<div class="pf-element pf-heading">
		<p>Check a setting to set it manually, or leave it unchecked to use the <?php echo $this->entity->per_user ? 'system configured' : 'default'; ?> setting.</p>
	</div>
	<?php foreach ($this->entity->defaults as $cur_var) {
		if (key_exists($cur_var['name'], $this->entity->config_keys)) {
			$is_default = false;
			$cur_value = $this->entity->config_keys[$cur_var['name']];
		} else {
			$is_default = true;
			$cur_value = $cur_var['value'];
		} ?>
	<div class="pf-element pf-full-width">
		<label class="setting-label">
			<span class="pf-label">
				<input class="p_muid_default_checkbox" type="checkbox" name="manset_<?php echo htmlspecialchars($cur_var['name']); ?>" value="ON" <?php echo $is_default ? '' : 'checked="checked" '; ?>/>
				<span class="cname"><?php echo htmlspecialchars($cur_var['cname']); ?></span>
				<span class="pf-note">
					<?php
					if (strlen($cur_var['description']) > 60)
						$desc = substr($cur_var['description'], 0, 60).'&hellip;';
					else
						$desc = $cur_var['description'];
					echo str_replace("\n", '<br />', htmlspecialchars($desc, null, null, false));
					?>
					<span class="description" style="display: none;">
						<?php echo str_replace("\n", '<br />', htmlspecialchars($cur_var['description'])); ?>
					</span>
				</span>
			</span>
		</label>
		<div class="setting" style="display: none;">
			<?php if (is_array($cur_var['options'])) { ?>
				<?php foreach($cur_var['options'] as $key => $cur_option) {
					$display = is_string($key) ? $key : $cur_option; ?>
				<div class="pf-group">
					<label><input class="pf-field" type="<?php echo is_array($cur_var['value']) ? 'checkbox' : 'radio'; ?>" name="opt_multi_<?php echo htmlspecialchars($cur_var['name']); ?><?php echo is_array($cur_var['value']) ? '[]' : ''; ?>" value="<?php echo addslashes(htmlspecialchars(serialize($cur_option))); ?>" <?php echo ( (is_array($cur_value) && in_array($cur_option, $cur_value) || (!is_array($cur_value) && $cur_value == $cur_option)) ? 'checked="checked" ' : ''); ?>/> <?php echo htmlspecialchars($display); ?></label><br />
				</div>
				<?php } ?>
			<?php } elseif (is_array($cur_var['value'])) { ?>
				<div class="pf-group pf-full-width">
					<?php if (is_int($cur_var['value'][0])) { ?>
					<input class="pf-field p_muid_do_tags" type="text" name="opt_int_<?php echo htmlspecialchars($cur_var['name']); ?>" value="<?php echo htmlspecialchars(implode(';;', $cur_value)); ?>" />
					<?php } elseif (is_float($cur_var['value'][0])) { ?>
					<input class="pf-field p_muid_do_tags" type="text" name="opt_float_<?php echo htmlspecialchars($cur_var['name']); ?>" value="<?php echo htmlspecialchars(implode(';;', $cur_value)); ?>" />
					<?php } elseif (is_string($cur_var['value'][0])) { ?>
					<div class="pf-field"><textarea rows="3" cols="35" class="p_muid_do_tags" style="width: 100%;" name="opt_string_<?php echo htmlspecialchars($cur_var['name']); ?>"><?php echo htmlspecialchars(implode(';;', $cur_value)); ?></textarea></div>
					<?php } ?>
				</div>
			<?php } else { ?>
				<?php if (is_bool($cur_var['value'])) { ?>
				<input class="pf-field" type="checkbox" name="opt_bool_<?php echo htmlspecialchars($cur_var['name']); ?>" value="ON" <?php echo ($cur_value ? 'checked="checked" ' : ''); ?>/>
				<?php } elseif (is_int($cur_var['value'])) { ?>
				<input class="pf-field" type="text" name="opt_int_<?php echo htmlspecialchars($cur_var['name']); ?>" value="<?php echo htmlspecialchars($cur_value); ?>" />
				<?php } elseif (is_float($cur_var['value'])) { ?>
				<input class="pf-field" type="text" name="opt_float_<?php echo htmlspecialchars($cur_var['name']); ?>" value="<?php echo htmlspecialchars($cur_value); ?>" />
				<?php } elseif (is_string($cur_var['value'])) { ?>
				<div class="pf-group pf-full-width">
					<div class="pf-field"><textarea rows="3" cols="35" class="" style="width: 100%;" name="opt_string_<?php echo htmlspecialchars($cur_var['name']); ?>"><?php echo htmlspecialchars($cur_value); ?></textarea></div>
				</div>
				<?php } else { ?>
				<div class="pf-group pf-full-width">
					<div class="pf-field"><textarea rows="3" cols="35" class="" style="width: 100%;" name="opt_serial_<?php echo htmlspecialchars($cur_var['name']); ?>"><?php echo htmlspecialchars(serialize($cur_value)); ?></textarea></div>
				</div>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="pf-group default" style="display: none;">
			<?php if (is_array($cur_var['value'])) {
				$output = array();
				foreach ($cur_var['value'] as $key => $cur_default)
					$output[] = '<div class="pf-field"><span class="label">'.htmlspecialchars(print_r(is_string($key) ? $key : $cur_default, true)).'</span></div>';
				echo implode('<span style="display: none;">, </span>', $output);
			} else {
				echo '<div class="pf-field"><span class="label">';
				if (is_bool($cur_var['value']))
					$cur_var['value'] = $cur_var['value'] ? 'Yes' : 'No';
				echo htmlspecialchars(print_r($cur_var['value'], true));
				echo '</span></div>';
			} ?>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element pf-buttons">
		<?php if ($this->entity->per_user) { ?>
		<input type="hidden" name="<?php echo $this->entity->user->is_com_configure_condition ? 'percondition' : 'peruser'; ?>" value="1" />
		<input type="hidden" name="type" value="<?php echo htmlspecialchars($this->entity->type); ?>" />
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->user->guid; ?>" />
		<?php } ?>
		<input type="hidden" name="component" value="<?php echo htmlspecialchars($this->entity->name); ?>" />
		<input class="pf-button btn btn-primary" type="submit" value="Save" name="save" />
		<input class="pf-button btn" type="reset" value="Reset" name="reset" onclick="window.setTimeout(function(){$('#p_muid_form input.default_checkbox').change()}, 1);" />
	</div>
</form>