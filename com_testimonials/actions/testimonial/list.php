<?php
/**
 * List testimonials.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_testimonials/listtestimonials') )
	punt_user(null, pines_url('com_testimonials', 'testimonial/list'));

switch ($_REQUEST['type']) {
	case 'denied':
	case 'pending':
	case 'approved':
		break;
	default: 
		$default = true;
}

if (!$default)
	$pines->com_testimonials->list_testimonials($_REQUEST['type']);
else
	$pines->com_testimonials->list_testimonials();
?>