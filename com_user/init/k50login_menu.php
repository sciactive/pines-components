<?php
/**
 * Add the login menu entry.
 *
 * @package Components
 * @subpackage user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_user->login_menu || gatekeeper())
	return;

$pines->menu->menu_arrays[] = array(
	'path' => $pines->config->com_user->login_menu_path,
	'text' => $pines->config->com_user->login_menu_text,
	'href' => array('com_user')
);

?>