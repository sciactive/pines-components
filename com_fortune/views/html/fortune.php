<?php
/**
 * Prints a fortune.
 *
 * @package Pines
 * @subpackage com_fortune
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Fortune';
?>
<div>
	<?php echo htmlspecialchars($this->fortune); ?>
</div>