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
	'version' => '1.1.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Email interface',
	'description' => 'Provides a more object oriented interface for creating emails in Pines. Supports attachments.',
	'depend' => array(
		'pines' => '<3',
		'service' => 'entity_manager&editor',
		'component' => 'com_jquery&com_bootstrap&com_pgrid&com_markdown&com_pform'
	),
	'recommend' => array(
		'class' => 'SQLite3'
	),
	'abilities' => array(
		array('listrenditions', 'List Renditions', 'User can see renditions.'),
		array('newrendition', 'Create Renditions', 'User can create new renditions.'),
		array('editrendition', 'Edit Renditions', 'User can edit current renditions.'),
		array('deleterendition', 'Delete Renditions', 'User can delete current renditions.'),
		array('sendtemplateemail', 'Send Email Textual Templates', 'User can send pre-written email templates on certain grids.'),
		array('editsendtemplateemail', 'Edit Email Prefix on Templates', 'User edit their sending mail address prefix (not the domain).'),
		array('listtemplates', 'List Design Templates', 'User can see templates.'),
		array('newtemplate', 'Create Design Templates', 'User can create new templates.'),
		array('edittemplate', 'Edit Design Templates', 'User can edit current templates.'),
		array('deletetemplate', 'Delete Design Templates', 'User can delete current templates.'),
	),
);

?>