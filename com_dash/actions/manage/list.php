<?php
/**
 * Manage dashboards.
 *
 * @package Components
 * @subpackage dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_dash/manage') )
	punt_user(null, pines_url('com_dash', 'manage/list'));

$pines->com_dash->manage();

?>