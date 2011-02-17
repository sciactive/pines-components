<?php
/**
 * Display a form to swap inventory on a sale.
 * 
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_form {
		padding-left: 25px;
	}
	/* ]]> */
</style>
<form class="pf-form" id="p_muid_form" action="">
	<div class="pf-element pf-heading">
		<h1>Item to Swap</h1>
	</div>
	<div class="pf-element">
		<?php foreach ($this->entity->products as $cur_item) {
			$serial = $cur_item['serial'] ? $cur_item['serial'] : 'No Serial';
			?>
			<input class="pf-field ui-widget-content" type="radio" name="swap_item" value="<?php echo $cur_item['sku'].':'.$cur_item['serial']; ?>" /> <?php echo $cur_item['entity']->name.' ('.$serial.')'; ?><br/>
		<?php } ?>
	</div>
	<div class="pf-element pf-heading">
		<h1>New Item</h1>
	</div>
	<div class="pf-element">
		<span class="pf-note">Item Serial</span>
		<input class="pf-field ui-widget-content ui-corner-all" type="text" name="new_serial" value="" />
	</div>
</form>