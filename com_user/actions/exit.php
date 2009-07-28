<?php
/**
 * Exit out of the current page and display a notice.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

display_notice(stripslashes($_REQUEST['message']));
/**
 * Load the default component.
 */
require('components/'.$config->default_component.'/actions/default.php');
?>