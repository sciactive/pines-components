<?php
/**
 * Determine if a username is available.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_user->check_username)
	throw new HttpClientException(null, 404);

$pines->page->override = true;
header('Content-Type: application/json');

if (!empty($_REQUEST['id']))
	$id = intval($_REQUEST['id']);

$pines->page->override_doc(json_encode($pines->user_manager->check_username($_REQUEST['username'], $id)));

?>