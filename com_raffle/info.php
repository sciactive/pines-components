<?php
/**
 * com_raffle's information.
 *
 * @package Pines
 * @subpackage com_raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Raffles',
	'author' => 'SciActive',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'An raffle manager',
	'description' => 'Create raffles, enter contestants and pick a winner/winners at random.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager',
		'component' => 'com_jquery&com_bootstrap&com_pgrid&com_pform'
	),
	'abilities' => array(
		array('listraffles', 'List Raffles', 'User can see raffles.'),
		array('newraffle', 'Create Raffles', 'User can create new raffles.'),
		array('editraffle', 'Edit Raffles', 'User can edit current raffles.'),
		array('completeraffle', 'Complete Raffles', 'User can complete current raffles.'),
		array('deleteraffle', 'Delete Raffles', 'User can delete current raffles.')
	),
);

?>