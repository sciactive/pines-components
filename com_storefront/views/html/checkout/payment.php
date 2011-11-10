<?php
/**
 * Provides a form for payment info.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Payment Options';
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var buttons = $(":button, :submit, :reset", "#p_muid_form .pf-buttons").click(function(){
			buttons.attr("disabled", "disabled").addClass("ui-state-disabled");
		});
		$("#p_muid_submit").button()
	});
	// ]]>
</script>
<div class="pf-form">
	<?php if (!$this->review_form) { // The totals are already shown on the review part if the pages are combined. ?>
	<div class="pf-element">
		<span class="pf-label">Subtotal</span>
		<span class="pf-field">$<?php echo number_format($_SESSION['com_storefront_sale']->subtotal, 2); ?></span>
	</div>
	<?php if ($_SESSION['com_storefront_sale']->item_fees) { ?>
	<div class="pf-element">
		<span class="pf-label">Item Fees</span>
		<span class="pf-field">$<?php echo number_format($_SESSION['com_storefront_sale']->item_fees, 2); ?></span>
	</div>
	<?php } ?>
	<div class="pf-element">
		<span class="pf-label">Tax</span>
		<span class="pf-field">$<?php echo number_format($_SESSION['com_storefront_sale']->taxes, 2); ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Sale Total</span>
		<span class="pf-field">$<?php echo number_format($_SESSION['com_storefront_sale']->total, 2); ?></span>
	</div>
	<?php } if (count($this->payment_types) == 1) { $cur_payment_type = $this->payment_types[0]; ?>
	<div class="pf-element pf-heading">
		<h1><?php echo htmlspecialchars($cur_payment_type->name); ?></h1>
	</div>
	<?php if (!empty($this->payment)) { ?>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			var form = $("#p_muid_form");
			var data = JSON.parse("<?php echo addslashes(json_encode($this->payment->data)); ?>");
			if (data) {
				$.each(data, function(i, val){
					form.find(":input:not(:radio, :checkbox)[name="+i+"]").val(val);
					form.find(":input:radio[name="+i+"][value="+val+"]").attr("checked", "checked");
					if (val == "")
						form.find(":input:checkbox[name="+i+"]").removeAttr("checked");
					else
						form.find(":input:checkbox[name="+i+"][value="+val+"]").attr("checked", "checked");
				});
			}
		});
		// ]]>
	</script>
	<?php } ?>
	<form id="p_muid_form" method="POST" action="<?php echo htmlspecialchars(pines_url('com_storefront', 'checkout/paymentsave')); ?>">
		<br class="pf-clearing" />
		<?php
		$pines->com_sales->call_payment_process(array(
			'action' => 'request_cust',
			'name' => $cur_payment_type->processing_type,
			'ticket' => $_SESSION['com_storefront_sale']
		), $module);

		if (isset($module))
			echo $module->render();
		?>
		<?php if ($this->review_form) { ?>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Order Comments</span>
			<textarea class="pf-field ui-widget-content ui-corner-all" rows="1" cols="35" name="comments"><?php echo htmlspecialchars($this->entity->comments); ?></textarea>
		</div>
		<?php } ?>
		<div class="pf-element pf-buttons">
			<input type="hidden" name="com_storefront_payment_id" value="<?php echo (int) $cur_payment_type->guid ?>" />
			<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" id="p_muid_submit" type="submit" value="<?php echo $this->review_form ? htmlspecialchars($pines->config->com_storefront->complete_order_text) : 'Continue'; ?>" />
		</div>
	</form>
	<?php } else { ?>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			var get_form = function(payment_data){
				$.ajax({
					url: "<?php echo addslashes(pines_url('com_storefront', 'checkout/paymentform')); ?>",
					type: "POST",
					dataType: "html",
					data: {"name": payment_data.processing_type},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to retrieve the data form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (data == null)
							return;
						$("#p_muid_payment_form").slideUp("fast", function(){
							var form = $(this).html(data);
							if (payment_data.data) {
								$.each(payment_data.data, function(i, val){
									form.find(":input:not(:radio, :checkbox)[name="+i+"]").val(val);
									form.find(":input:radio[name="+i+"][value="+val+"]").attr("checked", "checked");
									if (val == "")
										form.find(":input:checkbox[name="+i+"]").removeAttr("checked");
									else
										form.find(":input:checkbox[name="+i+"][value="+val+"]").attr("checked", "checked");
								});
							}
							form.slideDown("fast");
						});
					}
				});
			};

			$("#p_muid_payment_types").delegate("input[name=payment_type]", "change", function(){
				var radio = $(this);
				var payment = JSON.parse(radio.val());
				if (radio.attr("checked"))
					get_form(payment);
				$("input[name=com_storefront_payment_id]", "#p_muid_form").val(payment.guid);
			});
			$("input:checked[name=payment_type]", "#p_muid_payment_types").change();
		});
		// ]]>
	</script>
	<div class="pf-element" id="p_muid_payment_types">
		<?php foreach ($this->payment_types as $cur_payment_type) { ?>
		<label><input type="radio" name="payment_type" value="<?php echo htmlspecialchars(json_encode(array('guid' => $cur_payment_type->guid, 'processing_type' => $cur_payment_type->processing_type, 'name' => $cur_payment_type->name, 'data' => $cur_payment_type->is($this->payment->entity) ? $this->payment->data : null))); ?>"<?php echo $cur_payment_type->is($this->payment->entity) ? ' checked="checked"' : ''; ?> /> <?php echo htmlspecialchars($cur_payment_type->name); ?></label>
		<?php } ?>
	</div>
	<form id="p_muid_form" method="POST" action="<?php echo htmlspecialchars(pines_url('com_storefront', 'checkout/paymentsave')); ?>">
		<br class="pf-clearing" />
		<div id="p_muid_payment_form"></div>
		<?php if ($this->review_form) { ?>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Order Comments</span>
			<textarea class="pf-field ui-widget-content ui-corner-all" rows="1" cols="35" name="comments"><?php echo htmlspecialchars($this->entity->comments); ?></textarea>
		</div>
		<?php } ?>
		<div class="pf-element pf-buttons">
			<input type="hidden" name="com_storefront_payment_id" value="" />
			<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" id="p_muid_submit" type="submit" value="<?php echo $this->review_form ? htmlspecialchars($pines->config->com_storefront->complete_order_text) : 'Continue'; ?>" />
		</div>
	</form>
	<?php } ?>
</div>