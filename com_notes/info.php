<?php
/**
 * com_notes' information.
 *
 * @package Components\notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Note System for Entities',
	'author' => 'SciActive',
	'version' => '1.1.0beta2',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'A global note system for threaded notes on entities',
	'description' => 'A note system which allows note threads to be added to most entities. A thread is a collection of related notes.',
	'depend' => array(
		'pines' => '>=1.0.2&<2',
		'service' => 'entity_manager&icons',
		'component' => 'com_jquery&com_bootstrap&com_pgrid&com_pform'
	),
	'abilities' => array(
		array('listthreads', 'List Threads', 'User can see all saved threads.'),
		array('seethreads', 'See Threads', 'User can view the threads on an entity. (As opposed to List Threads, which shows all threads on all entities.)'),
		array('newthread', 'Create Threads', 'User can create new threads.'),
		array('continueownthread', 'Continue Own Threads', 'User can continue (comment on) their own threads.'),
		array('continuethread', 'Continue Threads', 'User can continue (comment on) any threads.'),
		array('editthread', 'Edit Threads', 'User can edit contents of current threads.'),
		array('deletethread', 'Delete Threads', 'User can delete current threads.')
	),
);

?>