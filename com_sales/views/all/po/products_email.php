<?php
/**
 * Provides a product list for a PO email.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<table width="100%" cellpadding="3" cellspacing="2" style="border-bottom: 1px solid #333;">
	<tr>
		<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;">Qty</th>
		<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;">SKU</th>
		<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;">Item</th>
	</tr>
	<?php foreach ($this->entity->products as $cur_product) { ?>
	<tr>
		<td valign="top" style="text-align: right;"><?php echo htmlspecialchars($cur_product['quantity']); ?></td>
		<td valign="top" style="text-align: left;"><?php echo htmlspecialchars($cur_product['entity']->sku); ?></td>
		<td valign="top" style="text-align: left; width: 85%;"><?php echo htmlspecialchars($cur_product['entity']->name); ?></td>
	</tr>
	<?php } ?>
</table>