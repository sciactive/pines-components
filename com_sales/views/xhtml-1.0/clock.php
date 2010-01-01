<?php
/**
 * Allows the user to clock in and out of the timeclock.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Timeclock';
$_SESSION['user']->refresh();
?>
<div class="pform" id="timeclock">
	<script type="text/javascript">
		// <![CDATA[
		$(function(){
			$("#timeclock button").click(function(){
				var loader;
				$.ajax({
					url: "<?php echo pines_url('com_sales', 'clock'); ?>",
					type: "POST",
					dataType: "json",
					data: {"id": "self"},
					beforeSend: function(){
						loader = pines.alert('Communicating with server...', 'Timeclock', 'icon picon_16x16_animations_throbber', {pnotify_hide: false, pnotify_history: false});
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
						$("#timeclock .status").html(data[1].status);
						$("#timeclock .time").html(data[1].time);
						if (data[1].status == "in") {
							$("#timeclock button").html("Clock Out");
						} else {
							$("#timeclock button").html("Clock In");
						}
						$("#timeclock").effect("highlight");
					}
				});
			});
		});
		// ]]>
	</script>
	<div class="element">
		<span class="label"><span>User: </span><span><?php echo $_SESSION['user']->name; ?></span></span>
		<span class="note"><span>Status: </span><span class="status"><?php echo $_SESSION['user']->com_sales->timeclock[count($_SESSION['user']->com_sales->timeclock) - 1]['status']; ?></span></span>
		<span class="note"><span>Time: </span><span class="time"><?php echo pines_date_format($_SESSION['user']->com_sales->timeclock[count($_SESSION['user']->com_sales->timeclock) - 1]['time']); ?></span></span>
	</div>
	<div class="element full_width">
		<button type="button" class="field ui-state-default ui-corner-all" style="float: right;" onmouseover="$(this).addClass('ui-state-hover');" onmouseout="$(this).removeClass('ui-state-hover');"><?php echo $_SESSION['user']->com_sales->timeclock[count($_SESSION['user']->com_sales->timeclock) - 1]['status'] == 'in' ? 'Clock Out' : 'Clock In'; ?></button>
	</div>
</div>