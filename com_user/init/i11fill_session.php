<?php
/**
 * Update the session variable.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

pines_session();
if ( isset($_SESSION['user_id']) )
	$pines->user_manager->fill_session();

?>