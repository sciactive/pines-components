<?php
/**
 * A return receipt.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Grey Vugrin <greyvugrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = '#to_first_name#, Sale #sale_id# at #system_name# has been returned.';
?>
Hi #to_name#,<br />
<br />
Your recent transaction at #system_name# is now <strong>returned</strong>. Here is the receipt:<br />
<br />
<hr />
<h3 align="center" style="text-align: center">RETURN</h3>
<hr style="border-bottom-width: 0; border-style: dashed; " />
<br /><br /><br />
<div>
	#receipt#
</div>
<br />
<br />
Regards,<br />
#system_name#