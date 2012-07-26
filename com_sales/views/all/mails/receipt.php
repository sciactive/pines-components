<?php
/**
 * A sale receipt.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = '#to_first_name#, Here\'s your receipt for Sale #sale_id# at #system_name#.';
?>
Hi #to_name#,<br />
<br />
Here is the receipt for your recent transaction at #system_name#:<br />
<br />
<hr />
<br /><br /><br />
<div>
	#receipt#
</div><br />
<br />
Regards,<br />
#system_name#