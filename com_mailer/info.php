<?php
/**
 * com_mailer's information.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Mailer',
	'author' => 'SciActive',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Email interface',
	'description' => 'Provides a more object oriented interface for creating emails in Pines. Supports attachments.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager&editor',
		'component' => 'com_jquery&com_bootstrap&com_pgrid&com_pform'
	),
	'abilities' => array(
		array('listtemplates', 'List Templates', 'User can see templates.'),
		array('newtemplate', 'Create Templates', 'User can create new templates.'),
		array('edittemplate', 'Edit Templates', 'User can edit current templates.'),
		array('deletetemplate', 'Delete Templates', 'User can delete current templates.'),
	),
);

?>