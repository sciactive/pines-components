<?php
/**
 * Switch the template for mobile users.
 *
 * @package Components
 * @subpackage uasniffer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_uasniffer->mobile_site)
	return;

if ($pines->depend->check('browser', 'mobile-real') xor ($_COOKIE['com_uasniffer_switch'] == 'true')) {
	$pines->config->default_template = $pines->config->com_uasniffer->mobile_template;
	$pines->config->admin_template = $pines->config->com_uasniffer->mobile_template;
}

if ($pines->depend->check('browser', 'mobile-real') && $pines->config->com_uasniffer->switcher) {
	$module = new module('com_uasniffer', 'switcher', $pines->config->com_uasniffer->switcher_pos);
	unset($module);
}

?>