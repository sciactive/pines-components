<?php
/**
 * Lists packages.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Installed Software';
?>
<div id="p_muid_packages">
	<?php foreach($this->db['packages'] as $key => $package) { ?>
	<div class="package">
		<div class="name">
			<a href="<?php echo htmlentities(pines_url('com_plaza', 'viewpackage', array('name' => $key))); ?>"><?php echo htmlentities($package['name']); ?></a>
		</div>
		<div class="author"><?php echo htmlentities($package['author']); ?></div>
		<div class="short_description"><?php echo htmlentities($package['short_description']); ?></div>
	</div>
	<?php } ?>
</div>