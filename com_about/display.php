<?php
/**
 * com_about's display control.
 *
 * @package XROOM
 * @subpackage com_about
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

if ( gatekeeper() ) $page->main_menu->add('About', $config->template->url('com_about'));

?>