<?php
/**
 * Provides actions to perform with a receipt.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Receipt Actions';

if (!isset($this->entity->customer->email) && !$pines->config->com_sales->receipt_printer) {
	$this->detach();
	return;
}

$sale = $this->entity->has_tag('sale');
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$("#p_muid_email").click(function(){
			pines.get("<?php echo pines_url('com_sales', $sale ? 'sale/sendreceipt' : 'return/sendreceipt', array('id' => $this->entity->guid)); ?>");
		});
		<?php if ($pines->config->com_sales->receipt_printer) { ?>
		$("#p_muid_print").click(function(){
			// Use window.location so an AJAX wrapper won't try to wrap this.
			window.location = "<?php echo pines_url('com_sales', $sale ? 'sale/printreceipt' : 'return/printreceipt', array('id' => $this->entity->guid)); ?>";
		});
		<?php if ($pines->config->com_sales->auto_receipt_printer && $this->auto_print_ok) { ?>
		$("#p_muid_print").click();
		<?php } } ?>
	});
	// ]]>
</script>
<div style="text-align: center;">
	<?php if (isset($this->entity->customer->email)) { ?>
	<button id="p_muid_email" class="ui-state-default ui-corner-all">Email Customer</button>
	<br /><br />
	<?php } if ($pines->config->com_sales->receipt_printer) { ?>
	<button id="p_muid_print" class="ui-state-default ui-corner-all">Receipt Printer</button>
	<?php } ?>
</div>