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
?>
<form action="<?php echo $config->template->url('com_configure', 'save'); ?>" name="configure" method="post">
<div class="config_list" style="border: 1px black solid"><?php foreach ($this->config as $cur_var) { ?>
    <div class="config_listing" style="padding: 0 3px; border-top: 1px black solid;">
        <div style="color: blue; font-weight: bold;">
            <?php echo $cur_var['cname']; ?>:
        </div>
        <div style="margin: 2px 10px;">
            <?php print_r($cur_var['description']); ?>
        </div>
        <div style="margin: 2px 10px;"><?php
            if (is_bool($cur_var['value'])) { ?>
                <label style="width: 100%;"><input type="checkbox" name="opt_bool_<?php echo $cur_var['name']; ?>" value="ON" <?php echo ($cur_var['value'] ? 'checked="checked" ' : ''); ?>/><?php echo htmlentities($cur_var['cname'], true); ?></label>
            <?php } elseif (is_int($cur_var['value'])) { ?>
                <input type="text" name="opt_int_<?php echo $cur_var['name']; ?>" value="<?php echo $cur_var['value']; ?>" />
            <?php } elseif (is_float($cur_var['value'])) { ?>
                <input type="text" name="opt_float_<?php echo $cur_var['name']; ?>" value="<?php echo $cur_var['value']; ?>" />
            <?php } elseif (is_string($cur_var['value'])) { ?>
                <textarea style="width: 100%;" name="opt_string_<?php echo $cur_var['name']; ?>"><?php echo htmlentities($cur_var['value'], true); ?></textarea>
            <?php } else { ?>
                <textarea style="width: 100%;" name="opt_serial_<?php echo $cur_var['name']; ?>"><?php echo htmlentities(serialize($cur_var['value']), true); ?></textarea>
            <?php }
        ?></div>
    </div>
<?php } ?></div>
<br />
<input type="hidden" name="component" value="<?php echo $this->req_component; ?>" />
<input type="submit" value="Save" name="save" />
<input type="reset" value="Reset" name="reset" />
</form>