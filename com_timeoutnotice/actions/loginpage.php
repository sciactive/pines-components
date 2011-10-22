<?php
/**
 * Provide the HTML of a login page.
 *
 * @package Pines
 * @subpackage com_timeoutnotice
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;

$login = new module('com_timeoutnotice', 'login');
$loginhtml = $login->render('module_head');
$pines->page->override_doc($loginhtml);

?>