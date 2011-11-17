<?php
/**
 * Displays a log.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Displaying Log View of File: '.htmlspecialchars($pines->config->com_logger->path);
preg_match_all('/^(\d{4}-\d{2}-\d{2}T[\d:-]+): ([a-z]+): (com_\w+)?, ([^:]+)?: ([\d.]+)?(.*?)? ?\(?(\d+)?\)?: (.*)$/mi', $this->log, $matches, PREG_SET_ORDER);
$pines->icons->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_logger/view']);
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'Reload', title: 'Reload', extra_class: 'picon picon-view-refresh', selection_optional: true, click: function(e){
					log_grid.pgrid_import_state({pgrid_filter: ""});
				}},
				{type: 'button', text: 'Debug', title: 'Debug', extra_class: 'picon picon-help-hint', selection_optional: true, click: function(e){
					log_grid.pgrid_import_state({pgrid_filter: "p_muid_debug"});
				}},
				{type: 'button', text: 'Info', title: 'Info', extra_class: 'picon picon-dialog-information', selection_optional: true, click: function(e){
					log_grid.pgrid_import_state({pgrid_filter: "p_muid_info"});
				}},
				{type: 'button', text: 'Notice', title: 'Notice', extra_class: 'picon picon-view-pim-notes', selection_optional: true, click: function(e){
					log_grid.pgrid_import_state({pgrid_filter: "p_muid_notice"});
				}},
				{type: 'button', text: 'Error', title: 'Error', extra_class: 'picon picon-dialog-error', selection_optional: true, click: function(e){
					log_grid.pgrid_import_state({pgrid_filter: "p_muid_error"});
				}},
				{type: 'button', text: 'Warning', title: 'Warning', extra_class: 'picon picon-dialog-warning', selection_optional: true, click: function(e){
					log_grid.pgrid_import_state({pgrid_filter: "p_muid_warning"});
				}},
				{type: 'button', text: 'Fatal', title: 'Fatal', extra_class: 'picon picon-script-error', selection_optional: true, click: function(e){
					log_grid.pgrid_import_state({pgrid_filter: "p_muid_fatal"});
				}},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'requests',
						content: rows
					});
				}}
			],
			pgrid_hidden_cols: [9],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_smartflights/list_requests", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var log_grid = $("#p_muid_grid").pgrid(cur_options);
	});
	// ]]>
</script>

<?php 
	$debugs = array();
	$infos = array();
	$notices = array();
	$warnings = array();
	$errors = array();
	$fatals = array();
	foreach($matches as $match) {
		switch ($match[2]){
			case "debug":
				$debugs[] = $match[2];
				break;
			case "info":
				$infos[] = $match[2];
				break;
			case "notice":
				$notices[] = $match[2];
				break;
			case "warning":
				$warnings[] = $match[2];
				break;
			case "error":
				$errors[] = $match[2];
				break;
			case "fatal":
				$fatals[] = $match[2];
				break;
		}
	}
?>
<div class="pf-form">
	<div class="pf-element pf-heading">
		<h1>Total Log Entries: <strong><?php echo count($matches); ?></strong></h1></span>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-label" style="width:180px;padding-right:100px;">
			<span><span class="picon picon-help-hint" style="padding-left:16px;background-repeat:no-repeat;line-height:16px;"> Total Debug Entries: <strong><span style="float:right;"><?php echo count($debugs); ?></span></strong></span><br/>
			<span><span class="picon picon-dialog-information" style="padding-left:18px;background-repeat:no-repeat;line-height:16px;"> Total Info Entries: <strong><span style="float:right;"><?php echo count($infos); ?></span></strong></span><br/>
			<span><span class="picon picon-view-pim-notes" style="padding-left:18px;background-repeat:no-repeat;line-height:16px;"> Total Notice Entries: <strong><span style="float:right;"><?php echo count($notices); ?></span></strong></span><br/><br/>
		</div>
		<div class="pf-group" style="width:315px;">
			<span><span class="picon picon-dialog-warning" style="padding-left:18px;background-repeat:no-repeat;line-height:16px;"> Total Warning Entries</span>: <strong><span style="float:right;"><?php echo count($warnings); ?></span></strong></span><br/>
			<span><span class="picon picon-dialog-error" style="padding-left:18px;background-repeat:no-repeat;line-height:16px;"> Total Error Entries: <strong><span style="float:right;"><?php echo count($errors); ?></span></strong></span><br/>
			<span><span class="picon picon-script-error" style="padding-left:18px;background-repeat:no-repeat;line-height:16px;"> Total Fatal Entries: <strong><span style="float:right;"><?php echo count($fatals); ?></span></strong></span><br/><br/>
		</div>
	</div>
</div>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Date / Time</th>
			<th>Level</th>
			<th>Component</th>
			<th>Action</th>
			<th>IP Address</th>
			<th>User</th>
			<th>User ID</th>
			<th>Message</th>
			<th>Filter Helper</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($matches as $match) { ?>
		<tr class="p_muid_normal">
			<td><?php echo htmlspecialchars(format_date(strtotime($match[1]))); ?></td>
			<td><?php
				switch ($match[2]){
					case "debug":
						echo '<span class="picon-help-hint" style="display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">'.htmlspecialchars($match[2]).'</span>';
						break;
					case "info":
						echo '<span class="picon-dialog-information" style="display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">'.htmlspecialchars($match[2]).'</span>';
						break;
					case "notice":
						echo '<span class="picon-view-pim-notess" style="display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">'.htmlspecialchars($match[2]).'</span>';
						break;
					case "warning":
						echo '<span class="picon-dialog-warning" style="display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">'.htmlspecialchars($match[2]).'</span>';
						break;
					case "error":
						echo '<span class="picon-dialog-error" style="display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">'.htmlspecialchars($match[2]).'</span>';
						break;
					case "fatal":
						echo '<span class="picon-script-error" style="display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">'.htmlspecialchars($match[2]).'</span>';
						break;
				}
			?></td>
			<td><?php echo htmlspecialchars($match[3]); ?></td>
			<td><?php echo htmlspecialchars($match[4]); ?></td>
			<td><?php echo htmlspecialchars($match[5]); ?></td>
			<td><?php echo htmlspecialchars($match[6]); ?></td>
			<td><?php echo htmlspecialchars($match[7]); ?></td>
			<td><?php echo htmlspecialchars($match[8]); ?></td>
			<td><?php echo htmlspecialchars('p_muid_'.$match[2]); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>