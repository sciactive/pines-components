<?php
/**
 * tpl_mobile's configuration.
 *
 * @package Pines
 * @subpackage tpl_mobile
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

pines_session();

return array(
	array(
		'name' => 'use_header_image',
		'cname' => 'Use Header Image',
		'description' => 'Show a header image (instead of just text) at the top of the page.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'header_image',
		'cname' => 'Header Image',
		'description' => 'The header image to use.',
		'value' => (isset($_SESSION['user']->group) && is_callable(array($_SESSION['user']->group, 'get_logo'))) ? $_SESSION['user']->group->get_logo() : $pines->config->location.$pines->config->upload_location.'logos/default_logo.png',
		'peruser' => true,
	),
);

?>