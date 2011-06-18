<?php
/**
 * com_esp's configuration defaults.
 *
 * @package Pines
 * @subpackage com_esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'esp_company',
		'cname' => 'ESP Name',
		'description' => 'The name of the Extended Service Plan',
		'value' => 'Generic Protection',
		'peruser' => true,
	),
	array(
		'name' => 'esp_product',
		'cname' => 'ESP Product',
		'description' => 'The guid of the Extended Service Plan product entity',
		'value' => 6139,
		'peruser' => true,
	),
	array(
		'name' => 'esp_rate',
		'cname' => 'ESP Price Rate',
		'description' => 'The price rate to charge for Extended Service Plans',
		'value' => 0.15,
		'peruser' => true,
	),
	array(
		'name' => 'esp_term',
		'cname' => 'ESP Coverage Term',
		'description' => 'The length of time that Extended Service Plans provide coverage for.',
		'value' => 2,
		'peruser' => true,
	),
	array(
		'name' => 'disposal_types',
		'cname' => 'Disposal Types',
		'description' => 'Uses this format: code:Name of Disposition.',
		'value' => array(
			'pending:Pending',
			'registered:Registered',
			'voided:Voided'
		),
		'peruser' => true,
	),
);

?>