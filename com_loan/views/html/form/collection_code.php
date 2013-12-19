<?php
/**
 * Display a form to change the Status Code.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

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
	#p_muid_form .code-status {
		text-align:right;
	}
</style>
<script type="text/javascript">
	pines(function(){
		var item = $('#p_muid_form').find('.item');
		var collection_code = $('#p_muid_form').find('[name=collection_code]');
		var code_status = $('#p_muid_form').find('.code-status');
		var customer_search = $('.ui-pgrid-toolbar input[type=text]');
		var customer_search_button = $('.ui-pgrid-toolbar .picon-system-search').closest('button');
		var loan_guid = <?php echo json_encode($this->entity->guid); ?>;
		var loan_id = <?php echo json_encode($this->entity->id); ?>;
		var change_code = function(code) {
			$.ajax({
				url: <?php echo json_encode(pines_url('com_loan', 'loan/savecollectioncode')); ?>,
				type: "GET",
				dataType: "json",
				data: {'id': loan_guid, 'code': code},
				beforeSend: function(){
					code_status.addClass('text-info').removeClass('text-error text-success').html('<i class="icon-spin icon-spinner"></i> Saving...');
				},
				error: function(XMLHttpRequest, textStatus){
					code_status.addClass('text-error').removeClass('text-info text-success').html('<i class="icon-remove"></i> Error');
					item.append('<div class="text-error error-message">An error occurred: </div>\n'+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data.no_loan) {
						code_status.addClass('text-error').removeClass('text-info text-success').html('<i class="icon-remove"></i> Error');
						item.append('<div class="text-error error-message">Loan was not accessible. Do you have permission? Was it deleted?</div>');
						return;
					}
					if (data.paid) {
						code_status.addClass('text-error').removeClass('text-info text-success').html('<i class="icon-remove"></i> Error');
						item.append('<div class="text-error error-message">Loan Already Paid</div>');
						return;
					}
					if (data.failed) {
						code_status.addClass('text-error').removeClass('text-info text-success').html('<i class="icon-remove"></i> Error');
						item.append('<div class="text-error error-message">The code was not saved on the loan.</div>');
						return;
					}
					if (data.success) {
						code_status.addClass('text-success').removeClass('text-info text-error').html('<i class="icon-ok"></i> Saved!');
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
		collection_code.change(function(){
			change_code($(this).val());
		});
		
	});
</script>
<div class="pf-form" id="p_muid_form">
	<div class="item clearfix">
		<h4><?php echo htmlspecialchars($this->entity->customer->name); ?> <span class="loan-label pull-right">Loan ID: <?php echo htmlspecialchars($this->entity->id); ?></span></h4>
		<div class="name row-fluid">
			<div class="span8">
				<select class="span12" name="collection_code">
					<option value="">None</option>
				<?php 
				$codes = $pines->config->com_loan->collections_codes;
				asort($codes);
				foreach ($codes as $cur_code) {
					$cur_code = explode(':', $cur_code);
					echo '<option value="'.htmlspecialchars($cur_code[0]).'" '.(($this->entity->collection_code == $cur_code[0]) ? 'selected=selected' : '').'>'.htmlspecialchars($cur_code[0]).' - '.htmlspecialchars($cur_code[1]).'</option>';
				} ?>
				</select>
			</div>
            <div class="code-status text-success span4"></div>
		</div>
	</div>
</div>