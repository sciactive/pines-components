<?php
/**
 * Provides a form for viewing sales totals.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sales Total Report';
?>
<div id="p_muid_totals" class="pf-form">
	<div class="pf-element pf-heading">
		<h1>Parameters</h1>
	</div>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$("#p_muid_hide_parameters").click(function(){
				$("#p_muid_totals > .pf-element:not(.pf-heading)").slideToggle();
			});
		});
		// ]]>
	</script>
	<button id="p_muid_hide_parameters" class="ui-state-default ui-corner-all" style="float: right;">Toggle Form</button>
	<div class="pf-element">
		<label><span class="pf-label">Location</span>
			<select class="pf-field ui-widget-content" id="p_muid_location" name="location">
				<option value="current">-- Current --</option>
				<?php if ($this->show_all) { ?>
				<option value="all">-- All --</option>
				<?php
				$pines->user_manager->group_sort($this->locations, 'name');
				foreach ($this->locations as $cur_group) {
					?><option value="<?php echo $cur_group->guid; ?>"><?php echo str_repeat('->', $cur_group->get_level())." {$cur_group->name} [{$cur_group->groupname}]"; ?></option><?php
				} ?>
				<?php } ?>
			</select></label>
	</div>
	<div class="pf-element">
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				$("#p_muid_date_start").datepicker({
					dateFormat: "yy-mm-dd",
					changeMonth: true,
					changeYear: true,
					showOtherMonths: true,
					selectOtherMonths: true
				});
			});
			// ]]>
		</script>
		<label><span class="pf-label">Start Date</span>
			<input class="pf-field ui-widget-content" type="text" id="p_muid_date_start" name="date_start" size="24" value="<?php echo date('Y-m-d'); ?>" /></label>
	</div>
	<div class="pf-element">
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				$("#p_muid_date_end").datepicker({
					dateFormat: "yy-mm-dd",
					changeMonth: true,
					changeYear: true,
					showOtherMonths: true,
					selectOtherMonths: true
				});
			});
			// ]]>
		</script>
		<label><span class="pf-label">End Date</span>
			<input class="pf-field ui-widget-content" type="text" id="p_muid_date_end" name="date_end" size="24" value="<?php echo date('Y-m-d'); ?>" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				var location = $("#p_muid_location");
				var date_start = $("#p_muid_date_start");
				var date_end = $("#p_muid_date_end");
				var result_totals = $("#p_muid_result_totals");

				$("#p_muid_retrieve_totals").click(function(){
					var loader;
					$.ajax({
						url: "<?php echo pines_url('com_sales', 'sale/totalsjson'); ?>",
						type: "POST",
						dataType: "json",
						data: {"location": location.val(), "date_start": date_start.val(), "date_end": date_end.val()},
						beforeSend: function(){
							result_totals.hide("normal");
							loader = $.pnotify({
								pnotify_title: 'Sales Totals',
								pnotify_text: 'Retrieving totals from server...',
								pnotify_notice_icon: 'picon picon-throbber',
								pnotify_nonblock: true,
								pnotify_hide: false,
								pnotify_history: false
							});
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
							$("#p_muid_total_location").html(data.location);
							$("#p_muid_total_date").html(data.date_start == data.date_end ? data.date_start : data.date_start+" - "+data.date_end);

							// Invoices
							$("#p_muid_total_invoice_count").html(data.invoices.count);
							$("#p_muid_total_invoice_total").html(data.invoices.total);

							// Sales
							$("#p_muid_total_sale_count").html(data.sales.count);
							$("#p_muid_total_sale_total").html(data.sales.total);
							$("#p_muid_total_sale_users").empty().each(function(){
								var total_users = $(this);
								$.each(data.sales_user, function(i, val){
									total_users.append("<div class=\"pf-element\"><span class=\"pf-label\">"+i+"</span><div class=\"pf-group\"><span class=\"pf-field\">Count: </span><span class=\"pf-field\">"+val.count+"</span><br /><span class=\"pf-field\">Total: </span><span class=\"pf-field\">$"+val.total+"</span></div></div>");
								});
							});
							$("#p_muid_total_sales_payments").empty().each(function(){
								var total_payments = $(this);
								$.each(data.payments, function(i, val){
									total_payments.append("<div class=\"pf-element\"><span class=\"pf-label\">"+i+"</span><div class=\"pf-group\"><span class=\"pf-field\">Count: </span><span class=\"pf-field\">"+val.count+"</span><br /><span class=\"pf-field\">Total: </span><span class=\"pf-field\">$"+val.total+"</span>"+(val.change_given ? "<br /><span class=\"pf-field\">Change Given: </span><span class=\"pf-field\">$"+val.change_given+"</span><br /><span class=\"pf-field\">Net Total: </span><span class=\"pf-field\">$"+val.net_total+"</span>" : "")+"</div></div>");
								});
							});

							// Returns
							$("#p_muid_total_return_count").html(data.returns.count);
							$("#p_muid_total_return_total").html(data.returns.total);
							$("#p_muid_total_return_users").empty().each(function(){
								var total_users = $(this);
								$.each(data.returns_user, function(i, val){
									total_users.append("<div class=\"pf-element\"><span class=\"pf-label\">"+i+"</span><div class=\"pf-group\"><span class=\"pf-field\">Count: </span><span class=\"pf-field\">"+val.count+"</span><br /><span class=\"pf-field\">Total: </span><span class=\"pf-field\">$"+val.total+"</span></div></div>");
								});
							});
							$("#p_muid_total_return_payments").empty().each(function(){
								var total_payments = $(this);
								$.each(data.returns_payments, function(i, val){
									total_payments.append("<div class=\"pf-element\"><span class=\"pf-label\">"+i+"</span><div class=\"pf-group\"><span class=\"pf-field\">Count: </span><span class=\"pf-field\">"+val.count+"</span><br /><span class=\"pf-field\">Total: </span><span class=\"pf-field\">$"+val.total+"</span>"+(val.change_given ? "<br /><span class=\"pf-field\">Change Given: </span><span class=\"pf-field\">$"+val.change_given+"</span><br /><span class=\"pf-field\">Net Total: </span><span class=\"pf-field\">$"+val.net_total+"</span>" : "")+"</div></div>");
								});
							});

							// Totals
							$("#p_muid_total_total").html(data.totals.total);
							$("#p_muid_total_users").empty().each(function(){
								var total_users = $(this);
								$.each(data.totals_user, function(i, val){
									total_users.append("<div class=\"pf-element\"><span class=\"pf-label\">"+i+"</span><div class=\"pf-group\"><span class=\"pf-field\">$"+val.total+"</span></div></div>");
								});
							});
							$("#p_muid_total_payments").empty().each(function(){
								var total_payments = $(this);
								$.each(data.totals_payments, function(i, val){
									total_payments.append("<div class=\"pf-element\"><span class=\"pf-label\">"+i+"</span><div class=\"pf-group\"><span class=\"pf-field\">$"+val.total+"</span></div></div>");
								});
							});

							result_totals.slideDown(400);
						}
					});
				});
			});
			// ]]>
		</script>
		<button id="p_muid_retrieve_totals" class="pf-button ui-state-default ui-corner-all">Retrieve</button>
	</div>
	<div id="p_muid_result_totals" style="display: none;">
		<div class="pf-element">
			<span class="pf-label">Location</span>
			<span class="pf-field"><span id="p_muid_total_location">null</span></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Date</span>
			<span class="pf-field"><span id="p_muid_total_date">null</span></span>
		</div>
		<div class="pf-element pf-heading">
			<h1>Invoices</h1>
		</div>
		<div class="pf-element">
			<span class="pf-label">Count</span>
			<span class="pf-field"><span id="p_muid_total_invoice_count">null</span></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Total</span>
			<span class="pf-field">$<span id="p_muid_total_invoice_total">null</span></span>
		</div>
		<br class="pf-clearing" />
		<fieldset class="pf-group" style="float: left; clear: none; width: 46%;">
			<legend>Sales</legend>
			<div class="pf-element">
				<span class="pf-label">Count</span>
				<span class="pf-field"><span id="p_muid_total_sale_count">null</span></span>
			</div>
			<div class="pf-element">
				<span class="pf-label">Total</span>
				<span class="pf-field">$<span id="p_muid_total_sale_total">null</span></span>
			</div>
			<div class="pf-element pf-heading">
				<p>Payments</p>
			</div>
			<div id="p_muid_total_sales_payments">
				<div class="pf-element">
					<span class="pf-label">name</span>
					<div class="pf-group">
						<span class="pf-field">Count: </span>
						<span class="pf-field">1</span><br />
						<span class="pf-field">Total: </span>
						<span class="pf-field">1.00</span><br />
						<span class="pf-field">Change Given: </span>
						<span class="pf-field">1.00</span>
					</div>
				</div>
			</div>
			<div class="pf-element pf-heading">
				<p>Users</p>
			</div>
			<div id="p_muid_total_sale_users">
				<div class="pf-element">
					<span class="pf-label">1: name [name]</span>
					<div class="pf-group">
						<span class="pf-field">Count: </span>
						<span class="pf-field">1</span><br />
						<span class="pf-field">Total: </span>
						<span class="pf-field">1.00</span>
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset class="pf-group" style="float: right; clear: none; width: 46%;">
			<legend>Returns</legend>
			<div class="pf-element">
				<span class="pf-label">Count</span>
				<span class="pf-field"><span id="p_muid_total_return_count">null</span></span>
			</div>
			<div class="pf-element">
				<span class="pf-label">Total</span>
				<span class="pf-field">$<span id="p_muid_total_return_total">null</span></span>
			</div>
			<div class="pf-element pf-heading">
				<p>Payments</p>
			</div>
			<div id="p_muid_total_return_payments">
				<div class="pf-element">
					<span class="pf-label">name</span>
					<div class="pf-group">
						<span class="pf-field">Count: </span>
						<span class="pf-field">1</span><br />
						<span class="pf-field">Total: </span>
						<span class="pf-field">1.00</span><br />
						<span class="pf-field">Change Given: </span>
						<span class="pf-field">1.00</span>
					</div>
				</div>
			</div>
			<div class="pf-element pf-heading">
				<p>Users</p>
			</div>
			<div id="p_muid_total_return_users">
				<div class="pf-element">
					<span class="pf-label">1: name [name]</span>
					<div class="pf-group">
						<span class="pf-field">Count: </span>
						<span class="pf-field">1</span><br />
						<span class="pf-field">Total: </span>
						<span class="pf-field">1.00</span>
					</div>
				</div>
			</div>
		</fieldset>
		<br class="pf-clearing" />
		<div class="ui-state-highlight ui-corner-all ui-helper-clearfix" style="font-weight: bold; padding-left: 1em; padding-right: 1em;">
			<div class="pf-element pf-heading">
				<h1>Totals</h1>
			</div>
			<div class="pf-element">
				<span class="pf-label">Total</span>
				<span class="pf-field">$<span id="p_muid_total_total">null</span></span>
			</div>
			<div class="pf-element pf-heading">
				<p>Payments</p>
			</div>
			<div id="p_muid_total_payments">
				<div class="pf-element">
					<span class="pf-label">name</span>
					<div class="pf-group">
						<span class="pf-field">Total: </span>
						<span class="pf-field">1.00</span>
					</div>
				</div>
			</div>
			<div class="pf-element pf-heading">
				<p>Users</p>
			</div>
			<div id="p_muid_total_users">
				<div class="pf-element">
					<span class="pf-label">1: name [name]</span>
					<div class="pf-group">
						<span class="pf-field">Total: </span>
						<span class="pf-field">1.00</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>