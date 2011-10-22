<?php
/**
 * A widget module.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$this->entity = com_example_widget::factory((int) $this->id);
$this->title = $this->entity->name;
?>
<div>
	<?php echo $this->entity->description; ?>
</div>