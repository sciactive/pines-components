<?php
/**
 * Provides a packing list for a shipment.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Packing List for Shipment '.htmlspecialchars($this->entity->id);

if ($pines->current_template != 'tpl_print') {
?>
<style type="text/css" media="print">
	#p_muid_print_link {display: none;}
</style>
<div id="p_muid_print_link" style="text-align: right;">
	<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'shipment/packinglist', array('id' => $this->entity->guid, 'template' => 'tpl_print'))); ?>" target="_blank">Printer Friendly Version</a>
</div>
<?php } ?>
<div class="pf-form" id="p_muid_form">
	<div class="hero-unit pull-right" style="padding-top: 30px; padding-bottom: 30px;">
		<h2><?php echo htmlspecialchars($this->entity->ref->info('name')); ?></h2>
	</div>
	<div class="pf-element" style="margin: 2em;">
		<span class="pf-label"><span class="label">Ship To</span></span>
		<div class="pf-group">
			<div class="pf-field">
				<strong><?php echo htmlspecialchars($this->entity->shipping_address->name); ?></strong><br />
				<?php if ($this->entity->shipping_address->address_type == 'us') { if (!empty($this->entity->shipping_address->address_1)) { ?>
				<?php echo htmlspecialchars($this->entity->shipping_address->address_1.' '.$this->entity->shipping_address->address_2); ?><br />
				<?php echo htmlspecialchars($this->entity->shipping_address->city); ?>, <?php echo htmlspecialchars($this->entity->shipping_address->state); ?> <?php echo htmlspecialchars($this->entity->shipping_address->zip); ?>
				<?php } } else { ?>
				<?php echo str_replace("\n", '<br />', htmlspecialchars($this->entity->shipping_address->address_international)); ?>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<table class="table table-bordered table-condensed" style="border-left: none; border-right: none; border-bottom: none;">
			<tr>
				<td style="width: 10%; text-align: right; border-left: none;"><span class="label">Shipment ID</span></td>
				<td style="width: 23%;"><?php echo htmlspecialchars($this->entity->id); ?></td>
				<td style="width: 10%; text-align: right; border-left: none;"><span class="label">Ship Date</span></td>
				<td style="width: 23%;"><?php echo htmlspecialchars(format_date($this->entity->p_cdate, 'date_med')); ?></td>
				<td style="width: 10%; text-align: right; border-left: none;"><span class="label">Fulfilled By</span></td>
				<td style="width: 23%;"><?php echo htmlspecialchars($this->entity->user->name); ?></td>
			</tr>
			<tr>
				<td style="width: 10%; text-align: right; border-left: none;"><span class="label">Shipper</span></td>
				<td style="width: 23%;"><?php echo htmlspecialchars($this->entity->shipper->name); ?></td>
				<td style="width: 10%; text-align: right; border-left: none;"><span class="label">ETA</span></td>
				<td style="width: 23%;"><?php echo ($this->entity->eta ? htmlspecialchars(format_date($this->entity->eta, 'date_med')) : ''); ?></td>
				<td style="width: 10%; text-align: right; border-left: none;"><span class="label">Reference</span></td>
				<td style="width: 23%;"><?php echo htmlspecialchars($this->entity->ref->info('name')); ?></td>
			</tr>
			<tr>
				<td style="width: 10%; text-align: right; border-left: none;"><span class="label">Tracking #</span></td>
				<td colspan="5"><div style="white-space: pre; font-family: monospace;"><?php echo isset($this->entity->tracking_numbers) ? htmlspecialchars(implode("\n", $this->entity->tracking_numbers)) : ''; ?></div></td>
			</tr>
		</table>
	</div>
	<div class="pf-element pf-full-width">
		<table class="table table-bordered table-condensed">
			<tr>
				<th>Quantity</th>
				<th>SKU</th>
				<th>Serial</th>
				<th>Item</th>
			</tr>
			<?php foreach ($this->entity->products as $key => $cur_product) { ?>
			<tr>
				<td style="text-align: right;"><?php echo htmlspecialchars(count($cur_product['stock_entities'])); ?></td>
				<td><?php echo htmlspecialchars($cur_product['entity']->sku); ?></td>
				<td><?php echo $cur_product['stock_entities'][0]->serial ? htmlspecialchars($cur_product['stock_entities'][0]->serial) : 'N/A'; ?></td>
				<td style="width: 85%;"><?php echo htmlspecialchars($cur_product['entity']->name); ?></td>
			</tr>
			<?php } ?>
		</table>
	</div>
	<?php if (!empty($this->entity->notes)) { ?>
	<div class="pf-element" style="margin: 2em;">
		<span class="pf-label"><span class="label">Notes</span></span>
		<div class="pf-group">
			<div class="pf-field" style="white-space: pre; font-family: monospace;"><?php echo htmlspecialchars($this->entity->notes); ?></div>
		</div>
	</div>
	<?php } ?>
</div>