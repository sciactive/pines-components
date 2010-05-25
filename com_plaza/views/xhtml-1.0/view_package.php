<?php
/**
 * Provides information and tools for a package.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = $this->package['name'];
$this->note = $this->package['author'];
?>
<div class="package">
	<div class="name">Package: <?php echo htmlentities($this->name); ?></div>
	<div class="type">Type: <?php echo htmlentities($this->package['type']); ?></div>
	<div class="version">Version: <?php echo htmlentities($this->package['version']); ?></div>
	<div class="license">License: <?php echo htmlentities($this->package['license']); ?></div>
	<div class="website">Website: <?php echo htmlentities($this->package['website']); ?></div>
	<div class="description">Description: <?php echo htmlentities($this->package['description']); ?></div>
</div>