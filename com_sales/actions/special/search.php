<?php
/**
 * Search specials, returning JSON.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/searchspecials'))
	punt_user(null, pines_url('com_sales', 'special/search', $_REQUEST));

$pines->page->override = true;
header('Content-Type: application/json');

$code = strtoupper($_REQUEST['code']);

if (empty($code)) {
	$special = null;
} else {
	$special = $pines->entity_manager->get_entity(
			array('class' => com_sales_special),
			array('&',
				'tag' => array('com_sales', 'special'),
				'data' => array('enabled', true),
				'strict' => array('code', $code)
			)
		);
	if (!isset($special->guid) || !$special->eligible())
		$special = null;
}

if (isset($special)) {
	$discounts = array();
	foreach ($special->discounts as $cur_discount) {
		if (isset($cur_discount['qualifier']))
			$discounts[] = array(
				'type' => $cur_discount['type'],
				'qualifier' => $cur_discount['qualifier']->guid,
				'value' => $cur_discount['value']
			);
		else
			$discounts[] = $cur_discount;
	}
	$requirements = array();
	foreach ($special->requirements as $cur_requirement) {
		if (is_object($cur_requirement['value']))
			$requirements[] = array(
				'type' => $cur_requirement['type'],
				'value' => $cur_requirement['value']->guid
			);
		else
			$requirements[] = $cur_requirement;
	}

	$json_struct = (object) array(
		'guid' => $special->guid,
		'name' => $special->name,
		'per_ticket' => $special->per_ticket,
		'before_tax' => $special->before_tax,
		'discounts' => $discounts,
		'requirements' => $requirements,
	);

	$special = $json_struct;
}

$pines->page->override_doc(json_encode($special));

?>