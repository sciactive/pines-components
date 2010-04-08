<?php
/**
 * Displays a note to the user.
 *
 * @package com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="pform" id="notemodule">
	<div class="element">
		<span class="label"><?php echo $this->header; ?></span>
		<span class="note"><?php echo str_replace("\n", "<br />\n", $this->message); ?></span>
	</div>
</div>