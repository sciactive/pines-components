<?php
/**
 * Print a form to add a new repository.
 *
 * @package Components\plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_plaza/editrepositories') )
	punt_user(null, pines_url('com_plaza', 'repository/new'));

$module = new module('com_plaza', 'repository/new', 'content');

?>