<?php
/**
 * Lists packages and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Packages';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_packager/package/list'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_packager/newpackage')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo addslashes(pines_url('com_packager', 'package/edit')); ?>'},
				{type: 'button', text: 'Package(s) Wizard', extra_class: 'picon picon-tools-wizard', selection_optional: true, url: '<?php echo addslashes(pines_url('com_packager', 'package/wizard')); ?>'},
				<?php } if (gatekeeper('com_packager/editpackage')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: '<?php echo addslashes(pines_url('com_packager', 'package/edit', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_packager/makepackage')) { ?>
				{type: 'button', text: 'Make Package(s)', extra_class: 'picon picon-package-x-generic', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_packager', 'package/make', array('id' => '__title__'))); ?>', delimiter: ','},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_packager/deletepackage')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_packager', 'package/delete', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'packages',
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_packager/package/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Package</th>
			<th>Type</th>
			<th>Name</th>
			<th>Author</th>
			<th>Component</th>
			<th>Version</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->packages as $package) { ?>
		<tr title="<?php echo (int) $package->guid ?>">
			<td><?php echo htmlspecialchars($package->name); ?></td>
			<td><?php switch($package->type) {
				case 'component':
					echo 'Component';
					break;
				case 'template':
					echo 'Template';
					break;
				case 'system':
					echo 'System';
					break;
				case 'meta':
					echo 'Meta';
					break;
			} ?></td>
			<td><?php switch($package->type) {
				case 'component':
				case 'template':
					$component = $package->component;
					echo htmlspecialchars($pines->info->$component->name);
					break;
				case 'system':
					echo htmlspecialchars($pines->info->name);
					break;
				case 'meta':
					echo htmlspecialchars($package->meta['name']);
					break;
			} ?></td>
			<td><?php switch($package->type) {
				case 'component':
				case 'template':
					$component = $package->component;
					echo htmlspecialchars($pines->info->$component->author);
					break;
				case 'system':
					echo htmlspecialchars($pines->info->author);
					break;
				case 'meta':
					echo htmlspecialchars($package->meta['author']);
					break;
			} ?></td>
			<td><?php switch($package->type) {
				case 'component':
				case 'template':
					echo htmlspecialchars($package->component);
					break;
				case 'system':
					echo 'system';
					break;
				case 'meta':
					echo 'N/A';
					break;
			} ?></td>
			<td><?php switch($package->type) {
				case 'component':
				case 'template':
					$component = $package->component;
					echo htmlspecialchars($pines->info->$component->version);
					break;
				case 'system':
					echo htmlspecialchars($pines->info->version);
					break;
				case 'meta':
					echo htmlspecialchars($package->meta['version']);
					break;
			} ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>