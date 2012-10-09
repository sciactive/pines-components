<?php
/**
 * Displays a note to the user.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="pf-form">
	<div class="pf-element">
		<span class="pf-label">
		<?php
			if (isset($this->link))
				$this->header = '<a href="'.htmlspecialchars($this->link).'">'.htmlspecialchars($this->header).' &raquo;</a>';
			echo $this->header;
		?>
		</span>
		<span class="pf-note"><?php echo str_replace("\n", "<br />", $this->message); ?></span>
	</div>
</div>