<?php
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