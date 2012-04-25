<?php
/**
 * Prints breadcrumbs example.
 *
 * @package Components\example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<ul class="breadcrumb">
	<li><a href="<?php echo htmlspecialchars(pines_url()); ?>" class="breadcrumb_item">Home</a> <span class="divider">&gt;</span></li>
	<li><span class="breadcrumb_item">Example Breadcrumbs</span> <span class="divider">&gt;</span></li>
	<li class="active"><span class="breadcrumb_item"><?php echo htmlspecialchars($this->position); ?></span></li>
</ul>