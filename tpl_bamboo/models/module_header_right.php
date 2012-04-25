<?php
/**
 * Template for a module.
 *
 * @package Templates\bamboo
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="module" style="float: right; clear: right;">
	<?php if ($this->show_title && (!empty($this->title) || !empty($this->note))) { ?>
	<?php if (!empty($this->title)) { ?>
		<h2><?php echo $this->title; ?></h2>
	<?php } if (!empty($this->note)) { ?>
		<p><?php echo $this->note; ?></p>
	<?php }
	} ?>
	<?php echo $this->content; ?>
	<br style="clear: both; height: 0;" />
</div>