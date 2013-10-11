<?php
/**
 * The view to load into the head section to attach css and javascript for a testimonial module.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

?>

<link rel="stylesheet" type="text/css" href="<?php echo pines_url('com_testimonials', 'testimonial_css'); ?>">
<script type="text/javascript" src="<?php echo pines_url('com_testimonials', 'testimonial_js');?>"></script>
