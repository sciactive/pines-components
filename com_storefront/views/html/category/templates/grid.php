<?php
/**
 * Grid layout of products.
 *
 * @package Components
 * @subpackage storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css">
	#p_muid_products .product {
		float: left;
		width: 28%;
		padding: .5em;
		margin: .5em;
		position: relative;
		border-top: none;
		border-right: none;
		border-left: none;
		background-image: none !important;
		cursor: pointer;
		font-weight: inherit !important;
	}
	#p_muid_products .divider {
		float: left;
		position: relative;
		border-top: none;
		border-right: none;
		border-bottom: none;
		margin: 1em 0 0;
		padding: .5em 0;
		width: 0;
	}
	#p_muid_products .product_main {
		text-align: left;
	}
	#p_muid_products .product_button {
		margin-bottom: 1em;
	}
	#p_muid_products .thumb {
		float: left;
		margin: .3em;
	}
	#p_muid_products .price {
		float: right;
		font-size: 1.6em;
		margin-bottom: .5em;
	}
	#p_muid_products .name {
		float: left;
		clear: left;
		font-size: 1.2em;
		width: 100%;
	}
	#p_muid_products .desc {
		font-size: .9em;
	}
	#p_muid_products .desc ul, #p_muid_products .desc ol {
		padding-left: 2em;
	}
	#p_muid_products .price .value {
		display: none;
	}
	#p_muid_products button.add_cart {
		float: right;
	}
</style>
<script type="text/javascript">
	pines(function(){
		var tallest = 0;
		$("div.product_main", "#p_muid_products").each(function(){
			var height = $(this).height();
			if (height > tallest)
				tallest = height;
		}).css("height", tallest+"px");
		$("#p_muid_products").delegate("div.product", "mouseenter", function(){
			$(this).addClass("ui-state-default ui-state-active");
		}).delegate("div.product", "mouseleave", function(){
			$(this).removeClass("ui-state-default ui-state-active");
		}).delegate("div.product", "click", function(){
			pines.get(<?php echo json_encode(pines_url('com_storefront', 'product', array('a' => '__alias__'))); ?>.replace("__alias__", $(this).children("div.product_alias").text()));
		}).delegate("button.add_cart", "click", function(e){
			var button = $(this);
			var product = button.closest("div.product");
			var guid = parseInt(button.parent().siblings("div.product_guid").text());
			pines.com_storefront_add_to_cart(guid, product.find("div.name").text(), parseFloat(product.find("div.price > span.value").text()), product);
			e.stopPropagation();
			return false;
		});
	});
</script>
<?php foreach ($products as $key => $cur_product) { ?>
<div class="product ui-widget-content">
	<div class="product_guid" style="display: none;"><?php echo htmlspecialchars($cur_product->guid); ?></div>
	<div class="product_alias" style="display: none;"><?php echo htmlspecialchars($cur_product->alias); ?></div>
	<div class="product_main">
		<div class="name"><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'product', array('a' => $cur_product->alias))); ?>"><?php echo htmlspecialchars($cur_product->name); ?></a></div>
		<div class="price"><?php echo $pines->com_storefront->format_price($cur_product->unit_price); ?><span class="value"><?php echo isset($cur_product->unit_price) ? round($cur_product->unit_price, 2) : ''; ?></span></div>
		<?php if (isset($cur_product->thumbnail)) { ?>
		<img class="thumb" alt="<?php echo htmlspecialchars($cur_product->name); ?>" src="<?php echo htmlspecialchars($cur_product->thumbnail); ?>" />
		<?php } ?>
		<br style="clear: both;" />
		<div class="desc"><?php echo format_content($cur_product->short_description); ?></div>
	</div>
	<div class="product_button">
		<?php if (!$pines->config->com_storefront->catalog_mode) { ?>
		<button class="add_cart btn btn-primary"><i class="icon-shopping-cart icon-white"></i> Add to Cart</button>
		<?php } ?>
	</div>
</div>
<div class="divider ui-widget-content">
	<div class="product_main">&nbsp;</div>
</div>
<?php } ?>
<br style="clear: left; height: 0; font-size: 0;" />