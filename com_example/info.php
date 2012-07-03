<?php
/**
 * com_example's information.
 *
 * @package Components\example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Example Component',
	'author' => 'SciActive',
	'version' => '1.1.0beta2',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'An example component design',
	'description' => 'This component functions as an example of how to use various features of the Pines framework.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager&editor',
		'component' => 'com_jquery&com_bootstrap&com_pgrid&com_pform'
	),
	'abilities' => array(
		array('listfoobars', 'List Foobars', 'User can see foobars.'),
		array('newfoobar', 'Create Foobars', 'User can create new foobars.'),
		array('editfoobar', 'Edit Foobars', 'User can edit current foobars.'),
		array('deletefoobar', 'Delete Foobars', 'User can delete current foobars.'),
		array('content', 'Example Content', 'User can view example content.')
	),
);

?>