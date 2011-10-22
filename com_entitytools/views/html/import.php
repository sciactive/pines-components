<?php
/**
 * Confirms that the user really wants to run a benchmark.
 *
 * @package Pines
 * @subpackage com_entitytools
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Entity Manager Import';
?>
<form enctype="multipart/form-data" class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_entitytools', 'import')); ?>">
	<div class="pf-element pf-heading">
		<p>
			Use this feature to import entities from a file made by a Pines
			Entity Manager.
		</p>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Upload File</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="file" name="entity_import" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
	</div>
</form>