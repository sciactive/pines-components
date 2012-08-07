<?php
/**
 * A sale shipment notification.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = '#to_first_name#, Your recent order has shipped, Sale #sale_id#.';
?>
Hi #to_name#,<br />
<br />
Thank you for shopping with us. Your order has been shipped to your address
specified below via #shipper#. This now completes your order.<br />
<br />
<hr />
<br /><br /><br />
<h2>Shipment Details</h2>
<div>
	<table width="100%" cellpadding="3" cellspacing="2" style="border-bottom: 1px solid #333;">
		<tr>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;">Estimated Delivery Date</th>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;">Shipping Address</th>
		</tr>
		<tr>
			<td valign="top" style="width: 50%" width="50%">
				#eta#
			</td>
			<td valign="top" style="width: 50%" width="50%">
				Your order was sent to:<br />
				#address#
			</td>
		</tr>
	</table>
</div>
<br />
<h2>Shipment Contents</h2>
<div>
	#packing_list#
</div><br />
<br />
Regards,<br />
#system_name#