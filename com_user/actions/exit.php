<?php
/**
 * Exit out of the current page and display a notice.
 *
 * @package XROOM
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('X_RUN') or die('Direct access prohibited');

display_notice(stripslashes($_REQUEST['message']));
require('components/'.$config->default_component.'/actions/default.php');
?>