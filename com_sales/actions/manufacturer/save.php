<?php
/**
 * Save changes to a manufacturer.
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

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editmanufacturer') )
		punt_user(null, pines_url('com_sales', 'manufacturer/list'));
	$manufacturer = com_sales_manufacturer::factory((int) $_REQUEST['id']);
	if (!isset($manufacturer->guid)) {
		pines_error('Requested manufacturer id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newmanufacturer') )
		punt_user(null, pines_url('com_sales', 'manufacturer/list'));
	$manufacturer = com_sales_manufacturer::factory();
}

$manufacturer->name = $_REQUEST['name'];
$manufacturer->email = $_REQUEST['email'];
$manufacturer->address_1 = $_REQUEST['address_1'];
$manufacturer->address_2 = $_REQUEST['address_2'];
$manufacturer->city = $_REQUEST['city'];
$manufacturer->state = $_REQUEST['state'];
$manufacturer->zip = $_REQUEST['zip'];
$manufacturer->phone_work = preg_replace('/\D/', '', $_REQUEST['phone_work']);
$manufacturer->fax = preg_replace('/\D/', '', $_REQUEST['fax']);

if ($_REQUEST['remove_logo'] == 'ON' && isset($manufacturer->logo))
	unset($manufacturer->logo);

// Logo image.
if (!empty($_REQUEST['logo']) && $pines->uploader->check($_REQUEST['logo']))
	$manufacturer->logo = $_REQUEST['logo'];

if (empty($manufacturer->name)) {
	$manufacturer->print_form();
	pines_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_sales_manufacturer, 'skip_ac' => true), array('&', 'tag' => array('com_sales', 'manufacturer'), 'data' => array('name', $manufacturer->name)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$manufacturer->print_form();
	pines_notice('There is already a manufacturer with that name. Please choose a different name.');
	return;
}

if ($pines->config->com_sales->global_manufacturers)
	$manufacturer->ac->other = 1;

if ($manufacturer->save()) {
	pines_notice('Saved manufacturer ['.$manufacturer->name.']');
} else {
	pines_error('Error saving manufacturer. Do you have permission?');
}

pines_redirect(pines_url('com_sales', 'manufacturer/list'));

?>