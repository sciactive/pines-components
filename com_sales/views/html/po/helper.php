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
if ($this->render == 'body') {
	$type = $this->entity->info('type');
	$icon = $this->entity->info('icon');
	$types = $this->entity->info('types');
	$url_list = $this->entity->info('url_list');
	if (!preg_match('/^[A-Z]{2,}/', $type))
		$type = ucwords($type);
	if (!preg_match('/^[A-Z]{2,}/', $types))
		$types = ucwords($types);
?>
<div style="float: left;">
	<?php if ($icon) { ?>
	<i style="float: left; height: 16px; width: 16px;" class="<?php echo htmlspecialchars($icon); ?>"></i>&nbsp;
	<?php }
	echo htmlspecialchars($type); ?>
</div>
<?php if ($url_list) { ?>
<div style="float: right;">
	<a href="<?php echo htmlspecialchars($url_list); ?>">List <?php echo htmlspecialchars($types); ?></a>
</div>
<?php } ?>
<div style="clear: both; padding-top: 1em;" class="clearfix">
	<div class="alert alert-info" style="float: left; font-size:.9em;">
		Created on <?php echo format_date($this->entity->p_cdate, 'full_med'); ?>.<br />
		Last modified on <?php echo format_date($this->entity->p_mdate, 'full_med'); ?>.
	</div>
	<?php if ($this->entity->user->guid) { ?>
	<div style="float: right; clear: right; font-size:.9em;">
		Owned by <a data-entity="<?php echo htmlspecialchars($this->entity->user->guid); ?>" data-entity-context="user"><?php echo htmlspecialchars($this->entity->user->info('name')); ?></a>
	</div>
	<?php } if ($this->entity->group->guid) { ?>
	<div style="float: right; clear: right; font-size:.9em;">
		Belongs to group <a data-entity="<?php echo htmlspecialchars($this->entity->group->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars($this->entity->group->info('name')); ?></a>
	</div>
	<?php } ?>
</div>
<?php if (gatekeeper('com_sales/listpos')) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Quick Information</h3>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr>
				<td style="font-weight:bold;">PO Number</td>
				<td><?php echo htmlspecialchars($this->entity->po_number);?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Reference Number</td>
				<td><?php echo htmlspecialchars($this->entity->reference_number);?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Vendor</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->vendor->guid); ?>" data-entity-context="com_sales_vendor"><?php echo htmlspecialchars($this->entity->vendor->name);?></a></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Destination</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->destination->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars(ucwords($this->entity->destination->groupname));?></a></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Shipper</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->shipper->guid); ?>" data-entity-context="com_sales_shipper"><?php echo htmlspecialchars($this->entity->shipper->name);?></a></td>
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
<?php } } elseif ($this->render == 'footer') {
	$url_view = $this->entity->info('url_view');
	$url_edit = $this->entity->info('url_edit');
	if ($url_view) { ?>
<a href="<?php echo htmlspecialchars($url_view); ?>" class="btn">View</a>
<?php } if ($url_edit) { ?>
<a href="<?php echo htmlspecialchars($url_edit); ?>" class="btn">Edit</a>
<?php } if (!$url_view && !$url_edit) { ?>
<a href="javascript:void(0);" class="btn" data-dismiss="modal">Close</a>
<?php } } ?>