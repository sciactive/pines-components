<?php
/**
 * Test form for PDF Display Editors
 *
 * @package Pines
 * @subpackage com_pdf
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'PDF Display Editor Testing'
	?>
<form class="pf-form" method="post" action="<?php echo htmlentities(pines_url('com_pdf', 'testprint')); ?>">
	<div class="pf-element display_edit" id="name">
		<label><span class="pf-label">Name</span>
			<input class="pf-field ui-widget-content" type="text" name="name" value="<?php echo $entity->name; ?>" /></label>
	</div>
	<div class="pf-element" id="age">
		<label><span class="pf-label">Age</span>
			<span class="pf-note">Not displayed.</span>
			<input class="pf-field ui-widget-content" type="text" name="age" value="<?php echo $entity->age; ?>" /></label>
	</div>
	<div class="pf-element display_edit" id="phone">
		<label><span class="pf-label">Phone</span>
			<input class="pf-field ui-widget-content" type="text" name="phone" value="<?php echo $entity->phone; ?>" /></label>
	</div>
	<div class="pf-element display_edit" id="favfood"><span class="pf-label">What's your favorite food?</span>
		<span class="pf-note">This will be your lunch.</span>
		<div class="pf-group">
			<label><input class="pf-field ui-widget-content" type="radio" name="favfood" value="Hot Dogs" <?php echo ($entity->favfood == 'Hot Dogs' ? 'checked="checked" ' : ''); ?>/>Hot Dogs</label><br />
			<label><input class="pf-field ui-widget-content" type="radio" name="favfood" value="Hamburgers" <?php echo ($entity->favfood == 'Hamburgers' ? 'checked="checked" ' : ''); ?>/>Hamburgers</label><br />
			<label><input class="pf-field ui-widget-content" type="radio" name="favfood" value="Cheeseburgers" <?php echo ($entity->favfood == 'Cheeseburgers' ? 'checked="checked" ' : ''); ?>/>Cheeseburgers</label><br />
			<label><input class="pf-field ui-widget-content" type="radio" name="favfood" value="Sushi" <?php echo ($entity->favfood == 'Sushi' ? 'checked="checked" ' : ''); ?>/>Sushi</label><br />
			<label><input class="pf-field ui-widget-content" type="radio" name="favfood" value="Pizza" <?php echo ($entity->favfood == 'Pizza' ? 'checked="checked" ' : ''); ?>/>Pizza</label><br />
			<label><input class="pf-field ui-widget-content" type="radio" name="favfood" value="Ham" <?php echo ($entity->favfood == 'Ham' ? 'checked="checked" ' : ''); ?>/>Ham</label><br />
			<label><input class="pf-field ui-widget-content" type="radio" name="favfood" value="Turkey" <?php echo ($entity->favfood == 'Turkey' ? 'checked="checked" ' : ''); ?>/>Turkey</label><br />
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="reset" value="Reset" />
	</div>
</form>