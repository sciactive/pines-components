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
$this->title = 'Schedule Actions';
$pines->com_jstree->load();
?>
<script type='text/javascript'>
	// <![CDATA[
	// Change the branch / division of the company.
	function select_branch() {
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
					height: 275,
					modal: true,
					open: function(){
						form.html(data);
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
	}
	// Create a new event.
	function new_event() {
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
					height: 500,
					modal: true,
					open: function(){
						form.html(data);
					},
					close: function(){
						form.remove();
					},
					buttons: {
						"Add Event": function(){
							form.dialog('close');
							pines.post("<?php echo pines_url('com_hrm', 'saveevent'); ?>",
							{
								employee: form.find(":input[name=employee]").val(),
								event_label: form.find(":input[name=event_label]").val(),
								event_date: form.find(":input[name=event_date]").val(),
								event_enddate: form.find(":input[name=event_enddate]").val(),
								all_day: form.find(":input[name=all_day]").val(),
								event_start: form.find(":input[name=event_start]").val(),
								event_end: form.find(":input[name=event_end]").val(),
								location: form.find(":input[name=location]").val()
							});
						}
					}
				});
			}
		});
	}
	// Edit an exisiting event.
	function edit_event() {
		$.ajax({
			url: "<?php echo pines_url('com_hrm', 'editevent'); ?>",
			type: "POST",
			dataType: "html",
			data: {id: "<?php echo $this->event->guid; ?>"},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the new event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Editing "+"<?php echo $this->event->label; ?>"+"\" />");
				form.dialog({
					bgiframe: true,
					autoOpen: true,
					height: 500,
					modal: true,
					open: function(){
						form.html(data);
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
								all_day: form.find(":input[name=all_day]").val(),
								event_start: form.find(":input[name=event_start]").val(),
								event_end: form.find(":input[name=event_end]").val(),
								location: form.find(":input[name=location]").val()
							});
						}
					}
				});
			}
		});
	}
	<?php if (isset($this->event)) { ?>
	// Edit the event if there is one to be edited.
	edit_event();
	<?php } ?>
	// ]]>
</script>
<div style="padding: 1em;">
	<div style="margin-bottom: 1em;">
		<input style="width: 100%;" class="ui-state-default ui-priority-primary ui-corner-all" type="button" value="Change Branch" onclick="select_branch();" />
	</div>
	<div>
		<input style="width: 100%;" class="ui-state-default ui-priority-primary ui-corner-all" type="button" value="New Event" onclick="new_event();" />
	</div>
</div>