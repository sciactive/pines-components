<?php
/**
 * Report product images.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Incomplete Product Image Report';
$this->note = 'Products that appear here are missing images.';
?>
<div>
	<div class="page-header">
		<h3>Section 1: Enabled Products Shown in Storefront</h3>
	</div>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>GUID</th>
				<th>SKU</th>
				<th>Product</th>
				<th style="text-align: right;">Links</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->section1 as $cur_product) { ?>
			<tr>
				<td><?php echo htmlspecialchars($cur_product->guid); ?></td>
				<td><?php echo htmlspecialchars($cur_product->sku); ?></td>
				<td><a data-entity="<?php echo htmlspecialchars($cur_product->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_product->name); ?></a></td>
				<td style="text-align: right;">
					<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'product/edit', array('id' => $cur_product->guid), true)); ?>" target="_blank">Edit <i class="icon-external-link"></i></a>
					| <a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'product', array('a' => $cur_product->alias), true)); ?>" target="_blank">View <i class="icon-external-link"></i></a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<div>
	<div class="page-header">
		<h3>Section 2: Enabled Products Not in Storefront</h3>
	</div>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>GUID</th>
				<th>SKU</th>
				<th>Product</th>
				<th style="text-align: right;">Links</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->section2 as $cur_product) { ?>
			<tr>
				<td><?php echo htmlspecialchars($cur_product->guid); ?></td>
				<td><?php echo htmlspecialchars($cur_product->sku); ?></td>
				<td><a data-entity="<?php echo htmlspecialchars($cur_product->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_product->name); ?></a></td>
				<td style="text-align: right;">
					<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'product/edit', array('id' => $cur_product->guid), true)); ?>" target="_blank">Edit <i class="icon-external-link"></i></a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<div>
	<div class="page-header">
		<h3>Section 3: Disabled Products Set to Show in Storefront</h3>
	</div>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>GUID</th>
				<th>SKU</th>
				<th>Product</th>
				<th style="text-align: right;">Links</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->section3 as $cur_product) { ?>
			<tr>
				<td><?php echo htmlspecialchars($cur_product->guid); ?></td>
				<td><?php echo htmlspecialchars($cur_product->sku); ?></td>
				<td><a data-entity="<?php echo htmlspecialchars($cur_product->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_product->name); ?></a></td>
				<td style="text-align: right;">
					<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'product/edit', array('id' => $cur_product->guid), true)); ?>" target="_blank">Edit <i class="icon-external-link"></i></a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<div>
	<div class="page-header">
		<h3>Section 4: Disabled Products Not Set to Show in Storefront</h3>
	</div>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>GUID</th>
				<th>SKU</th>
				<th>Product</th>
				<th style="text-align: right;">Links</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->section4 as $cur_product) { ?>
			<tr>
				<td><?php echo htmlspecialchars($cur_product->guid); ?></td>
				<td><?php echo htmlspecialchars($cur_product->sku); ?></td>
				<td><a data-entity="<?php echo htmlspecialchars($cur_product->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_product->name); ?></a></td>
				<td style="text-align: right;">
					<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'product/edit', array('id' => $cur_product->guid), true)); ?>" target="_blank">Edit <i class="icon-external-link"></i></a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>