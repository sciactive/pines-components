<?php
/**
 * Load jQuery.
 *
 * @package Pines
 * @subpackage com_jquery
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$module = new module('com_jquery', 'jquery', 'head');
unset ($module);
$module = new module('com_jquery', 'jquery-ui', 'head');
unset ($module);

?>