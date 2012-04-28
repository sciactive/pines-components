<?php
/**
 * PO entity helper.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$module = new module('com_entityhelper', 'default_helper');
$module->render = $this->render;
$module->entity = $this->entity;
echo $module->render();

if ($this->render == 'body' && gatekeeper('com_sales/listpos')) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Properties</h3>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr>
				<td style="font-weight:bold;">PO Number</td>
				<td><?php echo htmlspecialchars($this->entity->po_number); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Reference Number</td>
				<td><?php echo htmlspecialchars($this->entity->reference_number); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Vendor</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->vendor->guid); ?>" data-entity-context="com_sales_vendor"><?php echo htmlspecialchars($this->entity->vendor->name); ?></a></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Destination</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->destination->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars(ucwords($this->entity->destination->groupname)); ?></a></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Shipper</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->shipper->guid); ?>" data-entity-context="com_sales_shipper"><?php echo htmlspecialchars($this->entity->shipper->name); ?></a></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">ETA</td>
				<td><?php echo ($this->entity->eta ? htmlspecialchars(format_date($this->entity->eta, 'date_sort')) : ''); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Status</td>
				<td><?php echo $this->entity->final ? ($this->entity->finished ? 'Received' : (empty($this->entity->received) ? 'Not Received' : 'Partially Received')) : 'Not Committed'; ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Products</td>
				<td><?php
				$names = array();
				foreach ((array) $this->entity->products as $cur_product) {
					$names[] = '<a data-entity="'.htmlspecialchars($cur_product['entity']->guid).'" data-entity-context="com_sales_product">'.htmlspecialchars($cur_product['entity']->name).' ['.htmlspecialchars($cur_product['entity']->sku).']</a>';
				}
				echo implode(', ', $names);
				?></td>
			</tr>
		</tbody>
	</table>
</div>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">PO Products</h3>
	<table class="table table-bordered" style="clear:both;">
		<thead>
			<th>SKU</th>
			<th>Product</th>
			<th>Quantity</th>
			<?php if ($this->entity->final) { ?>
			<th>Received</th>
			<?php } ?>
			<th>Unit Cost</th>
			<th>Line Total</th>
		</thead>
		<tbody>
			<?php foreach ((array) $this->entity->products as $cur_product) { ?>
			<tr>
				<td><?php echo htmlspecialchars($cur_product['entity']->sku); ?></td>
				<td><a data-entity="<?php echo htmlspecialchars($cur_product['entity']->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_product['entity']->name); ?></a></td>
				<td><?php echo htmlspecialchars($cur_product['quantity']); ?></td>
				<?php if ($this->entity->final) { ?>
				<td>
					<?php
					$all_received = (array) $this->entity->received;
					$rec_qty = 0;
					foreach ($all_received as $key => $cur_received) {
						if ($rec_qty >= $cur_product['quantity'])
							break;
						if ($cur_product['entity']->is($cur_received->product)) {
							$rec_qty++;
							unset($all_received[$key]);
						}
					}
					echo (int) $rec_qty;
					?>
				</td>
				<?php } ?>
				<td><?php echo htmlspecialchars($cur_product['cost']); ?></td>
				<td><?php echo $pines->com_sales->round((int) $cur_product['quantity'] * (float) $cur_product['cost']); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php } ?>