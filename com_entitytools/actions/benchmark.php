<?php
/**
 * Provide a benchmark utility to test an entity manager's speed.
 *
 * @package Components\entitytools
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_entitytools/test') )
	punt_user(null, pines_url('com_entitytools', 'benchmark'));

$module = new module('com_entitytools', 'benchmark', 'content');

?>