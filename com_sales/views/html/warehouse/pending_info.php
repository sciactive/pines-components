<?php
/**
 * Displays pending warehouse items.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Stock Location Guide';
?>
<div class="pf-form">
	<div class="pf-element pf-heading">
		<h3><a data-entity="<?php echo htmlspecialchars($this->product->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($this->product->name); ?></a></h3>
		<p>SKU: <?php echo htmlspecialchars($this->product->sku); ?></p>
	</div>
	<div class="pf-element">
		<span class="pf-label">Warehouse</span>
		<span class="pf-note">How many of this product are in the <a data-entity="<?php echo htmlspecialchars($this->warehouse_entity->guid); ?>" data-entity-context="group">warehouse</a>.</span>
		<?php if ($this->warehouse) { ?>
		<div class="pf-group">
			<div class="pf-field">
				<?php if ($this->product->serialized) { ?><a href="javascript:void(0);" onclick="$(this).parent().next().slideToggle();"><?php } ?>
				There <?php echo (count($this->warehouse) == 1 ? 'is' : 'are'); ?> <?php echo count($this->warehouse); ?> available in the warehouse.
				<?php if ($this->product->serialized) { ?></a><?php } ?>
			</div>
			<?php if ($this->product->serialized) { ?>
			<div class="pf-field" style="display: none;">
				Serial numbers:<br />
				<ul>
					<?php foreach ($this->warehouse as $cur_warehouse) { ?>
					<li><a data-entity="<?php echo htmlspecialchars($cur_warehouse->guid); ?>" data-entity-context="com_sales_stock"><?php echo htmlspecialchars($cur_warehouse->serial); ?></a></li>
					<?php } ?>
				</ul>
			</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<span class="pf-field">Not found in the warehouse.</span>
		<?php } ?>
	</div>
	<div class="pf-element">
		<span class="pf-label">Locations</span>
		<span class="pf-note">This product is in the inventory of these locations.</span>
		<?php if ($this->locations) { ?>
		<div class="pf-group">
			<?php foreach ($this->locations as $cur_location) { ?>
			<div class="pf-field"><a data-entity="<?php echo htmlspecialchars($cur_location->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars("{$cur_location->name} [{$cur_location->groupname}]"); ?></a> <a href="javascript:void(0);" onclick="$(this).parent().next().slideToggle();">(<?php echo htmlspecialchars(count($this->locations_serials[$cur_location->guid])); ?> available.)</a></div>
			<div class="pf-field" style="display: none;">
				Serial numbers:<br />
				<ul>
					<?php foreach ($this->locations_serials[$cur_location->guid] as $cur_serial) { ?>
					<li><a data-entity="<?php echo htmlspecialchars($cur_serial->guid); ?>" data-entity-context="com_sales_stock"><?php echo htmlspecialchars($cur_serial->serial); ?></a></li>
					<?php } ?>
				</ul>
			</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<span class="pf-field">Not found at any location.</span>
		<?php } ?>
	</div>
	<div class="pf-element">
		<span class="pf-label">Vendors</span>
		<span class="pf-note">This product is available from these vendors.</span>
		<?php if ($this->product->vendors) { ?>
		<div class="pf-group">
			<?php foreach ($this->product->vendors as $cur_vendor) { ?>
			<div class="pf-field"><a data-entity="<?php echo htmlspecialchars($cur_vendor['entity']->guid); ?>" data-entity-context="com_sales_vendor"><?php echo htmlspecialchars($cur_vendor['entity']->name); ?></a> (Vendor SKU: <?php echo htmlspecialchars($cur_vendor['sku']); ?>, Cost: $<?php echo htmlspecialchars($cur_vendor['cost']); ?><?php if (!empty($cur_vendor['link'])) { ?>, <a href="<?php echo htmlspecialchars($cur_vendor['link']); ?>" target="_blank">Vendor Link</a><?php } ?>)</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<span class="pf-field">No vendors are saved for this product.</span>
		<?php } ?>
	</div>
	<div class="pf-element">
		<span class="pf-label">POs</span>
		<span class="pf-note">This product is included on these warehouse POs.</span>
		<?php if ($this->pos) { ?>
		<div class="pf-group">
			<?php foreach ($this->pos as $cur_po) { ?>
			<div class="pf-field"><a data-entity="<?php echo htmlspecialchars($cur_po->guid); ?>" data-entity-context="com_sales_po"><?php echo htmlspecialchars($cur_po->po_number); ?></a> (Shipper: <a data-entity="<?php echo htmlspecialchars($cur_po->shipper->guid); ?>" data-entity-context="com_sales_shipper"><?php echo htmlspecialchars($cur_po->shipper->name); ?></a>, ETA: <?php echo htmlspecialchars(format_date($cur_po->eta, 'date_med')); ?><?php if (!empty($cur_po->reference_number)) { echo ', Ref #: '.htmlspecialchars($cur_po->reference_number); } ?>)</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<span class="pf-field">This product is not on any POs arriving at the warehouse.</span>
		<?php } ?>
	</div>
	<div class="pf-element">
		<span class="pf-label">Transfers</span>
		<span class="pf-note">This product is included on these warehouse transfers.</span>
		<?php if ($this->transfers) { ?>
		<div class="pf-group">
			<?php foreach ($this->transfers as $cur_transfer) { ?>
			<div class="pf-field"><a data-entity="<?php echo htmlspecialchars($cur_transfer->guid); ?>" data-entity-context="com_sales_transfer"><?php echo htmlspecialchars($cur_transfer->guid); ?></a> (Shipper: <a data-entity="<?php echo htmlspecialchars($cur_transfer->shipper->guid); ?>" data-entity-context="com_sales_shipper"><?php echo htmlspecialchars($cur_transfer->shipper->name); ?></a>, ETA: <?php echo htmlspecialchars(format_date($cur_transfer->eta, 'date_med')); ?>, Origin: <a data-entity="<?php echo htmlspecialchars($cur_transfer->origin->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars("{$cur_transfer->origin->name} [{$cur_transfer->origin->groupname}]"); ?></a><?php if (!empty($cur_transfer->reference_number)) { echo ', Ref #: '.htmlspecialchars($cur_transfer->reference_number); } ?>)</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<span class="pf-field">This product is not on any transfers arriving at the warehouse.</span>
		<?php } ?>
	</div>
</div>