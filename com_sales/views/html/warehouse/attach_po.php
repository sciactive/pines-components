<?php
/**
 * Provides a form for attaching a PO to warehouse items.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Attach a PO';
$pines->com_pgrid->load();
?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_sales', 'warehouse/attachposave')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$("#p_muid_po_grid").pgrid({
				pgrid_view_height: "300px",
				pgrid_multi_select: false,
				pgrid_click: function(e, rows){
					if (rows.length < 1) {
						$("#p_muid_selected").html("Nothing selected.");
						$("#p_muid_po").val("");
						return;
					}
					$("#p_muid_selected").html("PO "+rows.pgrid_get_value(1)+" is selected.");
					$("#p_muid_po").val(rows.attr("title"));
				}
			});
		});
		// ]]>
	</script>
	<div class="pf-element">
		The following is a list of POs which contain all of these items:
		<ul>
			<?php foreach ($this->products as $cur_product) { ?>
			<li><?php echo htmlspecialchars("{$cur_product->sku}: $cur_product->name"); ?></li>
			<?php } ?>
		</ul>
		Please select one, and it will be attached to the selected warehouse
		orders.
	</div>
	<div class="pf-element pf-full-width">
		<table id="p_muid_po_grid">
			<thead>
				<tr>
					<th>PO Number</th>
					<th>Reference Number</th>
					<th>Destination</th>
					<th>Shipper</th>
					<th>ETA</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->pos as $cur_po) { ?>
				<tr title="<?php echo $cur_po->guid; ?>">
					<td><?php echo htmlspecialchars($cur_po->po_number); ?></td>
					<td><?php echo htmlspecialchars($cur_po->reference_number); ?></td>
					<td><?php echo htmlspecialchars("{$cur_po->destination->name} [{$cur_po->destination->groupname}]"); ?></td>
					<td><?php echo htmlspecialchars($cur_po->shipper->name); ?></td>
					<td><?php echo format_date($cur_po->eta, 'date_sort'); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="pf-element">
		<div id="p_muid_selected">Nothing selected yet.</div>
		<input type="hidden" name="po" id="p_muid_po" value="" />
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'warehouse/pending')); ?>');" value="Cancel" />
	</div>
</form>