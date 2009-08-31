<?php
/**
 * Display a list of configurable components.
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
<?php foreach($this->components as $cur_component) { ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $cur_component; ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if ($cur_component != 'system') { if (in_array($cur_component, $this->disabled_components)) { ?>
<input type="button" onclick="window.location='<?php echo $config->template->url('com_configure', 'enable', array('component' => urlencode($cur_component))); ?>';" value="Enable" />
<?php } else { ?>
<input type="button" onclick="window.location='<?php echo $config->template->url('com_configure', 'disable', array('component' => urlencode($cur_component))); ?>';" value="Disable" />
<?php } } ?>
<?php if (in_array($cur_component, $this->config_components)) { ?>
<input type="button" onclick="window.location='<?php echo $config->template->url('com_configure', 'edit', array('component' => urlencode($cur_component))); ?>';" value="Configure" />
<input type="button" onclick="window.location='<?php echo $config->template->url('com_configure', 'view', array('component' => urlencode($cur_component))); ?>';" value="View Config" />
<?php } ?>
<br /><br />
<?php } ?>