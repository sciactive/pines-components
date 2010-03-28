<?php
/**
 * Template for a module.
 *
 * @package Pines
 * @subpackage tpl_pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="module ui-widget-content ui-corner-all ui-clearfix <?php echo htmlentities($this->classes); ?>">
	<?php if ($this->show_title && (!empty($this->title) || !empty($this->note))) { ?>
	<div class="module_title ui-widget-header ui-corner-all">
		<div class="module_maximize ui-state-default ui-corner-all"><span class="ui-icon ui-icon-arrow-4-diag"></span></div>
		<?php if (!empty($this->title)) { ?>
			<h2><?php echo $this->title; ?></h2>
		<?php } ?>
		<?php if (!empty($this->note)) { ?>
			<p><?php echo $this->note; ?></p>
		<?php } ?>
	</div>
	<?php } ?>
	<div class="module_content">
		<?php echo $this->content; ?>
	</div>
</div>