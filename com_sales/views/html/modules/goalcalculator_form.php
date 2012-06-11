<?php
/**
 * Provides a form for the user to choose goal calculator module options.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>

<div class="pf-form">
	<div class="pf-element">
		<label><span class="pf-label">Choose Goals (1-1000)</span>
			<span class="pf-note">Separate by commas.</span>
			<input class="pf-field" type="text" name="goals" size="36" value="<?php echo (!$this->goals) ? "20, 30, 60, 80" : $this->goals; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Choose Yellow Percentage</span>
			<div class="input-append">
			<span class="pf-note">Percentage that determines a yellow trend.</span>
			<input style="position:inherit;" class="pf-field" type="text" name="yellow_multiplier" size="3" value="<?php echo (!$this->yellow_multiplier) ? "80" : $this->yellow_multiplier; ?>" /><span class="add-on">%</span>
			</div>
		</label>
	</div>
</div>