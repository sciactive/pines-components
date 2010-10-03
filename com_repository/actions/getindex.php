<?php
/**
 * Get an index.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;

$publisher = $_REQUEST['pub'];

$user = user::factory($publisher);
if (!isset($user->guid))
	$user = null;

header('Content-Type: application/json');

$pines->page->override_doc($pines->com_repository->get_index($user, false));

?>