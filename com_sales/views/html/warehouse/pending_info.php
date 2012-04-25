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
		<h3><a href="<?php echo htmlspecialchars(pines_url('com_sales', 'product/edit', array('id' => $this->product->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($this->product->name); ?></a></h3>
		<p>SKU: <?php echo htmlspecialchars($this->product->sku); ?></p>
	</div>
	<div class="pf-element">
		<span class="pf-label">Warehouse</span>
		<span class="pf-note">How many of this product are in the warehouse.</span>
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
					<li><?php echo htmlspecialchars($cur_warehouse->serial); ?></li>
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
			<div class="pf-field"><?php echo htmlspecialchars("{$cur_location->name} [{$cur_location->groupname}]"); ?> <a href="javascript:void(0);" onclick="$(this).parent().next().slideToggle();">(<?php echo htmlspecialchars(count($this->locations_serials[$cur_location->guid])); ?> available.)</a></div>
			<div class="pf-field" style="display: none;">
				Serial numbers:<br />
				<ul>
					<?php foreach ($this->locations_serials[$cur_location->guid] as $cur_serial) { ?>
					<li><?php echo htmlspecialchars($cur_serial); ?></li>
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
			<div class="pf-field"><?php if (!empty($cur_vendor['link'])) { ?><a href="<?php echo htmlspecialchars($cur_vendor['link']); ?>" onclick="window.open(this.href); return false;"><?php } ?><?php echo htmlspecialchars($cur_vendor['entity']->name); ?><?php if (!empty($cur_vendor['link'])) { ?></a><?php } ?> (Vendor SKU: <?php echo htmlspecialchars($cur_vendor['sku']); ?>, Cost: $<?php echo htmlspecialchars($cur_vendor['cost']); ?>)</div>
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
			<div class="pf-field"><a href="<?php echo htmlspecialchars(pines_url('com_sales', 'po/edit', array('id' => $cur_po->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_po->po_number); ?></a> (Shipper: <?php echo htmlspecialchars($cur_po->shipper->name); ?>, ETA: <?php echo htmlspecialchars(format_date($cur_po->eta, 'date_med')); ?><?php if (!empty($cur_po->reference_number)) { echo ', Ref #: '.htmlspecialchars($cur_po->reference_number); } ?>)</div>
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
			<div class="pf-field"><a href="<?php echo htmlspecialchars(pines_url('com_sales', 'transfer/edit', array('id' => $cur_transfer->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_transfer->guid); ?></a> (Shipper: <?php echo htmlspecialchars($cur_transfer->shipper->name); ?>, ETA: <?php echo htmlspecialchars(format_date($cur_transfer->eta, 'date_med')); ?>, Origin: <?php echo htmlspecialchars("{$cur_transfer->origin->name} [{$cur_transfer->origin->groupname}]"); ?><?php if (!empty($cur_transfer->reference_number)) { echo ', Ref #: '.htmlspecialchars($cur_transfer->reference_number); } ?>)</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<span class="pf-field">This product is not on any transfers arriving at the warehouse.</span>
		<?php } ?>
	</div>
</div>