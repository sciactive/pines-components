<?php
/**
 * A PO committed notification.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = '#to_name#, A PO shipping to your location has been committed.';
?>
Hi #to_name#,<br />
<br />
Purchase order, #po_number#, has been committed to be shipped to your address
specified below via #shipper#. The order is coming from #vendor#.<br />
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
				Purchase order destination:<br />
				<strong>#destination#</strong><br />
				#address#
			</td>
		</tr>
		<tr>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;" colspan="2">Reference Information</th>
		</tr>
		<tr>
			<td valign="top" colspan="2">
				PO Number: #po_number#<br />
				Reference: #ref_number#
			</td>
		</tr>
		<tr>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;" colspan="2">Tracking Information</th>
		</tr>
		<tr>
			<td valign="top" colspan="2">
				If tracking is available, the tracking links follow:<br />
				<span style="font-size: .75em">#tracking_link#</span>
			</td>
		</tr>
	</table>
</div>
<br />
<h2>Order Contents</h2>
<div>
	#products#
	<p>#comments#</p>
</div><br />
<br />
Regards,<br />
#system_name#