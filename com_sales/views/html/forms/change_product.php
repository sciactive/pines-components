<?php
/**
 * Display a form to change product on a sale.
 * 
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<form class="pf-form" id="p_muid_form" action="">
	<div class="pf-element pf-heading">
		<h3>Product to Change</h3>
	</div>
	<div class="pf-element">
		<?php foreach ($this->entity->products as $key => $cur_product) {
			if ($cur_product['delivery'] != 'warehouse')
				continue;
			foreach ($cur_product['stock_entities'] as $cur_stock) {
				if (!$cur_stock->in_array((array) $cur_product['returned_stock_entities']))
					continue 2;
			}
			$items_shown = true;
			?>
		<input class="pf-field ui-widget-content" type="radio" name="product" value="<?php echo htmlspecialchars($key); ?>" /> <?php echo htmlspecialchars($cur_product['entity']->name).' <small>x '.htmlspecialchars($cur_product['quantity']).'</small>'; ?><br/>
		<?php } if (!$items_shown) { ?>
		This sale has no warehouse items that haven't been fulfilled.
		<?php } ?>
	</div>
	<?php if ($items_shown) { ?>
	<div class="pf-element pf-heading">
		<h3>New Product</h3>
	</div>
	<div class="pf-element">
		<span class="pf-label">Product</span>
		<input class="pf-field product_box" type="text" name="new_product" value="" />
	</div>
	<?php } ?>
</form>