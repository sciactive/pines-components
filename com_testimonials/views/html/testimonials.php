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

<link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($pines->config->location); ?>components/com_testimonials/includes/<?php echo ($pines->config->debug_mode) ? 'testimonial' : 'testimonial.min'; ?>.css">
<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->location); ?>components/com_testimonials/includes/<?php echo ($pines->config->debug_mode) ? 'testimonial' : 'testimonial.min'; ?>.js"></script>
<?php if ($pines->config->com_testimonials->scroll_load) { ?>
<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->location); ?>components/com_testimonials/includes/<?php echo ($pines->config->debug_mode) ? 'testimonial.scroll.load' : 'testimonial.scroll.load.min'; ?>.js"></script>
<?php } ?>
