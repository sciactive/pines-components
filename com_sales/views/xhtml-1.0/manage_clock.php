<?php
/**
 * Lists employees and provides functions to manipulate their timeclock.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Employee Timeclock';
?>
<script type="text/javascript">
	// <![CDATA[

	$(function(){
		function format_time(timestamp) {
			var d = new Date();
			d.setTime(timestamp * 1000);
			return d.toLocaleString();
		}

		$("#timeclock_grid .time").each(function(){
			$(this).html(format_time($(this).html()));
		});

		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'View', extra_class: 'icon picon_16x16_actions_document-new', double_click: true, url: '<?php echo pines_url('com_sales', 'viewclock', array('id' => '#title#')); ?>'},
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', url: '<?php echo pines_url('com_sales', 'editclock', array('id' => '#title#')); ?>'},
				{type: 'button', text: 'Clock In/Out', extra_class: 'icon picon_16x16_stock_generic_stock_timer', multi_select: true, click: function(e, rows){
					var loader;
					rows.each(function(){
						var cur_row = $(this);
						$.ajax({
							url: "<?php echo pines_url('com_sales', 'clock'); ?>",
							type: "POST",
							dataType: "json",
							data: {"id": cur_row.pgrid_export_rows()[0].key},
							beforeSend: function(){
								if (!loader)
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
								cur_row.pgrid_set_value(3, data[1].status);
								cur_row.pgrid_set_value(4, format_time(data[1].time));
							}
						});
					});
				}},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'timeclock',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 'col_1',
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('system', 'pgrid_save_state'); ?>", {view: "com_sales/manage_clock", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#timeclock_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="timeclock_grid">
	<thead>
		<tr>
			<th>Name</th>
			<th>Username</th>
			<th>Status</th>
			<th>Time</th>
			<th>Time Today</th>
			<th>Time Sum</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->users as $user) { ?>
		<tr title="<?php echo $user->guid; ?>">
			<td><?php echo $user->name; ?></td>
			<td><?php echo $user->username; ?></td>
			<td><?php echo $user->com_sales->timeclock[count($_SESSION['user']->com_sales->timeclock) - 1]['status']; ?></td>
			<td class="time"><?php echo $user->com_sales->timeclock[count($_SESSION['user']->com_sales->timeclock) - 1]['time']; ?></td>
			<td><?php echo round($user->com_sales->time_sum(strtotime('Today 12:00 AM')) / (60 * 60), 2).' hours'; ?></td>
			<td><?php echo round($user->com_sales->time_sum() / (60 * 60), 2).' hours'; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>