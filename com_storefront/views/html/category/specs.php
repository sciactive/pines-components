<?php
/**
 * Shows category specs.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Narrow Selection';
// Build a list of specs.
$specs = $this->entity->get_specs_all();
if (!$specs) {
	$this->detach();
	return;
}

?>
<style type="text/css">
	#p_muid_specs select {
		display: block;
	}
	/* ]> */
</style>
<div id="p_muid_specs" class="com_storefront_specs">
<?php
// Sort the specs.
$pines->com_sales->sort_specs($specs);

foreach ($specs as $cur_spec) {
	switch ($cur_spec['type']) {
		case 'heading': ?>
<h2><?php echo htmlspecialchars($cur_spec['name']); ?></h2>
<?php
			break;
		case 'bool':
			if (!$cur_spec['show_filter'])
				continue;
			?>
<div>
	<strong><?php echo htmlspecialchars($cur_spec['name']); ?>:</strong>
	<select name="">
		<option value="">Any</option>
		<option value="true">Yes</option>
		<option value="false">No</option>
	</select>
</div>
<?php
			break;
	}
	//var_dump($cur_spec);
}

//var_dump($specs);

?>
</div>