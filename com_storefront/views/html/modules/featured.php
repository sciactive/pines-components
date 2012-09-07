<?php
/**
 * Displays a featured product module.
 *
 * @package Components\storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

if ($this->id == 'random' || empty($this->id)) {
	$products = $pines->entity_manager->get_entities(
			array('class' => com_sales_product),
			array('&',
				'tag' => array('com_sales', 'product'),
				'strict' => array(
					array('enabled', true),
					array('show_in_storefront', true),
					array('featured', true)
				)
			)
		);
	$this->entity = $products[array_rand($products)];
} else {
	$this->entity = com_sales_product::factory((int) $this->id);
}
if (empty($this->title))
	$this->title = htmlspecialchars($this->entity->name);
?>
<style type="text/css">
	#p_muid_product {
		font-size: 1.2em;
		font-weight: bold;
	}
	#p_muid_product a, #p_muid_product a:link {
		text-decoration: none;
	}
	#p_muid_product .title {
		display: block;
	}
	#p_muid_product .thumbnail img {
		margin: 0;
		vertical-align: text-top;
		border: none;
	}
	#p_muid_product .price, #p_muid_product .button {
		margin: .2em;
	}
</style>
<span id="p_muid_product" class="com_storefront_featured_item">
	<script type="text/javascript">
		pines(function(){
			$("button.add_cart", "#p_muid_product").click(function(){
				pines.com_storefront_add_to_cart(<?php echo json_encode($this->entity->guid); ?>, <?php echo json_encode($this->entity->name); ?>, <?php echo (float) $this->entity->unit_price; ?>, $("#p_muid_product"));
			});
		});
	</script>
	<a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'product', array('a' => $this->entity->alias))); ?>">
		<?php if ($this->show_name) { ?>
		<span class="title"><?php echo htmlspecialchars($this->entity->name); ?></span>
		<?php } if ($this->show_thumbnail) { ?>
		<span class="thumbnail"><img class="thumb" alt="<?php echo !empty($this->alt_text) ? htmlspecialchars($this->alt_text) : htmlspecialchars($this->entity->name); ?>" src="<?php echo !empty($this->entity->featured_image) ? htmlspecialchars($this->entity->featured_image) : htmlspecialchars($this->entity->thumbnail); ?>" /></span>
		<?php } if ($this->show_price) { ?>
		<span class="price"><?php echo isset($this->entity->unit_price) ? $pines->com_storefront->format_price($this->entity->unit_price) : ''; ?></span>
		<?php } ?>
	</a>
	<?php if ($this->show_button && !$pines->config->com_storefront->catalog_mode) { ?>
	<span class="button"><button class="add_cart btn btn-primary">Add to Cart</button></span>
	<?php } ?>
</span>