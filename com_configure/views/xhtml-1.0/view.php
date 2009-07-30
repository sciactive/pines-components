<?php
/**
 * Display a list of configuration settings.
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
<div class="config_list" style="border: 1px black solid"><?php foreach ($this->config as $cur_var) { ?>
    <div class="config_listing" style="padding: 0 3px; border-top: 1px black solid;">
        <div style="color: blue; font-weight: bold;">
            <?php echo $cur_var['cname']; ?>:
        </div>
        <div style="margin: 2px 10px; padding: 1px; border: 1px gray solid;">
            <pre><?php
                if (is_bool($cur_var['value'])) $cur_var['value'] = ($cur_var['value']) ? 'Yes' : 'No';
                echo htmlentities(print_r($cur_var['value'], true));
            ?></pre>
        </div>
        <div style="margin: 2px 10px;">
            <?php print_r($cur_var['description']); ?>
        </div>
    </div>
<?php } ?></div>