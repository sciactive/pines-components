<?php
/**
 * Delete a company.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/deletecompany') )
	punt_user(null, pines_url('com_customer', 'company/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_company) {
	$cur_entity = com_customer_company::factory((int) $cur_company);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_company;
}
if (empty($failed_deletes)) {
	pines_notice('Selected company(ies) deleted successfully.');
} else {
	pines_error('Could not delete companies with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_customer', 'company/list'));

?>