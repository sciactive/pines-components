<?php
/**
 * Display a view to show customer history
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'View Customer History';
$pines->icons->load();

$loan_ids = $this->loan_ids;

?>
<style type="text/css">
	#p_muid_form .accordion-group {
		margin-bottom:10px;
	}
	#p_muid_form .accordion-heading {
		opacity: .8;
	}
	#p_muid_form .accordion-heading:hover {
		color:#fff;
	}
	.p_muid_loading {
		text-align:center;
		min-height: 300px;
		padding-top: 150px;
		font-weight: bold;
		font-size: 20px;
	}
	.ui-dialog.ui-widget {
		z-index: 1037 !important;
	}
</style>
<script type="text/javascript">
	pines(function(){
		setTimeout(function(){
			var form = $('#p_muid_form');
			
			$(".p_muid_interaction_table").pgrid({
				pgrid_toolbar: false,
				pgrid_footer: true,
				pgrid_view_height: 'auto',
				pgrid_sort_col: 2,
				pgrid_sort_ord: 'desc'
			});
			
			if (form.find('.p_muid_interaction_table').length < 2) {
				form.find('.p_muid_loading').hide();
				var grid = form.find('.p_muid_grid');
				grid.fadeIn();
			}
		}, 1000);
		if ($('.history_status').html().length > 0) {
			$('<hr style="margin-bottom:12px;"/>').insertAfter('.history_status');
		}
	});
</script>
<div id="p_muid_form">
	<div class="history_status"></div>
	<?php 
	if (count($loan_ids) > 1) {
		$c = 0;
		?><div class="accordion" id="p_muid_accordion_parent">
		<?php foreach ($loan_ids as $loan_id) {
		$loan = com_loan_loan::factory((int) $loan_id);
		$customer = $loan->customer;

		if (!isset($customer->guid)) { ?>
			<div class="alert-error accordion-heading" style="margin-bottom: 10px; border: 1px solid #fff;"><big class="accordion-toggle"><i class="icon-exclamation-sign"></i> Error Finding Customer.</big></div>
			<?php continue;
		} 
		
		$interactions = $pines->entity_manager->get_entities(
				array('class' => com_customer_interaction),
				array('&',
					'ref' => array('customer', $customer->guid),
					'tag' => array('com_customer', 'interaction')
				)
			);
		
		?>
				<div class="accordion-group">
					<a class="accordion-heading" data-parent="#p_muid_accordion_parent" data-toggle="collapse" href="#p_muid_collapse<?php echo $c;?>">
						<big class="accordion-toggle label-info" style="color:#fff;"><?php echo htmlspecialchars($customer->name); ?></big>
					</a>
					<div id="p_muid_collapse<?php echo $c;?>" class="accordion-body collapse">
						<div class="accordion-inner clearfix">
							<?php if (empty($interactions)) {
								echo '<i class="icon-info-sign"></i> This customer does not have any customer history.';
							} else { ?>
							<table class="p_muid_interaction_table">
								<thead>
									<tr>
										<th>ID</th>
										<th>Created</th>
										<th>Appointment</th>
										<th>Employee</th>
										<th>Interaction</th>
										<th>Status</th>
										<th>Comments</th>
									</tr>
								</thead>
								<tbody>
						<?php foreach ($interactions as $cur_interaction) { ?>
									<tr title="<?php echo htmlspecialchars($cur_interaction->guid); ?>">
										<td><a data-entity="<?php echo htmlspecialchars($cur_interaction->guid); ?>" data-entity-context="com_customer_interaction"><?php echo htmlspecialchars($cur_interaction->guid); ?></a></td>
										<td><?php echo htmlspecialchars(format_date($cur_interaction->p_cdate, 'date_sort')); ?></td>
										<td><?php echo htmlspecialchars(format_date($cur_interaction->action_date, 'date_sort')); ?></td>
										<td><a data-entity="<?php echo htmlspecialchars($cur_interaction->employee->guid); ?>" data-entity-context="user"><?php echo htmlspecialchars($cur_interaction->employee->name); ?></a></td>
										<td><?php echo htmlspecialchars($cur_interaction->type); ?></td>
										<td><?php echo ucwords($cur_interaction->status); ?></td>
										<td><?php echo htmlspecialchars($cur_interaction->comments); ?></td>
									</tr>
						<?php } ?>
								</tbody>
							</table>
							<?php } ?>
						</div>
					</div>
				</div>
			
	<?php $c++; 
			} ?>
			</div>
	<?php } else {
		$loan = com_loan_loan::factory((int) $loan_ids);
		$customer = $loan->customer;
		if (!isset($customer->guid)) { ?>
			<div class="alert-error accordion-heading" style="margin-bottom: 10px; border: 1px solid #fff;"><big class="accordion-toggle"><i class="icon-exclamation-sign"></i> Error Finding Customer.</big></div>
			<?php
		} else { 
			$interactions = $pines->entity_manager->get_entities(
				array('class' => com_customer_interaction),
				array('&',
					'ref' => array('customer', $customer->guid),
					'tag' => array('com_customer', 'interaction')
				)
			);
			?>
			<div class="accordion">
				<div class="accordion-group">
					<a class="accordion-heading" data-toggle="collapse" href="javascript:void(0);">
						<big class="accordion-toggle label-info" style="color:#fff;"><?php echo htmlspecialchars($customer->name); ?></big>
					</a>
					<div class="accordion-body in collapse">
						<div class="accordion-inner clearfix">
							<?php if (empty($interactions)) {
								echo '<i class="icon-info-sign"></i> This customer does not have any customer history.';
							} else { ?>
							<div class="p_muid_loading"><i class="icon-spinner icon-spin"></i> Loading Customer History...</div>
							<div class="p_muid_grid <?php echo (empty($interactions)) ? '' : 'hide'; ?>">
								<table class="p_muid_interaction_table">
									<thead>
										<tr>
											<th>ID</th>
											<th>Created</th>
											<th>Appointment</th>
											<th>Employee</th>
											<th>Interaction</th>
											<th>Status</th>
											<th>Comments</th>
										</tr>
									</thead>
									<tbody>
							<?php foreach ($interactions as $cur_interaction) { ?>
										<tr title="<?php echo htmlspecialchars($cur_interaction->guid); ?>">
											<td><a data-entity="<?php echo htmlspecialchars($cur_interaction->guid); ?>" data-entity-context="com_customer_interaction"><?php echo htmlspecialchars($cur_interaction->guid); ?></a></td>
											<td><?php echo htmlspecialchars(format_date($cur_interaction->p_cdate, 'date_sort')); ?></td>
											<td><?php echo htmlspecialchars(format_date($cur_interaction->action_date, 'date_sort')); ?></td>
											<td><a data-entity="<?php echo htmlspecialchars($cur_interaction->employee->guid); ?>" data-entity-context="user"><?php echo htmlspecialchars($cur_interaction->employee->name); ?></a></td>
											<td><?php echo htmlspecialchars($cur_interaction->type); ?></td>
											<td><?php echo ucwords($cur_interaction->status); ?></td>
											<td><?php echo htmlspecialchars($cur_interaction->comments); ?></td>
										</tr>
							<?php } ?>
									</tbody>
								</table>
							</div>
						<?php } ?>
						</div>
					</div>
					
				</div>
			</div>
		<?php }
	} ?>
</div>