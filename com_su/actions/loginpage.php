<?php
/**
 * Provide the HTML of a login page.
 *
 * @package Pines
 * @subpackage com_su
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() )
	punt_user(null, pines_url());

$pines->page->override = true;

$login = new module('com_su', 'login');
$login->hide_password = gatekeeper('com_su/nopassword');
$login->pin_login = $pines->config->com_su->allow_pins;
$loginhtml = $login->render('module_head');
$pines->page->override_doc($loginhtml);

?>