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
$this->show_title = false;
?>
<form class="pform" action="" method="post">
    <fieldset>
        <legend><?php echo $this->title; ?></legend>
        <?php foreach ($this->config as $cur_var) { ?>
        <div class="element" style="width: 95%">
            <label>
            <span class="label">
                <?php echo $cur_var['cname']; ?>
            </span>
            <span class="note">
                <?php print_r($cur_var['description']); ?>
            </span>
            <div class="group">
                <span class="field"><?php
                    if (is_bool($cur_var['value'])) $cur_var['value'] = ($cur_var['value']) ? 'Yes' : 'No';
                    echo htmlentities(print_r($cur_var['value'], true));
                ?></span>
            </div>
            </label>
        </div>
        <?php } ?>
    </fieldset>
</form>