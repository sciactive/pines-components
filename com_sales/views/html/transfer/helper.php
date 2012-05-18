<?php
/**
 * Transfer entity helper.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$module = new module('com_entityhelper', 'default_helper');
$module->render = $this->render;
$module->entity = $this->entity;
echo $module->render();

if ($this->render == 'body' && gatekeeper('com_sales/listtransfers')) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Properties</h3>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr>
				<td style="font-weight:bold;">Transfer ID</td>
				<td><?php echo htmlspecialchars($this->entity->guid); ?></td>
			</tr>
			<?php if (!empty($this->entity->reference_number)) { ?>
			<tr>
				<td style="font-weight:bold;">Reference Number</td>
				<td><?php echo htmlspecialchars($this->entity->reference_number); ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td style="font-weight:bold;">Origin</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->origin->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars("{$this->entity->origin->name} [{$this->entity->origin->groupname}]"); ?></a></td>
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
				<td style="font-weight:bold;">Status</td>
				<td><?php echo $this->entity->final ? ($this->entity->finished ? 'Received' : (empty($this->entity->received) ? 'Not Received' : 'Partially Received')) : 'Not Committed'; ?></td>
			</tr>
			<?php if ($this->entity->shipped) { ?>
			<tr>
				<td style="font-weight:bold;">Date Shipped</td>
				<td><?php echo htmlspecialchars(format_date($this->entity->shipped_date, 'full_long')); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">User Who Shipped</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->shipped_user->guid); ?>" data-entity-context="user"><?php echo htmlspecialchars("{$this->entity->shipped_user->name} [{$this->entity->shipped_user->username}]"); ?></a></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Products</h3>
	<table class="table table-bordered" style="clear:both;">
		<thead>
			<tr>
				<th>SKU</th>
				<th>Product</th>
				<th>Quantity</th>
				<?php if ($this->entity->final) { ?>
				<th>Shipped</th>
				<th>Received</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php
			$all_shipped = (array) $this->entity->stock;
			$all_received = (array) $this->entity->received;
			$products = array();
			foreach ((array) $this->entity->products as $cur_product) {
				if (!$products[$cur_product->guid]) {
					$products[$cur_product->guid] = array(
						'entity' => $cur_product,
						'quantity' => 1
					);
				} else
					$products[$cur_product->guid]['quantity']++;
			}
			foreach ($products as $cur_product) { ?>
			<tr>
				<td><?php echo htmlspecialchars($cur_product['entity']->sku); ?></td>
				<td><a data-entity="<?php echo htmlspecialchars($cur_product['entity']->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_product['entity']->name); ?></a></td>
				<td><?php echo htmlspecialchars($cur_product['quantity']); ?></td>
				<?php if ($this->entity->final) { ?>
				<td>
					<?php
					$shipped = array();
					$received = array();
					foreach ($all_shipped as $key => $cur_shipped) {
						if (count($shipped) >= $cur_product['quantity'])
							break;
						if ($cur_product['entity']->is($cur_shipped->product)) {
							$shipped[] = $cur_shipped;
							unset($all_shipped[$key]);
							if ($rkey = $cur_shipped->array_search($all_received)) {
								$received[] = $all_received[$rkey];
								unset($all_received[$rkey]);
							}
						}
					}
					foreach ($all_received as $key => $cur_received) {
						if (count($received) >= $cur_product['quantity'])
							break;
						if ($cur_product['entity']->is($cur_received->product)) {
							$received[] = $cur_received;
							unset($all_received[$key]);
						}
					}
					echo count($shipped);
					?>
				</td>
				<td><?php echo count($received); ?></td>
				<?php } ?>
			</tr>
			<?php if ($this->entity->final && $shipped) { ?>
			<tr>
				<td colspan="2" style="text-align: right;">Shipped Stock</td>
				<td colspan="3"><?php
				$text = array();
				foreach ($shipped as $cur_shipped)
					$text[] = '<a data-entity="'.htmlspecialchars($cur_shipped->guid).'" data-entity-context="com_sales_stock">'.htmlspecialchars($cur_shipped->guid.(!empty($cur_shipped->serial) ? " (Serial: $cur_shipped->serial)" : '')).'</a>';
				echo implode(', ', $text);
				?></td>
			</tr>
			<?php } if ($this->entity->final && $received) { ?>
			<tr>
				<td colspan="2" style="text-align: right;">Received Stock</td>
				<td colspan="3"><?php
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
<?php if (!empty($this->entity->comments)) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Comments</h3>
	<div style="white-space: pre-wrap; padding-bottom: .5em;"><?php echo htmlspecialchars($this->entity->comments); ?></div>
</div>
<?php } } elseif ($this->render == 'footer' && $this->entity->final && !$this->entity->shipped && gatekeeper('com_sales/shipstock')) { ?>
<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'transfer/ship', array('id' => $this->entity->guid))); ?>" class="btn">Ship</a>
<?php } ?>