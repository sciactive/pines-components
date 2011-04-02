<?php
/**
 * List repositories.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_plaza/editrepositories') )
	punt_user(null, pines_url('com_plaza', 'repository/list'));

$pines->com_plaza->list_repositories();

?>