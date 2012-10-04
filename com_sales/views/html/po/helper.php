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

if ($this->render == 'body' && (gatekeeper('com_sales/listpos') || gatekeeper('com_sales/managestock'))) {
$module = new module('com_entityhelper', 'default_helper');
$module->render = $this->render;
$module->entity = $this->entity;
echo $module->render();
?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Properties</h3>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr>
				<td style="font-weight:bold;">PO Number</td>
				<td><?php echo htmlspecialchars($this->entity->po_number); ?></td>
			</tr>
			<?php if (!empty($this->entity->reference_number)) { ?>
			<tr>
				<td style="font-weight:bold;">Reference Number</td>
				<td><?php echo htmlspecialchars($this->entity->reference_number); ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td style="font-weight:bold;">Status</td>
				<td><?php echo $this->entity->final ? ($this->entity->finished ? 'Received' : (empty($this->entity->received) ? 'Not Received' : 'Partially Received')) : 'Not Committed'; ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Vendor</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->vendor->guid); ?>" data-entity-context="com_sales_vendor"><?php echo htmlspecialchars($this->entity->vendor->name); ?></a></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Destination</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->destination->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars("{$this->entity->destination->name} [{$this->entity->destination->groupname}]"); ?></a></td>
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
				<td style="font-weight:bold;">Tracking #</td>
				<td><?php
					if (isset($this->entity->shipper->guid)) {
						$links = array();
						foreach ((array) $this->entity->tracking_numbers as $cur_number)
							$links[] = '<a href="'.htmlspecialchars($this->entity->shipper->tracking_url($cur_number)).'" target="_blank">'.htmlspecialchars($cur_number).'</a>';
						echo implode('<br />', $links);
					} else
						echo str_replace("\n", '<br />', htmlspecialchars(isset($this->entity->tracking_numbers) ? implode("\n", $this->entity->tracking_numbers) : ''));
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
			<tr>
				<th>SKU</th>
				<th>Product</th>
				<th>Quantity</th>
				<?php if ($this->entity->final) { ?>
				<th>Received</th>
				<?php } ?>
				<th>Unit Cost</th>
				<th>Line Total</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$all_received = (array) $this->entity->received;
			foreach ((array) $this->entity->products as $cur_product) { ?>
			<tr>
				<td><?php echo htmlspecialchars($cur_product['entity']->sku); ?></td>
				<td><a data-entity="<?php echo htmlspecialchars($cur_product['entity']->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_product['entity']->name); ?></a></td>
				<td><?php echo htmlspecialchars($cur_product['quantity']); ?></td>
				<?php if ($this->entity->final) { ?>
				<td>
					<?php
					$received = array();
					foreach ($all_received as $key => $cur_received) {
						if (count($received) >= $cur_product['quantity'])
							break;
						if ($cur_product['entity']->is($cur_received->product)) {
							$received[] = $cur_received;
							unset($all_received[$key]);
						}
					}
					echo count($received);
					?>
				</td>
				<?php } ?>
				<td><?php echo htmlspecialchars($cur_product['cost']); ?></td>
				<td><?php echo $pines->com_sales->round((int) $cur_product['quantity'] * (float) $cur_product['cost']); ?></td>
			</tr>
			<?php if ($this->entity->final && $received) { ?>
			<tr>
				<td colspan="2" style="text-align: right;">Stock Entries</td>
				<td colspan="4"><?php
				$text = array();
				foreach ($received as $cur_received)
					$text[] = '<a data-entity="'.htmlspecialchars($cur_received->guid).'" data-entity-context="com_sales_stock">'.htmlspecialchars($cur_received->guid.(!empty($cur_received->serial) ? " (Serial: $cur_received->serial)" : '')).'</a>';
				echo implode(', ', $text);
				?></td>
			</tr>
			<?php } ?>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php
$sales = $pines->entity_manager->get_entities(
		array('class' => com_sales_sale),
		array('&',
			'tag' => array('com_sales', 'sale'),
			'ref' => array('products', $this->entity)
		)
	);
if ($sales) {
?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Attached Warehouse Orders</h3>
	<table class="table table-bordered" style="clear:both;">
		<thead>
			<tr>
				<th>Sale</th>
				<th>SKU</th>
				<th>Product</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($sales as $cur_sale) {
				foreach ($cur_sale->products as $cur_product) {
					if (!isset($cur_product['po']) || !$this->entity->is($cur_product['po']))
						continue;
					?>
			<tr>
				<td><a data-entity="<?php echo htmlspecialchars($cur_sale->guid); ?>" data-entity-context="com_sales_sale"><?php echo htmlspecialchars($cur_sale->id); ?></a></td>
				<td><?php echo htmlspecialchars($cur_product['entity']->sku); ?></td>
				<td><a data-entity="<?php echo htmlspecialchars($cur_product['entity']->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_product['entity']->name); ?></a></td>
			</tr>
			<?php } } ?>
		</tbody>
	</table>
</div>
<?php
}
if (!empty($this->entity->comments)) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Comments</h3>
	<div style="white-space: pre-wrap; padding-bottom: .5em;"><?php echo htmlspecialchars($this->entity->comments); ?></div>
</div>
<?php } } elseif ($this->render == 'footer') { ?>
<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'po/edit', array('id' => $this->entity->guid))); ?>" class="btn">Edit</a>
<?php if (!$this->entity->finished && ( (gatekeeper('com_sales/receive') && isset($this->entity->destination->guid) && $this->entity->destination->is($_SESSION['user']->group)) || (gatekeeper('com_sales/receivelocation') && isset($this->entity->destination->guid) && isset($_SESSION['user']->group->guid) && $this->entity->destination->in_array($_SESSION['user']->group->get_descendants(true))) ) ) { ?>
<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'stock/receive', array('location' => $this->entity->destination->guid, 'shipments' => $this->entity->guid))); ?>" class="btn">Receive</a>
<?php } if (gatekeeper('com_sales/listpos')) { ?>
<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'po/list')); ?>" class="btn">View in List</a>
<?php } } ?>