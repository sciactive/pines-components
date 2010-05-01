<?php
/**
 * Provide a form to make a package.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_packager/makepackage') )
	punt_user('You don\'t have necessary permission.', pines_url('com_packager', 'makepackage', array('id' => $_REQUEST['id'])));

$entity = com_packager_package::factory((int) $_REQUEST['id']);

if (!isset($entity->guid)) {
	pines_error('Requested package id is not accessible.');
	return;
}

$name = $entity->get_filename();
$module = new module('com_packager', 'result_package', 'content');
$module->entity = $entity;
$module->filename = "{$name}.slm";
$module->path = "{$pines->config->setting_upload}/packages/{$module->filename}";
$module->result = $entity->package($module->path);

?>