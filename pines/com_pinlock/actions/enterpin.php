<?php
/**
 * Require the user to enter their PIN.
 *
 * @package Pines
 * @subpackage com_pinlock
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$module = new module('com_pinlock', 'enterpin', 'content');
$module->component = $pines->com_pinlock->component;
$module->action = $pines->com_pinlock->action;
$module->sessionid = $pines->com_pinlock->sessionid;

?>