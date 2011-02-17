<?php
/**
 * Display a form to swap salespeople.
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
<form class="pf-form" action="">
	<div class="pf-element pf-heading">
		<h1>Items to Swap</h1>
	</div>
	<div class="pf-element">
		<?php foreach ($this->entity->products as $key => $cur_item) {
			$serial = $cur_item['serial'] ? $cur_item['serial'] : 'No Serial'; ?>
			<input class="pf-field ui-widget-content" type="radio" name="swap_item" value="<?php echo $key; ?>" /> <?php echo $cur_item['entity']->name.' ('.$serial.') - '.$cur_item['salesperson']->name; ?><br/>
		<?php } ?>
	</div>
	<div class="pf-element pf-heading">
		<h1>Salesperson</h1>
	</div>
	<div class="pf-element">
		<span class="pf-note">Start typing to select a Salesperson.</span>
		<input class="pf-field ui-widget-content ui-corner-all salesperson_box" type="text" name="salesperson" value="" />
	</div>
</form>