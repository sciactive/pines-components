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
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Packages';
$pines->com_pgrid->load();
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_packager', 'editpackage'); ?>'},
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_packager', 'editpackage', array('id' => '__title__')); ?>'},
				{type: 'button', text: 'Make Package', extra_class: 'icon picon_16x16_mimetypes_package-x-generic', url: '<?php echo pines_url('com_packager', 'makepackage', array('id' => '__title__')); ?>'},
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_packager', 'deletepackage', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_packager/list_packages", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#package_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="package_grid">
	<thead>
		<tr>
			<th>Package</th>
			<th>Type</th>
			<th>Component</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->packages as $package) { ?>
		<tr title="<?php echo $package->guid; ?>">
			<td><?php echo $package->name; ?></td>
			<td><?php switch($package->type) {
				case 'component':
					echo 'Component Package';
					break;
				case 'template':
					echo 'Template Package';
					break;
				case 'system':
					echo 'System Package';
					break;
				case 'meta':
					echo 'Meta Package';
					break;
			} ?></td>
			<td><?php if (!in_array($package->type, array('system', 'meta'))) {
				$name = $package->component;
				echo "{$pines->info->$name->name} [{$name} {$pines->info->$name->version}]";
			} else {
				if ($package->type == 'system') {
					echo "{$pines->info->name} [system {$pines->info->version}]";
				} else {
					echo 'N/A';
				}
			} ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>