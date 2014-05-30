<?php

/*
 * com_user's action to check if a user has verified their email yet.
 * 
 * @package Components\cache
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;

$result = ( !gatekeeper() || !$pines->config->com_user->confirm_email || !(isset($_SESSION['user']->secret) || isset($_SESSION['user']->new_email_secret)) ) ? false : true;

$pines->page->override_doc(json_encode($result));
?>