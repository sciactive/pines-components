<?php
/**
 * Display a form to edit configuration settings.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Editing Configuration for {$this->req_component}";
?>
<form class="pform" action="<?php echo pines_url('com_configure', 'save'); ?>" name="configure" method="post">
	<?php foreach ($this->config as $cur_var) { ?>
	<div class="element" style="width: 95%;">
	<span class="label">
		<?php echo $cur_var['cname']; ?>
	</span>
	<span class="note">
		<?php print_r($cur_var['description']); ?>
	</span>
	<div class="group">
		<?php if (is_bool($cur_var['value'])) { ?>
		<input class="field" type="checkbox" name="opt_bool_<?php echo $cur_var['name']; ?>" value="ON" <?php echo ($cur_var['value'] ? 'checked="checked" ' : ''); ?>/>
		<?php } elseif (is_int($cur_var['value'])) { ?>
		<input class="field" type="text" name="opt_int_<?php echo $cur_var['name']; ?>" value="<?php echo $cur_var['value']; ?>" />
		<?php } elseif (is_float($cur_var['value'])) { ?>
		<input class="field" type="text" name="opt_float_<?php echo $cur_var['name']; ?>" value="<?php echo $cur_var['value']; ?>" />
		<?php } elseif (is_string($cur_var['value'])) { ?>
		<textarea class="field" style="width: 100%;" name="opt_string_<?php echo $cur_var['name']; ?>"><?php echo htmlentities($cur_var['value'], true); ?></textarea>
		<?php } else { ?>
		<textarea class="field" style="width: 100%;" name="opt_serial_<?php echo $cur_var['name']; ?>"><?php echo htmlentities(serialize($cur_var['value']), true); ?></textarea>
		<?php } ?>
	</div>
	</div>
	<?php } ?>
	<div class="element buttons">
	<input type="hidden" name="component" value="<?php echo $this->req_component; ?>" />
	<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Save" name="save" />
	<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="reset" value="Reset" name="reset" />
	</div>
</form>