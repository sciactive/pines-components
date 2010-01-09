<?php
/**
 * Test form for PDF Display Editors
 *
 * @package Pines
 * @subpackage com_pdf
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'PDF Display Editor Testing'
?>
<div class="pform">
	<div class="element display_edit" id="name">
		<label><span class="label">Name</span>
		<input class="field" type="text" name="name" value="<?php echo $entity->name; ?>" /></label>
	</div>
	<div class="element" id="age">
		<label><span class="label">Age</span>
		<input class="field" type="text" name="age" value="<?php echo $entity->age; ?>" /></label>
	</div>
	<div class="element display_edit" id="phone">
		<label><span class="label">Phone</span>
		<input class="field" type="text" name="phone" value="<?php echo $entity->phone; ?>" /></label>
	</div>
	<div class="element display_edit" id="favfood"><span class="label">What's your favorite food?</span>
		<span class="note">This will be your lunch.</span>
		<div class="group">
			<label><input class="field" type="radio" name="favfood" value="Hot Dogs" <?php echo ($entity->favfood == 'Hot Dogs' ? 'checked="checked" ' : ''); ?>/>Hot Dogs</label><br />
			<label><input class="field" type="radio" name="favfood" value="Hamburgers" <?php echo ($entity->favfood == 'Hamburgers' ? 'checked="checked" ' : ''); ?>/>Hamburgers</label><br />
			<label><input class="field" type="radio" name="favfood" value="Cheeseburgers" <?php echo ($entity->favfood == 'Cheeseburgers' ? 'checked="checked" ' : ''); ?>/>Cheeseburgers</label><br />
			<label><input class="field" type="radio" name="favfood" value="Sushi" <?php echo ($entity->favfood == 'Sushi' ? 'checked="checked" ' : ''); ?>/>Sushi</label><br />
			<label><input class="field" type="radio" name="favfood" value="Pizza" <?php echo ($entity->favfood == 'Pizza' ? 'checked="checked" ' : ''); ?>/>Pizza</label><br />
			<label><input class="field" type="radio" name="favfood" value="Steak" <?php echo ($entity->favfood == 'Steak' ? 'checked="checked" ' : ''); ?>/>Steak</label><br />
			<label><input class="field" type="radio" name="favfood" value="Ham" <?php echo ($entity->favfood == 'Ham' ? 'checked="checked" ' : ''); ?>/>Ham</label><br />
			<label><input class="field" type="radio" name="favfood" value="Turkey" <?php echo ($entity->favfood == 'Turkey' ? 'checked="checked" ' : ''); ?>/>Turkey</label><br />
			<label><input class="field" type="radio" name="favfood" value="Mashed Potatoes" <?php echo ($entity->favfood == 'Mashed Potatoes' ? 'checked="checked" ' : ''); ?>/>Mashed Potatoes</label><br />
			<label><input class="field" type="radio" name="favfood" value="Eggs" <?php echo ($entity->favfood == 'Eggs' ? 'checked="checked" ' : ''); ?>/>Eggs</label><br />
			<label><input class="field" type="radio" name="favfood" value="Bacon" <?php echo ($entity->favfood == 'Bacon' ? 'checked="checked" ' : ''); ?>/>Bacon</label><br />
			<label><input class="field" type="radio" name="favfood" value="Cake" <?php echo ($entity->favfood == 'Cake' ? 'checked="checked" ' : ''); ?>/>Cake</label><br />
			<label><input class="field" type="radio" name="favfood" value="Ice Cream" <?php echo ($entity->favfood == 'Ice Cream' ? 'checked="checked" ' : ''); ?>/>Ice Cream</label><br />
		</div>
	</div>
	<div class="element buttons">
		<input type="hidden" name="pdf_file" value="<?php echo $entity->pdf_file; ?>" />
		<input class="button" type="submit" value="Submit" />
		<input class="button" type="reset" value="Reset" />
	</div>
</div>