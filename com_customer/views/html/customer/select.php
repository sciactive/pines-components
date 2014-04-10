<?php
/**
 * A view to load the customer selector.
 *
 * @package Components\customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
    <?php if ($pines->config->com_customer->no_autocomplete) { ?>
            pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_customer/includes/<?php echo($pines->config->debug_mode ? 'jquery.customerselect.noauto.js' : 'jquery.customerselect.noauto.min.js') ?>");
        <?php } else { ?>
            pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_customer/includes/<?php echo($pines->config->debug_mode ? 'jquery.customerselect.js' : 'jquery.customerselect.min.js') ?>");
	<?php } ?>
        pines.com_customer_autocustomer_url = <?php echo json_encode(pines_url('com_customer', 'customer/search')); ?>;
</script>