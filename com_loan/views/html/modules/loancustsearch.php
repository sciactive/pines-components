<?php
/**
 * com_loan's module to search a loan by customer.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrel <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Search Loan By Customer'
?>
<script type="text/javascript">
	pines(function(){
		var loan_search = $('#loan-widget-search');
		var loan_button = loan_search.find('.widget-search');
		var loan_input = loan_search.find('[name=q]');
		var status_icon = loan_search.find('.status-icon');
		var status_msg = loan_search.find('.status-message');
		var loan_status = loan_search.find('.widget-status');
		var loan_results = loan_search.find('.widget-results');
		var loan_table = loan_search.find('.widget-table');
		var loan_table_tbody = loan_table.find('tbody');
		var loan_nav = loan_results.find('.widget-nav');
		var loan_nav_prev = loan_nav.find('.prev-button');
		var loan_nav_next = loan_nav.find('.next-button');
		var loan_nav_buttons = loan_nav.find('.widget-nav-button');
		var per_page = 2;
		var make_row = function(row, data){
			row.append('<td><a data-entity="'+pines.safe(data.guid)+'" data-entity-context="com_loan_loan">Loan '+pines.safe(data.id)+'</a>'+'</td>');
			row.append('<td><a data-entity="'+pines.safe(data.customer_guid)+'" data-entity-context="com_customer_customer">'+pines.safe(data.customer_name)+'</a></td>');
			if (data.balance == '$0.00') {
				row.append('<td><span class="text-success">Paid Off</span></td>');
				row.append('<td style="text-align:right;"><span class="text-success"><strong>'+pines.safe(data.total_payments_made)+'</strong></span></td>');
			} else if (data.archived) {
				row.append('<td><span class="text-warning">Archived</span></td>');
				row.append('<td style="text-align:right;"><span class="text-warning"><strong>'+pines.safe(data.archived)+'</strong></span></td>');
			} else if (data.current_past_due != '$0.00') {
				row.append('<td><span class="text-error">Past Due</span></td>');
				row.append('<td style="text-align:right;"><span class="text-error"><strong>'+pines.safe(data.current_past_due)+'</strong> <span class="widget-refresh widget-tooltip" title="Refresh to Confirm Amount." data-toggle="tooltip" style="cursor:pointer;"><i class="icon-refresh"></i></span></span></td>');
			} else if (data.missed_first_payment) {
				row.append('<td><span class="text-error widget-tooltip" title="'+pines.safe(data.missed_first_payment)+' days late" >Missed 1st</span></td>');
				row.append('<td style="text-align:right;"><span class="text-error"><strong>'+pines.safe(data.next_payment_amount)+'</strong> <span class="widget-refresh widget-tooltip" title="Refresh to Confirm Amount." data-toggle="tooltip" style="cursor:pointer;"><i class="icon-refresh"></i></span></span></td>');
			} else {
				row.append('<td><span class="text-info">'+pines.safe(data.next_payment_due)+'</span></td>');
				row.append('<td style="text-align:right;"><span class="text-info"><strong>'+pines.safe(data.next_payment_amount)+'</strong> <span class="widget-refresh widget-tooltip" title="Refresh to Confirm Amount." data-toggle="tooltip" style="cursor:pointer;"><i class="icon-refresh"></i></span></span></td>');
			}
			return row;
		};
		var search_loans = function(query, refresh, tr) {
			var options = {};
			options.q = query;
			if (refresh)
				options.refresh_payment_info = 'true';
			$.ajax({
				url: <?php echo json_encode(pines_url('com_loan', 'loan/search')); ?>,
				type: "GET",
				dataType: "json",
				data: options,
				beforeSend: function(){
					if (refresh)
						return;
					loan_results.hide();
					loan_table_tbody.html('');
					loan_status.removeClass('alert-success alert-danger').addClass('alert-info');
					status_icon.removeClass('icon-ok icon-remove').addClass('icon-spin icon-spinner');
					status_msg.text('Searching Loans...');
					loan_status.show();
				},
				error: function(XMLHttpRequest, textStatus){
					if (refresh)
						return;
					loan_status.removeClass('alert-success alert-info').addClass('alert-danger');
					status_icon.removeClass('icon-ok icon-spin icon-spinner').addClass('icon-remove');
					status_msg.text(pines.safe(XMLHttpRequest.status)+': '+pines.safe(textStatus));
					pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					var count = data.length;
					if (refresh) {
						tr.html('');
						var refreshed_tr = make_row(tr,data[0]);
						tr.html(refreshed_tr.html());
						tr.find('.widget-tooltip').tooltip();
						return;
					}
						
					loan_status.removeClass('alert-info alert-danger').addClass('alert-success');
					status_icon.removeClass('icon-spin icon-spinner icon-remove').addClass('icon-ok');
					if (!count) {
						status_msg.text('No matching Loans were found.');
						return;
					}
					
					status_msg.html('Found <strong>'+data.length+'</strong> matching loans!');
					var c = 1;
					$.each(data, function(){
						var cur_data = this;
						var tr = $('<tr></tr>');
						if (c > per_page)
							tr.addClass('hide');
						tr = make_row(tr, cur_data);
						loan_table_tbody.append(tr);
						loan_table_tbody.find('.widget-tooltip').tooltip();
						c++;
					});
					
					if (count > per_page) {
						loan_nav_prev.hide();
						loan_nav_next.show();
						loan_nav.show();
					} else {
						loan_nav_buttons.hide();
					}
					loan_results.show();
				}
			});
		};
		
		loan_search.find('.widget-tooltip').tooltip();
		loan_button.click(function(){
			var query = loan_input.val();
			if (query == '')
				loan_input.focus();
			else
				search_loans(query, false, null);
		});
		
		loan_input.keypress(function(e){
			if (e.keyCode == 13)
				loan_button.click();
		});
		
		var change_page = function(items, item_selector, type, per_page) {
			var count = items.length;
			var position = (type == 'prev') ? items.filter(':visible').first() : items.filter(':visible').last();
			var num = position.prevAll(item_selector).length + 1;
			var show_count = (type == 'prev') ? num - per_page : num + per_page;
			var elements = $();
			var hide_prev = (type == 'prev' && (num - per_page == 1)) ? true : false;
			var hide_next = (type != 'prev' && (num + per_page >= count)) ? true : false;
			if (type == 'prev') {
				for (var i = num - 1; i >= show_count; i--) {
					elements = elements.add(items.eq(i - 1));
				}
			} else {
				for (var i = num; i < show_count; i++) {
					elements = elements.add(items.eq(i));
				}
			}
			(hide_prev) ? loan_nav_prev.hide() : loan_nav_prev.show();
			(hide_next) ? loan_nav_next.hide() : loan_nav_next.show();
			items.addClass('hide');
			elements.removeClass('hide');
		};
		
		loan_nav_buttons.click(function(){
			var type = ($(this).hasClass('prev-button')) ? 'prev' : 'next';
			var items = loan_table_tbody.find('tr');
			change_page(items, 'tr', type, per_page);
		});
		
		loan_search.on('click', '.widget-refresh', function(){
			var tr = $(this).closest('tr');
			var id = tr.find('td').first().text().replace(/\D/g, '');
			search_loans('loan:'+id, true, tr);
		});
	});
</script>
<div id="loan-widget-search">
	<p>
		<input type="text" class="input-medium" name="q" placeholder="Name, Phone, or Email">
		<span class="btn widget-search"><i class="icon-search"></i></span>
		<span class="btn widget-tooltip" data-toggle="tooltip" title="Search for Loans by Customer Name, Phone or Email" ><i class="icon-question"></i></span>
	</p>
	<div class="alert alert-info hide widget-status">
		<i class="status-icon icon-spin icon-spinner"></i>
		<span class="status-message">Searching Loans...</span>
	</div>
	<div class="widget-results hide">
		<table class="table widget-table">
			<thead>
				<th class="text-success">Loan</th>
				<th class="text-success">Customer</th>
				<th class="text-success">Due</th>
				<th class="text-success">Amount</th>
			</thead>
			<tbody></tbody>
		</table>
		<ul class="widget-nav pager hide">
			<li class="previous hide">
				<a class="prev-button widget-nav-button" href="javascript:void(0);">&larr; Previous</a>
			</li>
			<li class="next hide">
				<a class="next-button widget-nav-button" href="javascript:void(0);">Next &rarr;</a>
			</li>
		</ul>
	</div>
</div>