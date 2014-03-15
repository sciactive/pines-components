<?php
/**
 * A view to load the Oxygen icons.
 *
 * @package Components\oxygenicons
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_oxygenicons->use_icon_sprite) { ?>
<script type="text/javascript">
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_oxygenicons/includes/oxygen/icons-sprite.css");
</script>
<?php } else if ($pines->config->com_oxygenicons->use_icon_sprite_cdn) { ?>
<script type="text/javascript">
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_oxygenicons/includes/oxygen/icons-sprite-cdn.css");
</script>
<?php } else { ?>
<script type="text/javascript">
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_oxygenicons/includes/oxygen/icons.css");
</script>
<?php } ?>
