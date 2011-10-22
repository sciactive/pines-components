<?php
/**
 * Allows the user to clock in and out of the timeclock.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
if (empty($this->title))
	$this->title = 'Timeclock';

if (!isset($this->entity)) {
	pines_session('write');
	if (!isset($_SESSION['user']->guid)) {
		pines_session('close');
		$this->detach();
		return;
	}
	$_SESSION['user']->refresh();
	pines_session('close');
	$user = com_hrm_employee::factory($_SESSION['user']->guid);
	$this->entity = $user->timeclock;
}

if (!isset($this->entity->user->guid) || !isset($this->entity)) {
	$this->detach();
	return;
}
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_timeclock .p_muid_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	#p_muid_timeclock button {
		padding: 0.2em;
	}
	#p_muid_timeclock button .ui-button-text {
		padding: 0;
	}
	/* ]]> */
</style>
<div class="pf-form" id="p_muid_timeclock">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			var do_clock = function(pin){
				var loader;
				$.ajax({
					url: "<?php echo addslashes(pines_url('com_hrm', 'employee/timeclock/clock')); ?>",
					type: "POST",
					dataType: "json",
					data: {"id": "self", "pin": pin, "comments": $("#p_muid_comments").val()},
					beforeSend: function(){
						loader = $.pnotify({
							pnotify_title: 'Timeclock',
							pnotify_text: 'Communicating with server...',
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
						pines.error("An error occured while communicating with the server:\n"+XMLHttpRequest.status+": "+textStatus);
					},
					success: function(data){
						if (data === undefined) {
							alert("No data was returned.");
							return;
						}
						if (data === "pin") {
							alert("Invalid PIN.");
							return;
						}
						if (data === false) {
							pines.error("There was an error saving the change to the database.");
							return;
						}
						if (data) {
							$("#p_muid_status").html("Clocked in.");
							$("#p_muid_comments").slideDown();
							$(".p_muid_button_text", "#p_muid_button").html("Clock Out");
						} else {
							$("#p_muid_status").html("Clocked out.");
							$("#p_muid_comments").slideUp();
							$(".p_muid_button_text", "#p_muid_button").html("Clock In");
						}
						$("#p_muid_timeclock").effect("highlight");
					}
				});
			};

			$("#p_muid_button").click(function(){
				<?php if ($pines->config->com_hrm->timeclock_verify_pin && !empty($_SESSION['user']->pin)) { ?>
				var dialog = $("<div></div>", {
					"title": "Please Verify Your PIN",
					"html": '<div class="pf-form"><div class="pf-element"><label><span class="pf-label">PIN</span><input type="password" class="pf-field ui-widget-content ui-corner-all" /></label></div></div><br />'
				}).dialog({
					modal: true,
					open: function(){
						$(this).find("input[type=password]").focus().keypress(function(e){
							if (e.keyCode == 13)
								dialog.dialog("option", "buttons").Done();
						});
					},
					buttons: {
						"Done": function(){
							var pin = dialog.find("input[type=password]").val();
							if (pin == "") {
								alert("Please enter a PIN.");
								return;
							}
							do_clock(pin);
							dialog.dialog("close").remove();
						}
					}
				});
				<?php } else { ?>
				do_clock();
				<?php } ?>
			});
		});

		var p_muid_rto_form;
		// Request time off.
		pines.com_hrm_time_off_form = function(rto_id){
			if (rto_id > 0)
				p_muid_rto_form.remove();
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_hrm', 'timeoff/request')); ?>",
				type: "POST",
				dataType: "html",
				data: {id: rto_id},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the time off form:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (data == "")
						return;
					p_muid_rto_form = $("<div title=\"Time Off Request for "+"<?php echo htmlspecialchars($_SESSION['user']->name); ?>"+"\"></div>");
					p_muid_rto_form.dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
						open: function(){
							p_muid_rto_form.html(data+"<br />").dialog("option", "position", "center");
						},
						close: function(){
							p_muid_rto_form.remove();
						},
						buttons: {
							"Submit Request": function(){
								p_muid_rto_form.dialog('close');
								$.ajax({
									url: "<?php echo addslashes(pines_url('com_hrm', 'timeoff/save')); ?>",
									type: "POST",
									dataType: "json",
									data: {
										id: p_muid_rto_form.find(":input[name=id]").val(),
										employee: p_muid_rto_form.find(":input[name=employee]").val(),
										reason: p_muid_rto_form.find(":input[name=reason]").val(),
										all_day: !!p_muid_rto_form.find(":input[name=all_day]").attr('checked'),
										start: p_muid_rto_form.find(":input[name=start]").val(),
										end: p_muid_rto_form.find(":input[name=end]").val(),
										time_start: p_muid_rto_form.find(":input[name=time_start]").val(),
										time_end: p_muid_rto_form.find(":input[name=time_end]").val()
									},
									error: function(XMLHttpRequest, textStatus){
										pines.error("An error occured while trying to submit the time off request:\n"+XMLHttpRequest.status+": "+textStatus);
									},
									success: function(successful){
										if (!successful) {
											pines.error("Your time off request was unsuccessful");
											return;
										}
										alert("Time Off Request Submitted");
									}
								});
							}
						}
					});
				}
			});
		};
		// ]]>
	</script>
	<div class="pf-element">
		<span class="pf-label"><?php echo htmlspecialchars($this->entity->user->name); ?></span>
		<span class="pf-note"><span>Status: </span><span id="p_muid_status"><?php echo $this->entity->clocked_in_time() ? 'Clocked in since '.format_date($this->entity->clocked_in_time(), 'full_short').'.' : 'Clocked out.'; ?></span></span>
	</div>
	<div class="pf-element" id="p_muid_comments" style="display: <?php echo $this->entity->clocked_in_time() ? 'block' : 'none'; ?>;">
		<label><span class="pf-label">Comments</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="comments" size="7" /></label>
	</div>
	<div class="pf-element pf-full-width">
		<button class="ui-state-default ui-corner-all" type="button" style="float: right;" onclick="pines.com_hrm_time_off_form();" title="Request Time Off"><span class="p_muid_btn picon picon-view-calendar-upcoming-events"></span></button>
		<button class="pf-field ui-state-default ui-corner-all" id="p_muid_button" type="button" style="float: right;"><span class="p_muid_button_text"><?php echo $this->entity->clocked_in_time() ? 'Clock Out' : 'Clock In'; ?></span></button>
	</div>
</div>