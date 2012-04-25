<?php
/**
 * Provide the HTML of a login page.
 *
 * @package Components\su
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;

if ( !gatekeeper('com_su/switch') )
	return;

$login = new module('com_su', 'login');
$login->hide_password = gatekeeper('com_su/nopassword');
$login->pin_login = $pines->config->com_su->allow_pins;
$loginhtml = $login->render('module_head');
$pines->page->override_doc($loginhtml);

?>