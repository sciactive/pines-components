<?php
/**
 * Display a form to swap salespeople.
 * 
 * @package Components
 * @subpackage sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<form class="pf-form" action="">
	<div class="pf-element pf-heading">
		<h3>Items to Swap</h3>
	</div>
	<div class="pf-element">
		<?php foreach ($this->entity->products as $key => $cur_item) {
			$serial = $cur_item['serial'] ? $cur_item['serial'] : 'No Serial'; ?>
			<input class="pf-field ui-widget-content" type="radio" name="swap_item" value="<?php echo htmlspecialchars($key); ?>" /> <?php echo htmlspecialchars($cur_item['entity']->name.' ('.$serial.') - '.$cur_item['salesperson']->name); ?><br/>
		<?php } ?>
	</div>
	<div class="pf-element pf-heading">
		<h3>New Salesperson</h3>
	</div>
	<div class="pf-element">
		<span class="pf-label">Salesperson</span>
		<span class="pf-note">Start typing to select a Salesperson.</span>
		<input class="pf-field salesperson_box" type="text" name="salesperson" value="" />
	</div>
</form>