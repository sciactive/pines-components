<?php
/**
 * The action to create a preview for testimonials
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_testimonials/help')) {
	punt_user(null, pines_url('', ''));
}

$pines->com_testimonials->print_preview();