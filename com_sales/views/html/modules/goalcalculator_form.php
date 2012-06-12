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
		<label><span class="pf-label">Goals</span>
			<span class="pf-note">Separate by commas.</span>
			<span class="pf-note">Any unit will work. Values will be the same unit. ($, $100s, $1000s, etc)</span>
			<input class="pf-field" type="text" name="goals" size="36" value="<?php echo htmlspecialchars(!$this->goals ? '20, 30, 60, 80' : $this->goals); ?>" /></label>
	</div>
	<div class="pf-element">
		<label for="p_muid_yellow"><span class="pf-label">Choose Yellow Percentage</span>
			<span class="pf-note">Percentage that determines a yellow trend.</span></label>
		<div class="pf-group">
			<div class="input-append">
				<input id="p_muid_yellow" style="position: inherit;" class="pf-field" type="text" name="yellow_multiplier" size="3" value="<?php echo htmlspecialchars(!$this->yellow_multiplier ? '80' : $this->yellow_multiplier); ?>" /><span class="add-on">%</span>
			</div>
		</div>
	</div>
</div>