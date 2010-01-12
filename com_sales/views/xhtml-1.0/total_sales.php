<?php
/**
 * Provides a form for viewing sales totals.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sales Totals';
?>
<div id="sales_totals" class="pform">
	<div class="element heading">
		<script type="text/javascript">
			// <![CDATA[
			$(function(){
				$("#hide_parameters").click(function(){
					$("#sales_totals").children(".element:not(.heading)").slideToggle();
				});
			});
			// ]]>
		</script>
		<button id="hide_parameters" class="ui-state-default ui-corner-all" style="float: right;">Toggle Form</button>
		<h1>Parameters</h1>
	</div>
	<div class="element">
		<label><span class="label">Location</span>
			<select class="field" id="location" name="location">
				<option value="current">-- Current --</option>
				<?php if ($this->show_all) { ?>
				<option value="all">-- All --</option>
				<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->locations); ?>
				<?php } ?>
			</select></label>
	</div>
	<div class="element">
		<script type="text/javascript">
			// <![CDATA[
			$(function(){
				$("#date_start").datepicker({
					dateFormat: "yy-mm-dd",
					changeMonth: true,
					changeYear: true
				});
			});
			// ]]>
		</script>
		<label><span class="label">Start Date</span>
			<input class="field" type="text" id="date_start" name="date_start" size="24" value="<?php echo date('Y-m-d'); ?>" /></label>
	</div>
	<div class="element">
		<script type="text/javascript">
			// <![CDATA[
			$(function(){
				$("#date_end").datepicker({
					dateFormat: "yy-mm-dd",
					changeMonth: true,
					changeYear: true
				});
			});
			// ]]>
		</script>
		<label><span class="label">End Date</span>
			<input class="field" type="text" id="date_end" name="date_end" size="24" value="<?php echo date('Y-m-d'); ?>" /></label>
	</div>
	<div class="element buttons">
		<script type="text/javascript">
			// <![CDATA[
			var com_sales_location;
			var com_sales_date_start;
			var com_sales_date_end;
			var com_sales_result_totals;

			$(function(){
				com_sales_location = $("#location");
				com_sales_date_start = $("#date_start");
				com_sales_date_end = $("#date_end");
				com_sales_result_totals = $("#result_totals");

				$("#retrieve_totals").click(function(){
					var loader;
					$.ajax({
						url: "<?php echo pines_url('com_sales', 'totaljson'); ?>",
						type: "POST",
						dataType: "json",
						data: {"location": com_sales_location.val(), "date_start": com_sales_date_start.val(), "date_end": com_sales_date_end.val()},
						beforeSend: function(){
							com_sales_result_totals.hide("normal");
							loader = pines.alert('Retrieving totals from server...', 'Sales Totals', 'icon picon_16x16_animations_throbber', {pnotify_hide: false, pnotify_history: false});
						},
						complete: function(){
							loader.pnotify_remove();
						},
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured while trying to retrieve totals:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (!data) {
								alert("No sales data was returned.");
								return;
							}
							com_sales_result_totals.find(".total_location").html(data.location);
							com_sales_result_totals.find(".total_date").html(data.date_start == data.date_end ? data.date_start : data.date_start+" - "+data.date_end);
							com_sales_result_totals.find(".total_invoice_count").html(data.invoice.count);
							com_sales_result_totals.find(".total_invoice_total").html(data.invoice.total);
							com_sales_result_totals.find(".total_sale_count").html(data.sale.count);
							com_sales_result_totals.find(".total_sale_total").html(data.sale.total);
							com_sales_result_totals.find(".total_users").empty().each(function(){
								var total_users = $(this);
								$.each(data.user, function(i, val){
									total_users.append("<div class=\"element\"><span class=\"label\">"+i+"</span><div class=\"group\"><span class=\"field\">Count: </span><span class=\"field\">"+val.count+"</span><br /><span class=\"field\">Total: </span><span class=\"field\">$"+val.total+"</span></div></div>");
								});
							});
							com_sales_result_totals.find(".total_payments").empty().each(function(){
								var total_payments = $(this);
								$.each(data.payment, function(i, val){
									total_payments.append("<div class=\"element\"><span class=\"label\">"+i+"</span><div class=\"group\"><span class=\"field\">Count: </span><span class=\"field\">"+val.count+"</span><br /><span class=\"field\">Total: </span><span class=\"field\">$"+val.total+"</span>"+(val.change_given ? "<br /><span class=\"field\">Change Given: </span><span class=\"field\">$"+val.change_given+"</span><br /><span class=\"field\">Net Total: </span><span class=\"field\">$"+val.net_total+"</span>" : "")+"</div></div>");
								});
							});
							com_sales_result_totals.show("normal");
						}
					});
				});
			});
			// ]]>
		</script>
		<button id="retrieve_totals" class="button ui-state-default ui-corner-all">Retrieve</button>
	</div>
	<div id="result_totals" style="clear: both; display: none;">
		<div class="element">
			<span class="label">Location</span>
			<span class="field"><span class="total_location">null</span></span>
		</div>
		<div class="element">
			<span class="label">Date</span>
			<span class="field"><span class="total_date">null</span></span>
		</div>
		<div class="element heading">
			<h1>Invoices</h1>
		</div>
		<div class="element">
			<span class="label">Count</span>
			<span class="field"><span class="total_invoice_count">null</span></span>
		</div>
		<div class="element">
			<span class="label">Total</span>
			<span class="field">$<span class="total_invoice_total">null</span></span>
		</div>
		<div class="element heading">
			<h1>Sales</h1>
		</div>
		<div class="element">
			<span class="label">Count</span>
			<span class="field"><span class="total_sale_count">null</span></span>
		</div>
		<div class="element">
			<span class="label">Total</span>
			<span class="field">$<span class="total_sale_total">null</span></span>
		</div>
		<div class="element heading">
			<h1>Users</h1>
		</div>
		<div class="total_users">
			<div class="element">
				<span class="label">1: name [name]</span>
				<div class="group">
					<span class="field">Count: </span>
					<span class="field">1</span><br />
					<span class="field">Total: </span>
					<span class="field">1.00</span>
				</div>
			</div>
		</div>
		<div class="element heading">
			<h1>Payments</h1>
		</div>
		<div class="total_payments">
			<div class="element">
				<span class="label">name</span>
				<div class="group">
					<span class="field">Count: </span>
					<span class="field">1</span><br />
					<span class="field">Total: </span>
					<span class="field">1.00</span><br />
					<span class="field">Change Given: </span>
					<span class="field">1.00</span>
				</div>
			</div>
		</div>
	</div>
</div>