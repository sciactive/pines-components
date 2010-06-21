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
$this->title = 'Timeclock';
$entry_count = count($this->entity->timeclock);
?>
<div class="pf-form" id="p_muid_timeclock">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$("#p_muid_button").click(function(){
				var loader;
				$.ajax({
					url: "<?php echo pines_url('com_hrm', 'employee/timeclock/clock'); ?>",
					type: "POST",
					dataType: "json",
					data: {"id": "self"},
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
						if (!data) {
							alert("No data was returned.");
							return;
						}
						if (!data[0]) {
							pines.error("There was an error saving the change to the database.");
							return;
						}
						$("#p_muid_status").html(data[1].status);
						$("#p_muid_time").html(data[1].time);
						if (data[1].status == "in") {
							$("#p_muid_button .p_muid_button_text").html("Clock Out");
						} else {
							$("#p_muid_button .p_muid_button_text").html("Clock In");
						}
						$("#p_muid_timeclock").effect("highlight");
					}
				});
			});
		});
		// ]]>
	</script>
	<div class="pf-element">
		<span class="pf-label"><?php echo $this->entity->name; ?></span>
		<span class="pf-note"><span>Status: </span><span id="p_muid_status"><?php echo empty($this->entity->timeclock) ? 'out' : $this->entity->timeclock[$entry_count - 1]['status']; ?></span></span>
		<span class="pf-note"><span>Time: </span><span id="p_muid_time"><?php echo empty($this->entity->timeclock) ? 'No Timeclock Data' : format_date($this->entity->timeclock[$entry_count - 1]['time']); ?></span></span>
	</div>
	<div class="pf-element pf-full-width">
		<button class="pf-field ui-state-default ui-corner-all" id="p_muid_button" type="button" style="float: right;"><span class="p_muid_button_text"><?php echo $this->entity->timeclock[$entry_count - 1]['status'] == 'in' ? 'Clock Out' : 'Clock In'; ?></span></button>
	</div>
</div>