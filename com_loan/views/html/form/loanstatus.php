<?php
/**
 * Display a form to change the Status on loans.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$loan_ids = $this->loan_ids;
if (!is_array($loan_ids))
	$loan_ids = array($loan_ids); // Makes an array out of the string guid.
?>
<style type="text/css">
	#p_muid_form {
		padding-top:20px;
	}
	#p_muid_form .item {
		background: #EEEEEE;
		border-bottom: 1px dotted #CCCCCC;
		padding: 15px;
		text-shadow: 1px 1px 0 #FFFFFF;
		margin-bottom: 10px;
	}
	#p_muid_form .item:hover {
		background: #ddd;
	}
	#p_muid_form .name {
		line-height: 30px;
		font-weight:bold;
		color: #777;
		font-size: 20px;
	}
	#p_muid_form .error-message {
		clear:both;
		padding-top:10px;
	}
	#p_muid_form .loan-label {
		font-size: 15px;
		text-shadow: none;
		color: #999;
	}
	#p_muid_form .results {
		text-align:right;
	}
</style>
<script type="text/javascript">
	pines(function(){
		var statii = $('#p_muid_form').find('[name=loan_status]');
		var customer_search_button = $('.ui-pgrid-toolbar .picon-system-search').closest('button');
		var change_status = function(item) {
			var status = item.find('[name=loan_status]').val();
			var loan_guid = item.find('[name=loan_status]').attr('data-guid');
			var results = item.find('.results');
			$.ajax({
				url: <?php echo json_encode(pines_url('com_loan', 'loan/saveloanstatus')); ?>,
				type: "GET",
				dataType: "json",
				data: {'id': loan_guid, 'status': status},
				beforeSend: function(){
					results.addClass('text-info').removeClass('text-error text-success').html('<i class="icon-spin icon-spinner"></i> Saving...');
				},
				error: function(XMLHttpRequest, textStatus){
					results.addClass('text-error').removeClass('text-info text-success').html('<i class="icon-remove"></i> Error');
					item.append('<div class="text-error error-message">An error occurred: </div>\n'+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data.no_loan) {
						results.addClass('text-error').removeClass('text-info text-success').html('<i class="icon-remove"></i> Error');
						item.append('<div class="text-error error-message">Loan was not accessible. Do you have permission? Was it deleted?</div>');
						return;
					}
					if (data.failed) {
						results.addClass('text-error').removeClass('text-info text-success').html('<i class="icon-remove"></i> Error');
						item.append('<div class="text-error error-message">The status was not changed on the loan.</div>');
						return;
					}
					if (data.success) {
						results.addClass('text-success').removeClass('text-info text-error').html('<i class="icon-ok"></i> Changed!');
						// Make the grid re-search.
						var link = $('<a href="javascript:void(0);">refresh grid with same search.</a>');
						link.click(function(){
							customer_search_button.click();
						});
						var div = $('<div class="alert error-message" style="margin-top: 10px;">Information in this grid is now outdated. Click here to </div>');
						div.append(link);
						item.append(div);
					}
				}
			});
		};
		statii.change(function(){
			change_status($(this).closest('.item'));
		});
		
	});
</script>
<div class="pf-form" id="p_muid_form">
	<?php foreach ($loan_ids as $cur_id) {
			$cur_loan = com_loan_loan::factory((int) $cur_id); ?>
	<div class="item clearfix">
		<h4><?php echo htmlspecialchars($cur_loan->customer->name); ?>  <span class="loan-label pull-right">Loan ID: <?php echo htmlspecialchars($cur_loan->id); ?></span></h4>
		<div class="name row-fluid">
			<div class="span8">
				<select class="span12" name="loan_status" data-guid="<?php echo $cur_loan->guid; ?>">
					<option value="">None</option>
					<?php echo (gatekeeper('com_loan/activeloan')) ?  ('<option value="active" '.(($cur_loan->has_tag('active')) ? 'selected=selected' : '').'>Active</option>') : ''; ?>
					<?php echo (gatekeeper('com_loan/payoffloan')) ?  ('<option value="paidoff" '.(($cur_loan->has_tag('paidoff')) ? 'selected=selected' : '').'>Paid in Full</option>') : ''; ?>
					<?php echo (gatekeeper('com_loan/writeoffloan')) ?  ('<option value="writtenoff" '.(($cur_loan->has_tag('writeoff')) ? 'selected=selected' : '').'>Written Off</option>') : ''; ?>
					<?php echo (gatekeeper('com_loan/cancelloan')) ?  ('<option value="cancelled" '.(($cur_loan->has_tag('cancelled')) ? 'selected=selected' : '').'>Cancelled</option>') : ''; ?>
					<?php echo (gatekeeper('com_loan/soldloan')) ?  ('<option value="sold" '.(($cur_loan->has_tag('sold')) ? 'selected=selected' : '').'>Sold</option>') : ''; ?>
				</select>
			</div>
            <div class="results text-success span4"></div>
		</div>
	</div>
	<?php } ?>
</div>