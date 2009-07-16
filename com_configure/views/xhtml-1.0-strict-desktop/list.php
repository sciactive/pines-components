<?php
/**
 * Display a list of configuration file locations by component.
 *
 * @package Dandelion
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');
?>
<table width="100%">
    <caption>Config Files</caption>
    <thead><tr><th>Component</th><th>Config File Location</th></tr></thead>
    <tbody>
    <?php foreach ($config->configurator->config_files as $cur_component => $cur_location) { ?>
    <tr><td><?php echo $cur_component; ?></td><td><?php echo $cur_location; ?></td></tr>
    <?php } ?>
    </tbody>
</table>