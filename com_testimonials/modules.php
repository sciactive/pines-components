<?php
/**
 * com_testimonials' modules.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'testimonials' => array(
		'cname' => 'Testimonials',
		'description' => 'Testimonials approved for display.',
		'view' => 'modules/view_testimonials',
		'type' => 'module imodule',
	),
);

?>