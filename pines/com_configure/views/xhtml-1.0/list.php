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
$this->title = 'Configure Components';
?>
<?php foreach($this->components as $cur_component) { ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $cur_component; ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if ($cur_component != 'system') { if (in_array($cur_component, $this->disabled_components)) { ?>
<input class="ui-state-default ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_configure', 'enable', array('component' => urlencode($cur_component))); ?>');" value="Enable" />
<?php } else { ?>
<input class="ui-state-default ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_configure', 'disable', array('component' => urlencode($cur_component))); ?>');" value="Disable" />
<?php } } ?>
<?php if (in_array($cur_component, $this->config_components)) { ?>
<input class="ui-state-default ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_configure', 'edit', array('component' => urlencode($cur_component))); ?>');" value="Configure" />
<input class="ui-state-default ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_configure', 'view', array('component' => urlencode($cur_component))); ?>');" value="View Config" />
<?php } ?>
<br /><br />
<?php } ?>