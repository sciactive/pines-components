<?php
/**
 * Display a form to edit configuration settings.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Editing Configuration for {$this->comp->info->name} {$this->comp->info->version} ({$this->comp->name})";
?>
<style type="text/css">
	/* <![CDATA[ */
	#configuration_form .setting .ui-ptags {
		display: inline-block;
	}
	#configuration_form .default .field {
		display: inline-block;
		padding: .2em;
		margin-right: .3em;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		$("#configuration_form .do_tags").ptags({ptags_delimiter: ';;'});
		$("#configuration_form").delegate("input.default_checkbox", "change", function(){
			var checkbox = $(this);
			if (checkbox.attr("checked")) {
				checkbox.closest("div.element").children("div.default").hide().end().children("div.setting").show();
			} else {
				checkbox.closest("div.element").children("div.setting").hide().end().children("div.default").show();
			}
		}).find("input.default_checkbox").change();
	});
	// ]]>
</script>
<form id="configuration_form" class="pform" action="<?php echo pines_url('com_configure', 'save'); ?>" method="post">
	<div class="element heading">
		<p>Check a setting to set it manually, or leave it unchecked to use the default setting.</p>
	</div>
	<?php foreach ($this->comp->defaults as $cur_var) {
		if (key_exists($cur_var['name'], $this->comp->config_keys)) {
			$is_default = false;
			$cur_value = $this->comp->config_keys[$cur_var['name']];
		} else {
			$is_default = true;
			$cur_value = $cur_var['value'];
		} ?>
	<div class="element full_width">
		<label><span class="label"><input type="checkbox" class="default_checkbox ui-widget-content" name="manset_<?php echo $cur_var['name']; ?>" value="ON" <?php echo $is_default ? '' : 'checked="checked" '; ?>/> <?php echo $cur_var['cname']; ?></span></label>
		<span class="note"><?php print_r($cur_var['description']); ?></span>
		<div class="setting" style="display: none;">
			<?php if (is_array($cur_var['options'])) { ?>
				<?php foreach($cur_var['options'] as $key => $cur_option) {
					$display = is_string($key) ? $key : $cur_option; ?>
				<div class="group">
					<label><input class="field ui-widget-content" type="<?php echo is_array($cur_var['value']) ? 'checkbox' : 'radio'; ?>" name="opt_multi_<?php echo $cur_var['name']; ?><?php echo is_array($cur_var['value']) ? '[]' : ''; ?>" value="<?php echo addslashes(htmlentities(serialize($cur_option))); ?>" <?php echo ( (is_array($cur_value) && in_array($cur_option, $cur_value) || (!is_array($cur_value) && $cur_value == $cur_option)) ? 'checked="checked" ' : ''); ?>/> <?php echo htmlentities($display); ?></label><br />
				</div>
				<?php } ?>
			<?php } elseif (is_array($cur_var['value'])) { ?>
				<div class="group">
					<?php if (is_int($cur_var['value'][0])) { ?>
					<input class="field ui-widget-content do_tags" type="text" name="opt_int_<?php echo $cur_var['name']; ?>" value="<?php echo implode(';;', $cur_value); ?>" />
					<?php } elseif (is_float($cur_var['value'][0])) { ?>
					<input class="field ui-widget-content do_tags" type="text" name="opt_float_<?php echo $cur_var['name']; ?>" value="<?php echo implode(';;', $cur_value); ?>" />
					<?php } elseif (is_string($cur_var['value'][0])) { ?>
					<span class="field full_width"><textarea rows="3" cols="35" class="ui-widget-content do_tags" style="width: 100%;" name="opt_string_<?php echo $cur_var['name']; ?>"><?php echo htmlentities(implode(';;', $cur_value), true); ?></textarea></span>
					<?php } ?>
				</div>
			<?php } else { ?>
				<?php if (is_bool($cur_var['value'])) { ?>
				<input class="field ui-widget-content" type="checkbox" name="opt_bool_<?php echo $cur_var['name']; ?>" value="ON" <?php echo ($cur_value ? 'checked="checked" ' : ''); ?>/>
				<?php } elseif (is_int($cur_var['value'])) { ?>
				<input class="field ui-widget-content" type="text" name="opt_int_<?php echo $cur_var['name']; ?>" value="<?php echo $cur_value; ?>" />
				<?php } elseif (is_float($cur_var['value'])) { ?>
				<input class="field ui-widget-content" type="text" name="opt_float_<?php echo $cur_var['name']; ?>" value="<?php echo $cur_value; ?>" />
				<?php } elseif (is_string($cur_var['value'])) { ?>
				<span class="field full_width"><textarea rows="3" cols="35" class="ui-widget-content" style="width: 100%;" name="opt_string_<?php echo $cur_var['name']; ?>"><?php echo htmlentities($cur_value, true); ?></textarea></span>
				<?php } else { ?>
				<span class="field full_width"><textarea rows="3" cols="35" class="ui-widget-content" style="width: 100%;" name="opt_serial_<?php echo $cur_var['name']; ?>"><?php echo htmlentities(serialize($cur_value), true); ?></textarea></span>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="group default" style="display: none;">
			<?php if (is_array($cur_var['value'])) {
				foreach ($cur_var['value'] as $key => $cur_value) {
					echo '<div class="field ui-corner-all ui-state-default ui-state-disabled">'.htmlentities(print_r(is_string($key) ? $key : $cur_value, true)).'</div>';
				}
			} else {
				echo '<div class="field ui-corner-all ui-state-default ui-state-disabled">';
				if (is_bool($cur_var['value']))
					$cur_var['value'] = $cur_var['value'] ? 'Yes' : 'No';
				echo htmlentities(print_r($cur_var['value'], true));
				echo '</div>';
			} ?>
		</div>
	</div>
	<?php } ?>
	<div class="element buttons">
		<input type="hidden" name="component" value="<?php echo $this->comp->name; ?>" />
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Save" name="save" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="reset" value="Reset" name="reset" />
	</div>
</form>