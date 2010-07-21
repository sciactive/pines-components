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

$sale = $this->entity->has_tag('sale');
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$("#p_muid_email").click(function(){
			pines.get("<?php echo pines_url('com_sales', $sale ? 'sale/sendreceipt' : 'return/sendreceipt', array('id' => $this->entity->guid)); ?>");
		});
		$("#p_muid_print").click(function(){
			pines.get("<?php echo pines_url('com_sales', $sale ? 'sale/printreceipt' : 'return/printreceipt', array('id' => $this->entity->guid)); ?>");
		});
	});
	// ]]>
</script>
<div style="text-align: center;">
	<?php if (isset($this->entity->customer->email)) { ?>
	<button id="p_muid_email" class="ui-state-default ui-corner-all">Email to Customer</button>
	<br /><br />
	<?php } ?>
	<button id="p_muid_print" class="ui-state-default ui-corner-all">Receipt Printer</button>
</div>