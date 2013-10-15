<?php
/**
 * Lists Reviews.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Reviews - '.ucwords($this->testimonial_type).' Reviews';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_testimonials/testimonial/list_reviews']);
$pines->com_ptags->load();
?>
<script type="text/javascript">
	pines(function(){
		
		var testimonial_type = <?php echo json_encode(ucwords($this->testimonial_type)); ?>;
		var search_box;
		var submit_search = function(){
			var search_string = search_box.val();
			if (search_string == "") {
				alert("Please enter a search string.");
				return;
			}
			var loader;
			$.ajax({
				url: <?php echo json_encode(pines_url('com_testimonials', 'search')); ?>,
				type: "POST",
				dataType: "json",
				data: {"q": search_string, "status": <?php echo json_encode($this->testimonial_type); ?>, "type": 'review'},
				beforeSend: function(){
					loader = $.pnotify({
						title: 'Search',
						text: 'Searching the database...',
						icon: 'picon picon-throbber',
						nonblock: true,
						hide: false,
						history: false
					});
					testimonial_grid.pgrid_get_all_rows().pgrid_delete();
				},
				complete: function(){
					loader.pnotify_remove();
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data) {
						alert("No testimonials were found that matched the query.");
						return;
					}
					var struct = [];
					$.each(data, function(){
						struct.push({
							"key": this.guid,
							"values": [
								'<a data-entity="'+pines.safe(this.guid)+'" data-entity-context="com_testimonials_testimonial">'+pines.safe(this.id)+'</a>',
								'<a data-entity="'+pines.safe(this.customer_guid)+'" data-entity-context="com_testimonials_testimonial">'+pines.safe(this.customer_name)+'</a>',
								'<a href="mailto:'+pines.safe(this.email)+'">'+pines.safe(this.email)+'</a>',
								'<a data-entity="'+pines.safe(this.user_guid)+'" data-entity-context="user">'+pines.safe(this.user_name)+'</a>',
								pines.safe(this.location),
								pines.safe(this.city),
								pines.safe(this.state),
								pines.safe(this.creation_date),
								pines.safe(this.status),
								pines.safe(this.rating),
								pines.safe(this.share_allowed),
								pines.safe(this.share_anon),
								pines.safe(this.original),
								pines.safe(this.quotes)
							]
						});
					});
					testimonial_grid.pgrid_add(struct);
				}
			});
		};
		
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'text', load: function(textbox){
					// Display the current sku being searched.
					textbox.keydown(function(e){
						if (e.keyCode == 13)
							submit_search();
					});
					search_box = textbox;
				}},
				{type: 'button', extra_class: 'picon picon-system-search', selection_optional: true, pass_csv_with_headers: true, click: submit_search},
				<?php if (gatekeeper('com_testimonials/newtestimonials')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_testimonials', 'testimonial/edit')); ?>},
				<?php } if (gatekeeper('com_testimonials/edittestimonials')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', selection_optional: true, url: <?php echo json_encode(pines_url('com_testimonials', 'testimonial/edit', array('id' => '__title__'))); ?>},
				<?php } if (gatekeeper('com_testimonials/changestatus')) { ?>
				{type: 'button', text: 'Approve/Disapprove', extra_class: 'picon picon-task-accepted', click: function(e, row){
					testimonial_grid.changestatus_form($(row).attr("title"));
				}},
				{type: 'separator'},
				<?php } if (gatekeeper('com_testimonials/toggletestimonials')) { ?>
				{type: 'button', text: 'Pending', extra_class: 'picon picon-view-refresh', selection_optional: true, url: <?php echo json_encode(pines_url('com_testimonials', 'testimonial/list_reviews', array('type' => 'pending'))); ?>},
				{type: 'button', text: 'Approved', extra_class: 'picon picon-dialog-ok-apply', selection_optional: true, url: <?php echo json_encode(pines_url('com_testimonials', 'testimonial/list_reviews', array('type' => 'approved'))); ?>},
				{type: 'button', text: 'Denied', extra_class: 'picon picon-dialog-close', selection_optional: true, url: <?php echo json_encode(pines_url('com_testimonials', 'testimonial/list_reviews', array('type' => 'denied'))); ?>},
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
		testimonial_grid.pgrid_get_all_rows().pgrid_delete();
		
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
								var tags = form.find(":[name=tags]").val();
								var quotefeedback = form.find(":[name=quotefeedback]").val();
								if (status == "") {
									alert('Please specify the status.');
								} else {
									form.dialog('close');
									// Submit the request status change.
									pines.post(<?php echo json_encode(pines_url('com_testimonials', 'testimonial/changestatus')); ?>, {
										"id": testimonial_id,
										"quotefeedback": quotefeedback,
										"tags": tags,
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
			<th>Original</th>
			<th>Quoted</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
		</tr>
	</tbody>
</table>