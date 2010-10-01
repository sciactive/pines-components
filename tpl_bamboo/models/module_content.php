<?php
/**
 * Template for a module.
 *
 * @package Pines
 * @subpackage tpl_bamboo
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="post">
	<?php if ($this->show_title && (!empty($this->title) || !empty($this->note))) { ?>
	<?php if (!empty($this->title)) { ?>
	<h2 class="title"><a href="#"><?php echo $this->title; ?></a></h2>
	<?php } if (!empty($this->note)) { ?>
	<p class="meta"><?php echo $this->note; ?></p>
	<?php }
	} ?>
	<div class="entry">
		<?php echo $this->content; ?>
		<br style="clear: both; height: 0;" />
	</div>
</div>