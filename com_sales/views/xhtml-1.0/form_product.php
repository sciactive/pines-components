<?php
/**
 * Provides a form for the user to edit a product.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->show_title = false;
?>
<form class="pform pform_twocol" method="post" id="product_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
<fieldset>
    <legend><?php echo $this->title; ?></legend>
    <div class="element heading">
        <p>Provide product details in this form.</p>
    </div>
    <div class="element">
        <label><span class="label">Name</span>
        <input class="field" type="text" name="name" size="20" value="<?php echo $this->entity->name; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Enabled</span>
        <input class="field" type="checkbox" name="enabled" size="20" value="ON"<?php echo ($this->entity->enabled || is_null($this->entity->enabled)) ? ' checked="checked"' : ''; ?> /></label>
    </div>
    <div class="element">
        <label><span class="label">Product SKU</span>
        <input class="field" type="text" name="sku" size="20" value="<?php echo $this->entity->sku; ?>" /></label>
    </div>
    <div class="element full_width">
        <span class="label">Description</span><br />
        <textarea class="peditor" style="width: 100%;" name="description"><?php echo $this->entity->description; ?></textarea>
    </div>
    <div class="element full_width">
        <span class="label">Short Description</span><br />
        <textarea class="peditor_simple" style="width: 100%;" name="short_description"><?php echo $this->entity->short_description; ?></textarea>
    </div>
    <div class="element">
        <label><span class="label">Manufacturer</span>
        <select class="field" name="manufacturer">
            <option value="null">-- None --</option>
            <?php foreach ($this->manufacturers as $cur_manufacturer) { ?>
                <option value="<?php echo $cur_manufacturer->guid; ?>"<?php echo $this->entity->manufacturer == $cur_manufacturer->guid ? ' selected="selected"' : ''; ?>><?php echo $cur_manufacturer->name; ?></option>
            <?php } ?>
        </select></label>
    </div>
    <div class="element">
        <label><span class="label">Manufacturer SKU</span>
        <input class="field" type="text" name="manufacturer_sku" size="20" value="<?php echo $this->entity->manufacturer_sku; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Average Cost</span>
        <input class="field" type="text" name="average_cost" size="20" value="<?php echo $this->entity->average_cost; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Pricing Method</span>
        <select class="field" name="type">
            <option value="fixed"<?php echo $this->entity->type == 'fixed' ? ' selected="selected"' : ''; ?>>Fixed Pricing</option>
            <option value="margin"<?php echo $this->entity->type == 'margin' ? ' selected="selected"' : ''; ?>>Margin Pricing</option>
        </select></label>
    </div>
    <br />
    <fieldset class="group">
        <legend>Pricing Defaults</legend>
        <div class="element">
            <label><span class="label">Unit Price</span>
            <input class="field" type="text" name="unit_price" size="20" value="<?php echo $this->entity->unit_price; ?>" /></label>
        </div>
        <div class="element">
            <label><span class="label">Margin</span>
            <input class="field" type="text" name="margin" size="20" value="<?php echo $this->entity->margin; ?>" /></label>
        </div>
        <div class="element">
            <label><span class="label">Floor</span>
            <input class="field" type="text" name="floor" size="20" value="<?php echo $this->entity->floor; ?>" /></label>
        </div>
    </fieldset>
    <br />
    <fieldset class="group">
        <legend>Attributes</legend>
        <div class="element">
            <label><span class="label">RMA Available After</span>
            <input class="field" type="text" name="rma_after" size="10" value="<?php echo $this->entity->rma_after; ?>" /> days.</label>
        </div>
        <div class="element">
            <label><span class="label">Discountable</span>
            <input class="field" type="checkbox" name="discountable" size="20" value="ON"<?php echo $this->entity->discountable ? ' checked="checked"' : ''; ?> /></label>
        </div>
        <div class="element">
            <label><span class="label">Hide on Invoice</span>
            <input class="field" type="checkbox" name="hide_on_invoice" size="20" value="ON"<?php echo $this->entity->hide_on_invoice ? ' checked="checked"' : ''; ?> /></label>
        </div>
        <div class="element">
            <label><span class="label">Non-Refundable</span>
            <input class="field" type="checkbox" name="non_refundable" size="20" value="ON"<?php echo $this->entity->non_refundable ? ' checked="checked"' : ''; ?> /></label>
        </div>
    </fieldset>
    <br />
    <div class="element">
        <label><span class="label">Additional Barcodes</span>
        <input class="field" type="text" name="additional_barcodes" size="20" value="<?php echo $this->entity->additional_barcodes; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Additional Taxes/Fees</span>
        <input class="field" type="text" name="additional_taxfees" size="20" value="<?php echo $this->entity->additional_taxfees; ?>" /></label>
    </div>
	<div class="element buttons">
        <?php if ( !is_null($this->id) ) { ?>
        <input type="hidden" name="id" value="<?php echo $this->id; ?>" />
        <?php } ?>
        <input class="button" type="submit" value="Submit" />
        <input class="button" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listproducts'); ?>';" value="Cancel" />
    </div>
</fieldset>
</form>