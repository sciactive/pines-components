<?php
/**
 * Displays a note to the user.
 *
 * @package com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="pf-form" id="notemodule">
	<div class="pf-element">
		<span class="pf-label"><?php echo $this->header; ?></span>
		<span class="pf-note"><?php echo str_replace("\n", "<br />\n", $this->message); ?></span>
	</div>
</div>