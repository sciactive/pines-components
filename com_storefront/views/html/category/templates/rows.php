<?php
/**
 * Row layout of products.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_products .product {
		padding: .5em;
		margin: .5em;
		position: relative;
		border-top: none;
		border-right: none;
		border-left: none;
		background-image: none !important;
		cursor: pointer;
		font-weight: inherit !important;
	}#p_muid_products .product_right, #p_muid_products .product_main {
		margin: 0;
		padding: 0;
		width: 25%;
	}
	#p_muid_products .product_main {
		width: 74%;
	}
	#p_muid_products .product_left, #p_muid_products .product_info {
		margin: 0;
		padding: 0;
		float: left;
	}
	#p_muid_products .product_left {
		width: 30%;
		text-align: center;
	}
	#p_muid_products .size_less {
		width: 70%;
	}
	#p_muid_products .product_right {
		float: right;
		border: none !important;
		background-image: none;
	}
	#p_muid_products .padding_box {
		padding: .4em;
	}
	#p_muid_products .price {
		font-size: 1.6em;
		margin-bottom: .5em;
	}
	#p_muid_products .name {
		float: left;
		clear: left;
		font-size: 1.2em;
		width: 100%;
	}
	#p_muid_products .info, #p_muid_products .desc {
		font-size: .9em;
	}
	#p_muid_products .desc ul, #p_muid_products .desc ol {
		padding-left: 2em;
	}
	#p_muid_products .price .value {
		display: none;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$("#p_muid_products").delegate("div.product", "mouseenter", function(){
			$(this).addClass("ui-state-default ui-state-active");
		}).delegate("div.product", "mouseleave", function(){
			$(this).removeClass("ui-state-default ui-state-active");
		}).delegate("div.product", "click", function(){
			pines.get("<?php echo addslashes(pines_url('com_storefront', 'product', array('a' => '__alias__'))); ?>".replace("__alias__", $(this).children("div.product_alias").text()));
		}).delegate("button.add_cart", "click", function(e){
			var button = $(this);
			var product = button.closest("div.product");
			var guid = parseInt(product.find("div.product_guid").text());
			pines.com_storefront_add_to_cart(guid, product.find("div.name").text(), parseFloat(product.find("div.price > span.value").text()), product);
			e.stopPropagation();
			return false;
		});
	});
	// ]]>
</script>
<div style="height: .5em;">&nbsp;</div>
<?php foreach ($products as $key => $cur_product) { ?>
<div class="product ui-widget-content">
	<div class="product_guid" style="display: none;"><?php echo htmlspecialchars($cur_product->guid); ?></div>
	<div class="product_alias" style="display: none;"><?php echo htmlspecialchars($cur_product->alias); ?></div>
	<div class="product_right ui-state-default">
		<div class="padding_box">
			<div class="price"><?php echo $pines->com_storefront->format_price($cur_product->unit_price); ?><span class="value"><?php echo isset($cur_product->unit_price) ? round($cur_product->unit_price, 2) : ''; ?></span></div>
			<div class="product_button">
				<?php if (!$pines->config->com_storefront->catalog_mode) { ?>
				<button class="add_cart ui-state-default ui-corner-all ui-state-focus">Add to Cart</button>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="product_main">
		<?php if (isset($cur_product->thumbnail)) { ?>
		<div class="product_left">
			<div class="padding_box">
				<img class="thumb" alt="<?php echo htmlspecialchars($cur_product->name); ?>" src="<?php echo htmlspecialchars($cur_product->thumbnail); ?>" />
			</div>
		</div>
		<?php } ?>
		<div class="product_info<?php echo isset($cur_product->thumbnail) ? ' size_less' : ''; ?>">
			<div class="padding_box">
				<div class="name"><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'product', array('a' => $cur_product->alias))); ?>"><?php echo htmlspecialchars($cur_product->name); ?></a></div>
				<br style="clear: both;" />
				<div class="info"><strong>Model:</strong> <?php echo format_content($cur_product->manufacturer_sku); ?> | <strong>SKU:</strong> <?php echo format_content($cur_product->sku); ?></div>
				<br style="clear: both;" />
				<div class="desc"><?php echo format_content($cur_product->short_description); ?></div>
			</div>
		</div>
	</div>
	<div style="clear: both; height: 0; font-size: 0;">&nbsp;</div>
</div>
<?php } ?>
<br style="clear: both; height: 0; font-size: 0;" />