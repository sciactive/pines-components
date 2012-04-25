<?php
/**
 * Provide a form to edit a stock entry.
 *
 * @package Components
 * @subpackage sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_sales/managestock') )
		punt_user(null, pines_url('com_sales', 'stock/edit', array('id' => $_REQUEST['id'])));
} else {
	punt_user('No id specified.');
}
$list = explode(',', $_REQUEST['id']);

if (empty($list)) {
	pines_notice('No inventory specified!');
	return;
}

if (count($list) > 1) {
	$entity = com_sales_stock::factory();
	$module = $entity->print_form();
	unset($module->entity);
	$module->entities = array();
	foreach ($list as $cur_id) {
		$module->entities[] = com_sales_stock::factory((int) $cur_id);
	}
} else {
	$entity = com_sales_stock::factory((int) $list[0]);
	$entity->print_form();
}

?>