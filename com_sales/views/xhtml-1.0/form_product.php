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
    <div class="element">
        <span class="label">Category</span>
	<script type="text/javascript">
	    // <![CDATA[
	    $(document).ready(function(){
		var input = $("#category");
		$("#category_tree").tree({
		    rules : {
			multiple : true
		    },
		    data : {
			type : "json",
			opts : {
			    "static" : [
				{
				    // the short format demo
				    data : "A node",
				    attributes : { "id" : "1" },
				    // here are the children
				    children : [
					{
					    data : "Child node 1",
					    attributes : { "id" : "3" }
					},
					{
					    data : "Child node 2",
					    attributes : { "id" : "4" }
					},
					{
					    data : "Child node 3",
					    attributes : { "id" : "5" }
					}
				    ]
				},
				{
				    data : "Another node",
				    attributes : { "id" : "2" }
				}
			    ]
			}
		    },
		    selected : ["1"],
		    callback : {
			oncreate : function(NODE, REF_NODE, TYPE, TREE_OBJ, RB) {
			    NODE.id = "6";
			    alert(REF_NODE.id+": "+TYPE+", "+NODE.id);
			    if (REF_NODE.id == "6") {
				$.tree.rollback(RB);
				alert("Can't create it.");
			    }
			},
			onrename : function(NODE, TREE_OBJ, RB) {
			    alert(NODE.id+", "+TREE_OBJ.get_text(NODE));
			    if (TREE_OBJ.get_text(NODE) == "loser") {
				$.tree.rollback(RB);
				alert("No loser for you!");
			    }
			},
			onchange : function(NODE, TREE_OBJ) {
			    input.val("[]");
			    $.each(TREE_OBJ.selected_arr, function(){
				input.val(JSON.stringify($.merge(JSON.parse(input.val()), [this.attr("id")])));
			    });
			}
		    },
		    plugins : {
			contextmenu : {}
		    }
		});
	    });
	    // ]]>
	</script>
	<div class="group">
	    <div id="category_tree" style="border: 1px solid black; float: left;"></div>
	</div>
        <input id="category" class="field" type="text" name="category" />
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
        <select class="field" name="pricing_method">
            <option value="fixed"<?php echo $this->entity->type == 'fixed' ? ' selected="selected"' : ''; ?>>Fixed Pricing</option>
            <option value="margin"<?php echo $this->entity->type == 'margin' ? ' selected="selected"' : ''; ?>>Margin Pricing</option>
        </select></label>
        <script type="text/javascript">
            // <![CDATA[
            $(document).ready(function(){
                $("#product_details [name=pricing_method]").change(function(){
                    if ($(this).val() == "fixed") {
                        $("#product_details [name=margin]").attr('disabled', 'disabled');
                        $("#product_details [name=unit_price]").removeAttr('disabled');
                    } else {
                        $("#product_details [name=unit_price]").attr('disabled', 'disabled');
                        $("#product_details [name=margin]").removeAttr('disabled');
                    }
                });
                $("#product_details [name=pricing_method]").change();
            });
            // ]]>
        </script>
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
            <input class="field" type="checkbox" name="discountable" size="20" value="ON"<?php echo ($this->entity->discountable || is_null($this->entity->discountable)) ? ' checked="checked"' : ''; ?> /></label>
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
    <div class="element full_width">
        <span class="label">Additional Barcodes</span>
        <div class="group">
            <input class="field" type="text" name="additional_barcodes" size="20" value="<?php echo (is_array($this->entity->additional_barcodes) ? implode(',', $this->entity->additional_barcodes) : ''); ?>" />
            <script type="text/javascript">
                // <![CDATA[
                $(document).ready(function(){
                    $("#product_details [name=additional_barcodes]").tagEditor({completeOnBlur: true});
                });
                // ]]>
            </script>
        </div>
    </div>
    <div class="element">
        <label><span class="label">Additional Taxes/Fees</span>
        <span class="note">These taxes will be applied in addition to the group's default taxes. If you select a tax applied to a group, it will be applied twice to this product for that group.</span>
        <span class="note">Hold Ctrl (Command on Mac) to select multiple.</span>
        <select class="field" name="additional_tax_fees[]" size="6" multiple="multiple">
            <?php foreach ($this->tax_fees as $cur_tax_fee) { ?>
                <option value="<?php echo $cur_tax_fee->guid; ?>"<?php echo (is_array($this->entity->additional_tax_fees) && in_array($cur_tax_fee->guid, $this->entity->additional_tax_fees)) ? ' selected="selected"' : ''; ?>><?php echo $cur_tax_fee->name; ?></option>
            <?php } ?>
        </select></label>
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