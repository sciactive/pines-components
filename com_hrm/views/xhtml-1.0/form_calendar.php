<?php
/**
 * Display a form to view schedules for different company divisions/locations.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Actions';
$pines->com_pgrid->load();
$pines->com_jstree->load();
?>
<script type='text/javascript'>
	// <![CDATA[
	<?php if (gatekeeper('com_hrm/editcalendar')) { ?>
	// Change the branch / division of the company.
	pines.com_hrm_select_branch = function(){
		$.ajax({
			url: "<?php echo pines_url('com_hrm', 'locationselect'); ?>",
			type: "POST",
			dataType: "html",
			data: {"location": "<?php echo $this->location; ?>"},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the company schedule form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Company Schedule\" />");
				form.dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					open: function(){
						form.html(data+"<br />").dialog("option", "position", "center");
					},
					close: function(){
						form.remove();
					},
					buttons: {
						"Update Schedule": function(){
							form.dialog('close');
							var schedule_location = form.find(":input[name=location]").val();
							pines.post("<?php echo pines_url('com_hrm', 'editcalendar'); ?>", { "location": schedule_location });
						}
					}
				});
			}
		});
	};
	// Create a new event.
	pines.com_hrm_new_event = function(){
		$.ajax({
			url: "<?php echo pines_url('com_hrm', 'editevent'); ?>",
			type: "POST",
			dataType: "html",
			data: {"location": "<?php echo $this->location; ?>"},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the new event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Add a New Event\" />");
				form.dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					open: function(){
						form.html(data+"<br />").dialog("option", "position", "center");
					},
					close: function(){
						form.remove();
					},
					buttons: {
						"Add Event": function(){
							form.dialog('close');
							pines.post("<?php echo pines_url('com_hrm', 'saveevent'); ?>", {
								employee: form.find(":input[name=employee]").val(),
								event_label: form.find(":input[name=event_label]").val(),
								event_date: form.find(":input[name=start]").val(),
								event_enddate: form.find(":input[name=end]").val(),
								all_day: form.find(":input[name=all_day]").val(),
								event_start: form.find(":input[name=time_start]").val(),
								event_end: form.find(":input[name=time_end]").val(),
								location: form.find(":input[name=location]").val()
							});
						}
					}
				});
			}
		});
	};
	// Edit an existing event.
	pines.com_hrm_edit_event = function(){
		$.ajax({
			url: "<?php echo pines_url('com_hrm', 'editevent'); ?>",
			type: "POST",
			dataType: "html",
			data: {id: "<?php echo $this->entity->guid; ?>"},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the new event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Editing "+"<?php echo $this->entity->label; ?>"+"\" />");
				form.dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					open: function(){
						form.html(data+"<br />").dialog("option", "position", "center");
					},
					close: function(){
						form.remove();
					},
					buttons: {
						"Save Event": function(){
							form.dialog('close');
							pines.post("<?php echo pines_url('com_hrm', 'saveevent'); ?>",
							{
								id: form.find(":input[name=id]").val(),
								employee: form.find(":input[name=employee]").val(),
								event_label: form.find(":input[name=event_label]").val(),
								event_date: form.find(":input[name=event_date]").val(),
								event_enddate: form.find(":input[name=event_enddate]").val(),
								all_day: form.find(":input[name=all_day]:checked").val(),
								event_start: form.find(":input[name=event_start]").val(),
								event_end: form.find(":input[name=event_end]").val(),
								location: form.find(":input[name=location]").val()
							});
						}
					}
				});
			}
		});
	};
	// Show and approve the requested time off.
	pines.com_hrm_time_off = function(){
		$.ajax({
			url: "<?php echo pines_url('com_hrm', 'timeoff/review'); ?>",
			type: "POST",
			dataType: "html",
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the new event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Pending Requests for Time Off\" />");
				form.dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					width: 700,
					open: function(){
						form.html(data+"<br />").dialog("option", "position", "center");
					},
					close: function(){
						form.remove();
					}
				});
			}
		});
	};
	<?php if (isset($this->entity)) { ?>
	// Edit the event if there is one to be edited.
	pines.com_hrm_edit_event();
	<?php } } if (gatekeeper('com_hrm/clock')) { ?>
	// Request time off.
	pines.com_hrm_time_off_form = function(rto_id){
		$.ajax({
			url: "<?php echo pines_url('com_hrm', 'timeoff/request'); ?>",
			type: "POST",
			dataType: "html",
			data: {id: rto_id},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the new event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Time Off Request for "+"<?php echo $_SESSION['user']->name; ?>"+"\" />");
				form.dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					open: function(){
						form.html(data+"<br />").dialog("option", "position", "center");
					},
					close: function(){
						form.remove();
					},
					buttons: {
						"Submit Request": function(){
							form.dialog('close');
							pines.post("<?php echo pines_url('com_hrm', 'timeoff/save'); ?>",
							{
								id: form.find(":input[name=id]").val(),
								employee: form.find(":input[name=employee]").val(),
								reason: form.find(":input[name=reason]").val(),
								all_day: form.find(":input[name=all_day]").attr('checked'),
								start: form.find(":input[name=start]").val(),
								end: form.find(":input[name=end]").val(),
								time_start: form.find(":input[name=time_start]").val(),
								time_end: form.find(":input[name=time_end]").val()
							});
						}
					}
				});
			}
		});
	};
	<?php if (isset($this->rto)) { ?>
	// Edit the event if there is one to be edited.
	pines.com_hrm_time_off_form(<?php echo $this->rto->guid; ?>);
	<?php } } ?>
	// ]]>
</script>
<div style="padding: 1em;">
	<?php if (gatekeeper('com_hrm/editcalendar')) { ?>
	<div style="margin-bottom: 1em;">
		<input style="width: 100%;" class="ui-state-default ui-priority-primary ui-corner-all" type="button" value="Change Branch" onclick="pines.com_hrm_select_branch();" />
	</div>
	<div style="margin-bottom: 1em;">
		<input style="width: 100%;" class="ui-state-default ui-priority-primary ui-corner-all" type="button" value="New Event" onclick="pines.com_hrm_new_event();" />
	</div>
	<div style="margin-bottom: 1em;">
		<input style="width: 100%;" class="ui-state-default ui-priority-primary ui-corner-all" type="button" value="RTO" onclick="pines.com_hrm_time_off();" />
	</div>
	<?php } if (gatekeeper('com_hrm/clock')) { ?>
	<div>
		<input style="width: 100%;" class="ui-state-default ui-priority-primary ui-corner-all" type="button" value="Request Time Off" onclick="pines.com_hrm_time_off_form();" />
	</div>
	<?php } ?>
</div>