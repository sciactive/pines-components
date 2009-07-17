<?php
/**
 * com_newsletter's configuration.
 *
 * @package XROOM
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

$config->com_newsletter = new DynamicConfig;

// This is the default "from" email.
$config->com_newsletter->default_from = "Nowhere <nowhere@example.com>";

// This is the "reply-to" email.
$config->com_newsletter->default_reply_to = "webmaster@example.com";

?>
