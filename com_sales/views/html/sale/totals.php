<?php
/**
 * Provides a form for viewing sales totals.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sales Total Report';
$pines->com_jstree->load();
?>
<div id="p_muid_form" class="pf-form">
	<div class="pf-element pf-heading">
		<h3>Parameters</h3>
	</div>
	<script type="text/javascript">
		pines(function(){
			var location = <?php echo json_encode("{$_SESSION['user']->group->guid}"); ?>;
			var date_start = $("#p_muid_date_start");
			var date_end = $("#p_muid_date_end");
			var result_totals = $("#p_muid_result_totals");

			$("#p_muid_hide_parameters").click(function(){
				$("#p_muid_form > .pf-element:not(.pf-heading)").slideToggle();
			});
			<?php if (gatekeeper('com_sales/totalothersales')) { ?>
			// Location Tree
			var tree_location = $("#p_muid_form [name=location]");
			$("#p_muid_form .location_tree")
			.bind("select_node.jstree", function(e, data){
				tree_location.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
			})
			.bind("before.jstree", function (e, data){
				if (data.func == "parse_json" && "args" in data && 0 in data.args && "attr" in data.args[0] && "id" in data.args[0].attr)
					data.args[0].attr.id = "p_muid_"+data.args[0].attr.id;
			})
			.bind("loaded.jstree", function(e, data){
				var path = data.inst.get_path("#"+data.inst.get_settings().ui.initially_select, true);
				if (!path.length) return;
				data.inst.open_node("#"+path.join(", #"), false, true);
			})
			.jstree({
				"plugins" : [ "themes", "json_data", "ui" ],
				"json_data" : {
					"ajax" : {
						"dataType" : "json",
						"url" : <?php echo json_encode(pines_url('com_jstree', 'groupjson')); ?>
					}
				},
				"ui" : {
					"select_limit" : 1,
					"initially_select" : [<?php echo json_encode("{$_SESSION['user']->group->guid}"); ?>]
				}
			});
			<?php } ?>

			$("#p_muid_retrieve_totals").click(function(){
				var loader;

				<?php if (gatekeeper('com_sales/totalothersales')) { ?>
				location = tree_location.val();
				if ($("#p_muid_form [name=all_locations]").attr('checked') || location == '')
					location = <?php echo json_encode("{$_SESSION['user']->group->guid}"); ?>;
				<?php } ?>

				$.ajax({
					url: <?php echo json_encode(pines_url('com_sales', 'sale/totalsjson')); ?>,
					type: "POST",
					dataType: "json",
					data: {"location": location, "date_start": date_start.val(), "date_end": date_end.val()},
					beforeSend: function(){
						result_totals.hide("normal");
						loader = $.pnotify({
							title: 'Sales Totals',
							text: 'Retrieving totals from server...',
							icon: 'picon picon-throbber',
							nonblock: true,
							hide: false,
							history: false
						});
					},
					complete: function(){
						loader.pnotify_remove();
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to retrieve totals:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (!data) {
							alert("No sales data was returned.");
							return;
						}
						$("#p_muid_total_location").html(pines.safe(data.location));
						$("#p_muid_total_date").html(data.date_start == data.date_end ? pines.safe(data.date_start) : pines.safe(data.date_start+" - "+data.date_end));

						// Invoices
						$("#p_muid_total_invoice_count").html(pines.safe(data.invoices.count));
						$("#p_muid_total_invoice_subtotal").html(pines.safe(data.invoices.subtotal));
						$("#p_muid_total_invoice_total").html(pines.safe(data.invoices.total));

						// Sales
						$("#p_muid_total_sale_count").html(pines.safe(data.sales.count));
						$("#p_muid_total_sale_subtotal").html(pines.safe(data.sales.subtotal));
						$("#p_muid_total_sale_total").html(pines.safe(data.sales.total));
						$("#p_muid_total_sale_users").empty().each(function(){
							var total_users = $(this);
							$.each(data.sales_user, function(i, val){
								total_users.append("<div class=\"pf-element\"><span class=\"pf-label\">"+pines.safe(i)+"</span><div class=\"pf-group\"><span class=\"pf-field\">Count: </span><span class=\"pf-field\">"+pines.safe(val.count)+"</span><br /><span class=\"pf-field\">Subtotal: </span><span class=\"pf-field\">$"+pines.safe(val.subtotal)+"</span><br /><span class=\"pf-field\">Total: </span><span class=\"pf-field\">$"+pines.safe(val.total)+"</span></div></div>");
							});
						});
						$("#p_muid_total_sales_payments").empty().each(function(){
							var total_payments = $(this);
							$.each(data.payments, function(i, val){
								total_payments.append("<div class=\"pf-element\"><span class=\"pf-label\">"+pines.safe(i)+"</span><div class=\"pf-group\"><span class=\"pf-field\">Count: </span><span class=\"pf-field\">"+pines.safe(val.count)+"</span><br /><span class=\"pf-field\">Total: </span><span class=\"pf-field\">$"+pines.safe(val.total)+"</span>"+(val.change_given ? "<br /><span class=\"pf-field\">Change Given: </span><span class=\"pf-field\">$"+pines.safe(val.change_given)+"</span><br /><span class=\"pf-field\">Net Total: </span><span class=\"pf-field\">$"+pines.safe(val.net_total)+"</span>" : "")+"</div></div>");
							});
						});

						// Returns
						$("#p_muid_total_return_count").html(pines.safe(data.returns.count));
						$("#p_muid_total_return_subtotal").html(pines.safe(data.returns.subtotal));
						$("#p_muid_total_return_total").html(pines.safe(data.returns.total));
						$("#p_muid_total_return_users").empty().each(function(){
							var total_users = $(this);
							$.each(data.returns_user, function(i, val){
								total_users.append("<div class=\"pf-element\"><span class=\"pf-label\">"+pines.safe(i)+"</span><div class=\"pf-group\"><span class=\"pf-field\">Count: </span><span class=\"pf-field\">"+pines.safe(val.count)+"</span><br /><span class=\"pf-field\">Subtotal: </span><span class=\"pf-field\">$"+pines.safe(val.subtotal)+"</span><br /><span class=\"pf-field\">Total: </span><span class=\"pf-field\">$"+pines.safe(val.total)+"</span></div></div>");
							});
						});
						$("#p_muid_total_return_payments").empty().each(function(){
							var total_payments = $(this);
							$.each(data.returns_payments, function(i, val){
								total_payments.append("<div class=\"pf-element\"><span class=\"pf-label\">"+pines.safe(i)+"</span><div class=\"pf-group\"><span class=\"pf-field\">Count: </span><span class=\"pf-field\">"+pines.safe(val.count)+"</span><br /><span class=\"pf-field\">Total: </span><span class=\"pf-field\">$"+pines.safe(val.total)+"</span>"+(val.change_given ? "<br /><span class=\"pf-field\">Change Given: </span><span class=\"pf-field\">$"+pines.safe(val.change_given)+"</span><br /><span class=\"pf-field\">Net Total: </span><span class=\"pf-field\">$"+pines.safe(val.net_total)+"</span>" : "")+"</div></div>");
							});
						});

						// Totals
						$("#p_muid_total_subtotal").html(pines.safe(data.totals.subtotal));
						$("#p_muid_total_total").html(pines.safe(data.totals.total));
						$("#p_muid_total_users").empty().each(function(){
							var total_users = $(this);
							$.each(data.totals_user, function(i, val){
								total_users.append("<div class=\"pf-element\"><span class=\"pf-label\">"+pines.safe(i)+"</span><div class=\"pf-group\"><span class=\"pf-field\">Subtotal: </span><span class=\"pf-field\">$"+pines.safe(val.subtotal)+"</span><br /><span class=\"pf-field\">Total: </span><span class=\"pf-field\">$"+pines.safe(val.total)+"</span></div></div>");
							});
						});
						$("#p_muid_total_payments").empty().each(function(){
							var total_payments = $(this);
							$.each(data.totals_payments, function(i, val){
								total_payments.append("<div class=\"pf-element\"><span class=\"pf-label\">"+pines.safe(i)+"</span><div class=\"pf-group\"><span class=\"pf-field\">$"+pines.safe(val.total)+"</span></div></div>");
							});
						});

						result_totals.slideDown(400);
					}
				});
			});
			$("#p_muid_date_start").datepicker({
				dateFormat: "yy-mm-dd",
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true
			});
			$("#p_muid_date_end").datepicker({
				dateFormat: "yy-mm-dd",
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true
			});
			var location_tree = $("#p_muid_form .location_tree");
		});
	</script>
	<button id="p_muid_hide_parameters" class="btn" style="float: right;">Toggle Form</button>
	<?php if (gatekeeper('com_sales/totalothersales')) { // TODO: Show all groups in the tree. ?>
	<div class="pf-element">
		<span class="pf-label">Location</span>
		<div class="pf-group">
			<div class="pf-field location_tree ui-widget-content ui-corner-all" style="height: 180px; width: 200px; overflow: auto;"></div>
		</div>
		<input type="hidden" name="location" />
	</div>
	<?php } ?>
	<div class="pf-element">
		<label><span class="pf-label">Start Date</span>
			<input class="pf-field" type="text" id="p_muid_date_start" name="date_start" size="24" value="<?php echo htmlspecialchars(format_date(time(), 'date_sort')); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">End Date</span>
			<input class="pf-field" type="text" id="p_muid_date_end" name="date_end" size="24" value="<?php echo htmlspecialchars(format_date(time(), 'date_sort')); ?>" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<button id="p_muid_retrieve_totals" class="pf-button btn">Retrieve</button>
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
			<h3>Invoices</h3>
		</div>
		<div class="pf-element">
			<span class="pf-label">Count</span>
			<span class="pf-field"><span id="p_muid_total_invoice_count">null</span></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Subtotal</span>
			<span class="pf-field">$<span id="p_muid_total_invoice_subtotal">null</span></span>
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
				<span class="pf-label">Subtotal</span>
				<span class="pf-field">$<span id="p_muid_total_sale_subtotal">null</span></span>
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
				<span class="pf-label">Subtotal</span>
				<span class="pf-field">$<span id="p_muid_total_return_subtotal">null</span></span>
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
				<h3>Totals</h3>
			</div>
			<div class="pf-element">
				<span class="pf-label">Subtotal</span>
				<span class="pf-field">$<span id="p_muid_total_subtotal">null</span></span>
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