<?php
/**
 * A foobar module.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->entity = com_example_foobar::factory((int) $this->id);
// This would normally be escaped with htmlspecialchars(), but in this case, we
// want to allow HTML in the title.
$this->title = $this->entity->name;
?>
<div>
	<?php echo $this->entity->description; ?>
</div>