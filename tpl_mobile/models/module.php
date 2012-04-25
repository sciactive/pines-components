<?php
/**
 * Template for a module.
 *
 * @package Templates
 * @subpackage mobile
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="module <?php echo htmlspecialchars($this->classes); ?>">
	<?php if ($this->show_title && (!empty($this->title) || !empty($this->note))) { ?>
	<div class="module_title page-header">
		<h2>
			<?php echo $this->title;
			if (!empty($this->note)) { ?>
				<small><?php echo $this->note; ?></small>
			<?php } ?>
		</h2>
	</div>
	<?php } ?>
	<div class="module_content ui-helper-reset ui-helper-clearfix">
		<?php echo $this->content; ?>
	</div>
</div>