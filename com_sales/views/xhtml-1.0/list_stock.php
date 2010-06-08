<?php
/**
 * Lists stock and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Stock';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_stock'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/receive')) { ?>
				{type: 'button', text: 'Receive', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo pines_url('com_sales', 'receive'); ?>'},
				<?php } if (gatekeeper('com_sales/managestock')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: '<?php echo pines_url('com_sales', 'editstock', array('id' => '__title__')); ?>'},
				<?php } if (gatekeeper('com_sales/managestock')) { ?>
				{type: 'button', text: 'Transfer', extra_class: 'picon picon-go-jump', multi_select: true, url: '<?php echo pines_url('com_sales', 'newtransfer', array('id' => '__title__')); ?>', delimiter: ','},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'stock',
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_sales/list_stock", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#stock_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="stock_grid">
	<thead>
		<tr>
			<th>SKU</th>
			<th>Product</th>
			<th>Serial</th>
			<th>Vendor</th>
			<th>Location</th>
			<th>Cost</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->stock as $stock) {
		if (!$this->all) {
			if (!in_array($stock->status, array('available', 'unavailable', 'sold_pending')))
				continue;
		} ?>
		<tr title="<?php echo $stock->guid; ?>">
			<td><?php echo $stock->product->sku; ?></td>
			<td><?php echo $stock->product->name; ?></td>
			<td><?php echo $stock->serial; ?></td>
			<td><?php echo $stock->vendor->name; ?></td>
			<td><?php echo "{$stock->location->name} [{$stock->location->groupname}]"; ?></td>
			<td><?php echo $stock->cost; ?></td>
			<td><?php echo ucwords($stock->status); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>