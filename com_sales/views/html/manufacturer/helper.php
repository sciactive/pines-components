<?php
/**
 * Manufacturer entity helper.
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

if ($this->render == 'body' && gatekeeper('com_sales/listmanufacturers')) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Properties</h3>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr>
				<td style="font-weight:bold;">GUID</td>
				<td><?php echo htmlspecialchars($this->entity->guid); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Name</td>
				<td><?php echo htmlspecialchars($this->entity->name); ?></td>
			</tr>
			<?php if (!empty($this->entity->email)) { ?>
			<tr>
				<td style="font-weight:bold;">Email</td>
				<td><a href="mailto:<?php echo htmlspecialchars($this->entity->email); ?>"><?php echo htmlspecialchars($this->entity->email); ?></a></td>
			</tr>
			<?php } if (!empty($this->entity->phone_work)) { ?>
			<tr>
				<td style="font-weight:bold;">Phone</td>
				<td><a href="tel:<?php echo htmlspecialchars($this->entity->phone_work); ?>"><?php echo htmlspecialchars(format_phone($this->entity->phone_work)); ?></a></td>
			</tr>
			<?php } if (!empty($this->entity->fax)) { ?>
			<tr>
				<td style="font-weight:bold;">Fax</td>
				<td><a href="tel:<?php echo htmlspecialchars($this->entity->fax); ?>"><?php echo htmlspecialchars(format_phone($this->entity->fax)); ?></a></td>
			</tr>
			<?php } if (isset($this->entity->logo)) { ?>
			<tr>
				<td style="font-weight:bold;">Company Logo</td>
				<td><img src="<?php echo htmlspecialchars($this->entity->get_logo()); ?>" alt="Company Logo" /></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php if (!empty($this->entity->address_1) || !empty($this->entity->address_international)) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Address</h3>
	<address>
		<?php if ($this->entity->address_type == 'us') {
			echo htmlspecialchars($this->entity->address_1).'<br />';
			if (!empty($this->entity->address_2))
				echo htmlspecialchars($this->entity->address_2).'<br />';
			echo htmlspecialchars($this->entity->city).', ';
			echo htmlspecialchars($this->entity->state).' ';
			echo htmlspecialchars($this->entity->zip);
		} else {
			echo '<pre>'.htmlspecialchars($this->entity->address_international).'</pre>';
		} ?>
	</address>
</div>
<?php }
$products = (array) $pines->entity_manager->get_entities(
		array('class' => com_sales_product),
		array('&',
			'tag' => array('com_sales', 'product'),
			'ref' => array('manufacturer', $this->entity)
		)
	);
if ($products) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Products</h3>
	<table class="table table-bordered" style="clear:both;">
		<thead>
			<tr>
				<th>SKU</th>
				<th>Product</th>
				<th>Enabled</th>
				<th>Manufacturer SKU</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($products as $cur_product) { ?>
			<tr>
				<td><?php echo htmlspecialchars($cur_product->sku); ?></td>
				<td><a data-entity="<?php echo htmlspecialchars($cur_product->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_product->name); ?></a></td>
				<td><?php echo $cur_product->enabled ? 'Yes' : 'No'; ?></td>
				<td><?php echo htmlspecialchars($cur_product->manufacturer_sku); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php } } ?>