<?php
/**
 * com_uasniffer's modules.
 *
 * @package Components\uasniffer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'switcher' => array(
		'cname' => 'Mobile/Desktop Site Switcher',
		'description' => 'Switch between the mobile and desktop version of the site.',
		'view' => 'switcher',
		'form' => 'switcher_form',
		'type' => 'module imodule',
	),
);

?>