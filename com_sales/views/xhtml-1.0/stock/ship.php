<?php
/**
 * Provides a form for the user to edit a shipment.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Editing Shipment ['.htmlspecialchars($this->entity->guid).']';
$this->note = 'Provide shipment details in this form.';
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'stock/saveship')); ?>">
	<div class="pf-element">
		<span class="pf-label">Shipping Address</span>
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
	<div class="pf-element">
		<label><span class="pf-label">Shipper</span>
			<select class="pf-field ui-widget-content ui-corner-all" name="shipper">
				<option value="null">-- None --</option>
				<?php foreach ($this->shippers as $cur_shipper) { ?>
				<option value="<?php echo $cur_shipper->guid; ?>"<?php echo $this->entity->shipper->guid == $cur_shipper->guid ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_shipper->name); ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="pf-element">
		<?php if (!$this->entity->final) { ?>
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				$("#p_muid_eta").datepicker({
					dateFormat: "yy-mm-dd",
					showOtherMonths: true,
					selectOtherMonths: true
				});
			});
			// ]]>
		</script>
		<?php } ?>
		<label><span class="pf-label">ETA</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_eta" name="eta" size="24" value="<?php echo ($this->entity->eta ? format_date($this->entity->eta, 'date_sort') : ''); ?>" /></label>
	</div>
	<div class="pf-element pf-full-width">
		<label><span class="pf-label">Tracking Number(s)</span>
			<span class="pf-note">One per line.</span>
			<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="tracking_numbers"><?php echo isset($this->entity->tracking_numbers) ? implode("\n", $this->entity->tracking_numbers) : ''; ?></textarea></span></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Shipped</span>
			<input class="pf-field" type="checkbox" name="shipped" value="ON"<?php echo $this->entity->has_tag('shipping_shipped') ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<?php switch ($this->type) {
		case 'sale':
		default: 
			?><div class="pf-element">
				<span class="pf-label">Sale</span>
				<span class="pf-field">
					#<?php echo $this->entity->id; ?>:
					<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/receipt', array('id' => $this->entity->guid))); ?>" onclick="window.open(this.href); return false;">Receipt</a>
					<?php if (gatekeeper('com_sales/editsale')) { ?>
					<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/edit', array('id' => $this->entity->guid))); ?>" onclick="window.open(this.href); return false;">Edit</a>
					<?php } ?>
				</span>
			</div><?php
			break;
	} ?>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="type" value="<?php echo $this->type; ?>" />
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Save" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'stock/shipments')); ?>');" value="Cancel" />
	</div>
</form>