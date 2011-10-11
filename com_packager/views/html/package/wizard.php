<?php
/**
 * New package wizard to create packages from components.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'New Package Wizard';
$this->note = 'This wizard will create packages from enabled components and templates.';
$pines->com_pgrid->load();
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$("#p_muid_form table").pgrid({
			pgrid_paginate: false,
			pgrid_select: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'All', extra_class: 'picon picon-checkbox', selection_optional: true, click: function(){
					$('input:checkbox', '#p_muid_form').attr('checked', 'checked');
				}},
				{type: 'button', text: 'Components', extra_class: 'picon picon-checkbox', selection_optional: true, click: function(){
					$('input:checkbox', '#p_muid_form .type_component').attr('checked', 'checked');
				}},
				{type: 'button', text: 'Templates', extra_class: 'picon picon-checkbox', selection_optional: true, click: function(){
					$('input:checkbox', '#p_muid_form .type_template').attr('checked', 'checked');
				}},
				{type: 'button', text: 'Visible', extra_class: 'picon picon-checkbox', selection_optional: true, click: function(){
					$('input:checkbox:visible', '#p_muid_form').attr('checked', 'checked');
				}},
				{type: 'separator'},
				{type: 'button', text: 'Uncheck All', extra_class: 'picon picon-list-remove', selection_optional: true, click: function(){
					$('input:checkbox', '#p_muid_form').removeAttr('checked');
				}}
			],
			pgrid_sort_col: 2,
			pgrid_sort_ord: 'asc'
		});
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_packager', 'package/wizardsave')); ?>">
	<div class="pf-element pf-heading">
		<h1>Choose Packages</h1>
	</div>
	<div class="pf-element pf-full-width">
		<table>
			<thead>
				<tr>
					<th></th>
					<th>Component/Template</th>
					<th>Name</th>
					<th>Author</th>
					<th>Version</th>
				</tr>
			</thead>
			<tbody style="">
				<?php foreach ($this->components as $cur_component => $cur_info) { ?>
				<tr class="type_<?php echo htmlspecialchars($cur_info->type); ?>" title="<?php echo ($cur_info->already_exists ? 'This package already exists.' : ($cur_info->disabled ? 'This component is disabled.' : '')); ?>">
					<td>
						<?php if (!$cur_info->already_exists && !$cur_info->disabled) { ?>
						<input type="checkbox" name="packages[]" id="p_muid_<?php echo htmlspecialchars($cur_component); ?>" value="<?php echo htmlspecialchars($cur_component); ?>" />
						<?php } ?>
					</td>
					<td><label for="p_muid_<?php echo htmlspecialchars($cur_component); ?>"><?php echo htmlspecialchars($cur_component); ?></label></td>
					<td><label for="p_muid_<?php echo htmlspecialchars($cur_component); ?>"><?php echo htmlspecialchars($cur_info->name); ?></label></td>
					<td><label for="p_muid_<?php echo htmlspecialchars($cur_component); ?>"><?php echo htmlspecialchars($cur_info->author); ?></label></td>
					<td><label for="p_muid_<?php echo htmlspecialchars($cur_component); ?>"><?php echo htmlspecialchars($cur_info->version); ?></label></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Create Packages" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_packager', 'package/list')); ?>');" value="Cancel" />
	</div>
</form>