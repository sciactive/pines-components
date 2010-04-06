<?php
/**
 * Confirms that the user really wants to run a benchmark.
 *
 * @package Pines
 * @subpackage com_entitytools
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Entity Manager Import';
?>
<form enctype="multipart/form-data" class="pform" method="post" action="<?php echo htmlentities(pines_url('com_entitytools', 'import')); ?>">
	<div class="element heading">
		<p>
			Use this feature to import entities from a file made by a Pines
			Entity Manager.
		</p>
	</div>
	<div class="element">
		<label><span class="label">Upload File</span>
			<input class="field ui-widget-content" type="file" name="entity_import" /></label>
	</div>
	<div class="element buttons">
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
	</div>
</form>