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
<?php foreach($this->components as $component) { ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $component; ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="button" onclick="window.location='<?php echo $config->template->url('com_configure', 'edit', array('component' => urlencode($component))); ?>';" value="Edit" />
<input type="button" onclick="window.location='<?php echo $config->template->url('com_configure', 'view', array('component' => urlencode($component))); ?>';" value="View" />
<br /><br />
<?php } ?>