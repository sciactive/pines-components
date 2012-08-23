<?php
/**
 * Grid layout of products.
 *
 * @package Components\storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css">
#p_muid_products .product {cursor: pointer;}
#p_muid_products .product_main {padding: 0 .6em;}
#p_muid_products .thumb {margin: .3em;}
#p_muid_products .price_box {margin: .5em 0;}
#p_muid_products .price {float: left; font-size: 1.4em; font-weight: bold; margin-bottom: .5em;}
#p_muid_products .add_cart {float: right; margin-left: .5em;}
#p_muid_products .name {font-size: 1.2em;}
#p_muid_products .desc ul, #p_muid_products .desc ol {padding-left: 2em;}
#p_muid_products .price .value {display: none;}
</style>
<script type="text/javascript">
pines(function(){
	$("#p_muid_products").on("click", ".product", function(){
		pines.get(<?php echo json_encode(pines_url('com_storefront', 'product', array('a' => '__alias__'))); ?>.replace("__alias__", $(this).children(".product_alias").text()));
	}).on("click", "button.add_cart", function(e){
		<?php if (!$pines->config->com_storefront->catalog_mode) { ?>
		var button = $(this), product = button.closest(".product"), guid = parseInt(button.parent().siblings(".product_guid").text());
		pines.com_storefront_add_to_cart(guid, product.find(".name").text(), parseFloat(product.find(".price > .value").text()), product);
		e.stopPropagation();
		<?php } ?>
		e.preventDefault();
	});
});
</script>
<div class="row-fluid">
	<?php $i = 0; foreach ($products as $key => $cur_product) {
		if ($i && !($i % 3)) { ?>
</div>
<hr />
<div class="row-fluid">
	<?php } $i++; ?>
	<div class="span4 product">
		<div class="product_guid" style="display: none;"><?php echo htmlspecialchars($cur_product->guid); ?></div>
		<div class="product_alias" style="display: none;"><?php echo htmlspecialchars($cur_product->alias); ?></div>
		<div class="product_main">
			<?php if (isset($cur_product->thumbnail)) { ?>
			<img class="thumb" alt="<?php echo htmlspecialchars($cur_product->name); ?>" src="<?php echo htmlspecialchars($pines->config->location.$cur_product->thumbnail); ?>" />
			<?php } ?>
			<div class="name"><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'product', array('a' => $cur_product->alias))); ?>"><?php echo htmlspecialchars($cur_product->name); ?></a></div>
			<div class="info">
				<?php if (!empty($cur_product->manufacturer_sku)) { ?>
				<strong>Model:</strong> <?php echo format_content(htmlspecialchars($cur_product->manufacturer_sku)); ?> | 
				<?php } ?>
				<strong>SKU:</strong> <?php echo format_content(htmlspecialchars($cur_product->sku)); ?>
			</div>
			
			<div class="price_box clearfix">
				<div class="price"><?php echo $pines->com_storefront->format_price($cur_product->unit_price); ?><span class="value"><?php echo isset($cur_product->unit_price) ? round($cur_product->unit_price, 2) : ''; ?></span></div>
				<?php if (!$pines->config->com_storefront->catalog_mode) { ?>
				<button class="add_cart btn btn-primary"><i class="icon-shopping-cart icon-white"></i> Add to Cart</button>
				<?php } ?>
			</div>
			<div class="desc"><?php echo format_content($cur_product->short_description); ?></div>
		</div>
	</div>
	<?php } ?>
</div>
<br style="clear: both; height: 0; font-size: 0;" />