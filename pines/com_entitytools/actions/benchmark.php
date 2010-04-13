<?php
/**
 * Provide a benchmark utility to test an entity manager's speed.
 *
 * @package Pines
 * @subpackage com_entitytools
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_entitytools/test') )
	punt_user('You don\'t have necessary permission.', pines_url('com_entitytools', 'benchmark'));

$module = new module('com_entitytools', 'benchmark', 'content');

?>