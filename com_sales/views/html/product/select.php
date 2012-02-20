<?php
/**
 * A view to load the product selector.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_sales/includes/jquery.productselect.js");
	pines.com_sales_autoproduct_url = <?php echo json_encode(pines_url('com_sales', 'product/autocomplete')); ?>;
</script>