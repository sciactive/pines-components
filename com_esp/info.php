<?php
/**
 * com_esp's information.
 *
 * @package Components\esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Extended Service Plans',
	'author' => 'SciActive',
	'version' => '1.1.0beta2',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Extended service plan manager',
	'description' => 'Used to track and manage warranties on products.',
	'depend' => array(
		'pines' => '<3',
		'service' => 'entity_manager&icons',
		'component' => 'com_customer&com_jquery&com_bootstrap&com_pgrid&com_pnotify&com_pform&com_sales'
	),
	'abilities' => array(
		array('listplans', 'List ESPs', 'User can see ESPs.'),
		array('disposeplans', 'Dispose ESPs', 'User can dispose ESPs.'),
		array('filterplans', 'Filter ESPs', 'User can filter ESPs.'),
		array('editplan', 'Edit ESPs', 'User can edit ESPs.'),
		array('deleteplan', 'Delete ESPs', 'User can delete current ESPs.'),
		array('printplan', 'Print ESPs', 'User can print ESPs.'),
		array('claim', 'Claim ESPs', 'User can claim accidents for ESPs.')
	),
);

?>