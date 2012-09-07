<?php
/**
 * Provides a form for public entering a raffle.
 *
 * @package Components\raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars($this->entity->name);
$this->note = 'Fill out this form to enter.';
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_raffle', 'raffle/enter')); ?>">
	<div class="pf-element">
		<label><span class="pf-label">First Name</span>
			<input class="pf-field" type="text" name="first_name" size="24" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Last Name</span>
			<input class="pf-field" type="text" name="last_name" size="24" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Email</span>
			<input class="pf-field" type="email" name="email" size="24" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Phone</span>
			<input class="pf-field" type="tel" name="phone" size="24" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($this->entity->guid); ?>" />
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
	</div>
</form>