<?php
/**
 * Provide the HTML of a login page.
 *
 * @package Pines
 * @subpackage com_su
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() )
	punt_user('You don\'t have necessary permission.', pines_url());

$pines->page->override = true;

$login = $pines->user_manager->print_login();
$loginhtml = $login->render('module_head');
$pines->page->override_doc($loginhtml);

?>