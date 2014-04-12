<?php
/**
 * A view to load the product selector.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
<?php if ($pines->config->com_sales->no_autocomplete_product) { ?>
pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_sales/includes/<?php echo ($pines->config->debug_mode ? 'jquery.productselect.noauto.js' : 'jquery.productselect.noauto.min.js'); ?>");
<?php } else { ?>
pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_sales/includes/<?php echo ($pines->config->debug_mode ? 'jquery.productselect.js' : 'jquery.productselect.min.js'); ?>");
<?php } ?>
pines.com_sales_autoproduct_url = <?php echo json_encode(pines_url('com_sales', 'product/autocomplete')); ?>;
</script>