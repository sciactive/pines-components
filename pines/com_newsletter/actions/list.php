<?php
/**
 * List all the newsletters.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/listmail') && !gatekeeper('com_newsletter/send') )
	punt_user('You don\'t have necessary permission.', pines_url('com_newsletter', 'list', null, false));

$pines->com_newsletter->list_mails();
?>