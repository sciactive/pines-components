<?php
/**
 * com_configure's information.
 *
 * @package Components\configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'System Configurator',
	'author' => 'SciActive',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('configurator'),
	'short_description' => 'Manages system configuration',
	'description' => 'Allows you to edit your system\'s configuration and the configuration of any installed components.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery&com_bootstrap&com_ptags&com_pgrid&com_pform'
	),
	'recommend' => array(
		'service' => 'entity_manager&user_manager'
	),
	'abilities' => array(
		array('edit', 'Edit Configuration', 'Let the user change (and see) configuration settings.'),
		array('editperuser', 'Edit Per User Configuration', 'Let the user change (and see) per user/group configuration settings.'),
		array('view', 'View Configuration', 'Let the user see current configuration settings.'),
		array('viewperuser', 'View Per User Configuration', 'Let the user see current per user/group configuration settings.')
	),
);

?>