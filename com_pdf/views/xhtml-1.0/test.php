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
<form class="pform" method="post" action="<?php echo pines_url('com_pdf', 'testprint'); ?>">
	<div class="element display_edit" id="name">
		<label><span class="label">Name</span>
			<input class="field" type="text" name="name" value="<?php echo $entity->name; ?>" /></label>
	</div>
	<div class="element" id="age">
		<label><span class="label">Age</span>
			<span class="note">Not displayed.</span>
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
			<label><input class="field" type="radio" name="favfood" value="Ham" <?php echo ($entity->favfood == 'Ham' ? 'checked="checked" ' : ''); ?>/>Ham</label><br />
			<label><input class="field" type="radio" name="favfood" value="Turkey" <?php echo ($entity->favfood == 'Turkey' ? 'checked="checked" ' : ''); ?>/>Turkey</label><br />
		</div>
	</div>
	<div class="element buttons">
		<input class="button" type="submit" value="Submit" />
		<input class="button" type="reset" value="Reset" />
	</div>
</form>