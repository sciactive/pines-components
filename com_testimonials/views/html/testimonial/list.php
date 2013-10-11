<?php
/**
 * Lists testimonials.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Testimonials - '.ucwords($this->testimonial_type).' Testimonials';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_testimonials/testimonial/list']);

?>
<script type="text/javascript">
	pines(function(){
		
		var testimonial_type = <?php echo json_encode(ucwords($this->testimonial_type)); ?>;
		
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_testimonials/newtestimonials')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_testimonials', 'testimonial/edit')); ?>},
				<?php } if (gatekeeper('com_testimonials/edittestimonials')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', selection_optional: true, url: <?php echo json_encode(pines_url('com_testimonials', 'testimonial/edit', array('id' => '__title__'))); ?>},
				<?php } if (gatekeeper('com_testimonials/changestatus')) { ?>
				{type: 'button', text: 'Approve/Disapprove Testimonial', extra_class: 'picon picon-task-accepted', click: function(e, row){
					testimonial_grid.changestatus_form($(row).attr("title"));
				}},
				{type: 'separator'},
				<?php } if (gatekeeper('com_testimonials/toggletestimonials')) { ?>
				{type: 'button', text: 'Pending', extra_class: 'picon picon-view-refresh', selection_optional: true, url: <?php echo json_encode(pines_url('com_testimonials', 'testimonial/list', array('type' => 'pending'))); ?>},
				{type: 'button', text: 'Approved', extra_class: 'picon picon-dialog-ok-apply', selection_optional: true, url: <?php echo json_encode(pines_url('com_testimonials', 'testimonial/list', array('type' => 'approved'))); ?>},
				{type: 'button', text: 'Denied', extra_class: 'picon picon-dialog-close', selection_optional: true, url: <?php echo json_encode(pines_url('com_testimonials', 'testimonial/list', array('type' => 'denied'))); ?>},
				{type: 'separator'},
				<?php } if (gatekeeper('com_testimonials/deletetestimonials')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_testimonials', 'testimonial/delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'loans',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_loan/loan/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var testimonial_grid = $("#p_muid_grid").pgrid(cur_options);
		
		if (testimonial_type) {
			var button = $('.ui-pgrid-toolbar').find('button:contains('+testimonial_type+')');
			button.addClass('ui-state-active');
		}
		
		testimonial_grid.changestatus_form = function(testimonial_id){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_testimonials', 'forms/changestatus')); ?>,
				type: "POST",
				dataType: "html",
				data: {"id": testimonial_id},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the change status form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Update Testimonial Status\"></div>").html(data+"<br />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						width: 600,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Update Testimonial": function(){
								var status = form.find(":[name=status]").val();
								var quotefeedback = form.find(":[name=quotefeedback]").val();
								if (status == "") {
									alert('Please specify the status.');
								} else {
									form.dialog('close');
									// Submit the request status change.
									pines.post(<?php echo json_encode(pines_url('com_testimonials', 'testimonial/changestatus')); ?>, {
										"id": testimonial_id,
										"quotefeedback": quotefeedback,
										"status": status
									});
								}
							}
						}
					});
					pines.play();
				}
			});
		};
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Customer</th>
			<th>Customer Email</th>
			<th>Created By</th>
			<th>Location</th>
			<th>City</th>
			<th>State</th>
			<th>Creation Date</th>
			<th>Status</th>
			<th>Rating</th>
			<th>Share Allowed</th>
			<th>Share Anonymous</th>
			<th>Feedback</th>
			<th>Testimonial</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->testimonials as $testimonial) { ?>
		<tr title="<?php echo htmlspecialchars($testimonial->guid); ?>">
			<td><a data-entity="<?php echo htmlspecialchars($testimonial->guid); ?>" data-entity-context="com_testimonials_testimonial"><?php echo htmlspecialchars($testimonial->id); ?></a></td>
			<td><a data-entity="<?php echo htmlspecialchars($testimonial->customer->guid); ?>" data-entity-context="com_customer_customer"><?php echo htmlspecialchars($testimonial->customer->name); ?></a></td>
			<td><?php echo htmlspecialchars($testimonial->customer->email); ?></td>
			<td><a data-entity="<?php echo htmlspecialchars($testimonial->user->guid); ?>" data-entity-context="user"><?php echo htmlspecialchars($testimonial->user->name); ?></a></td>
			<td><?php echo htmlspecialchars($testimonial->customer->group->name); ?></td>
			<td><?php echo htmlspecialchars($testimonial->customer->city); ?></td>
			<td><?php echo htmlspecialchars($testimonial->customer->state); ?></td>
			<td><?php echo htmlspecialchars(format_date($testimonial->p_cdate, "date_short")); ?></td>
			<td>
			<?php 
			if (!isset($testimonial->status))
				echo "Pending";
			elseif ($testimonial->status)
				echo "Approved";
			elseif (!$testimonial->status)
				echo "Denied";
			?>
			</td>
			<td><?php echo htmlspecialchars($testimonial->rating); ?></td>
			<td><?php echo ($testimonial->share) ? 'Yes' : 'No'; ?></td>
			<td><?php echo ($testimonial->anon) ? 'Yes' : 'No'; ?></td>
			<td><?php echo htmlspecialchars(substr($testimonial->feedback, 0, 40)); ?>...</td>
			<td><?php echo (!empty($testimonial->quotefeedback)) ? htmlspecialchars(substr($testimonial->quotefeedback, 0, 40)).'...' : ''; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>