<?php
/**
 * Provides a password form to sign packages.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sign Packages';
?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_repository', 'signpackage')); ?>">
	<div class="pf-element pf-heading">
		<p>A password is required to sign packages. Please enter the password below and try again.</p>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Password</span>
			<input class="pf-field" type="password" name="password" size="24" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
	</div>
</form>