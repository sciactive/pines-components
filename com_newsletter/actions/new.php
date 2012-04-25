<?php
/**
 * Create a newsletter.
 *
 * @package Components\newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/listmail') )
	punt_user(null, pines_url('com_newsletter', 'list'));

$pines->com_newsletter->edit_mail(null, 'com_newsletter', 'edit');
?>