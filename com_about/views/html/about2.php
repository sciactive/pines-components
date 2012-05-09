<?php
/**
 * Displays Pines' own "about" information.
 *
 * @package Components\about
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars("About {$pines->info->name}");
$this->note = htmlspecialchars("Version {$pines->info->version}");
?>
<p>
<?php echo htmlspecialchars($pines->info->name); ?> is a PHP application
framework from
<a href="http://sciactive.com/" target="_blank">SciActive</a>,
designed to be extensible and easy to use. It allows rapid development, highly
customizable implementation, easy maintenance, and unmatched flexibility.
</p>
<p>
New features can be added by downloading new components, and the look and feel
can be customized by downloading new templates.
<?php echo htmlspecialchars($pines->info->name); ?> uses a package manager
called Pines Plaza, which automatically installs any dependencies a component
needs. <?php echo htmlspecialchars($pines->info->name); ?> is designed to allow
maximum flexibility for the developer, and provide more than enough tools and
libraries to make development of even very complex systems easy.
<?php echo htmlspecialchars($pines->info->name); ?> supports different databases
by using a database abstraction system called an Entity Manager. Choosing the
right database is as simple as installing a new component.
</p>
<p class="btn-group" style="float: right;">
	<button type="button" class="btn btn-primary" onclick="pines.get('http://pinesframework.org/', null, '_blank');">Pines Framework</button>
	<button type="button" class="btn" onclick="pines.get('http://sourceforge.net/projects/pines/', null, '_blank');">Pines on SourceForge</button>
	<button type="button" class="btn" onclick="pines.get('http://sourceforge.net/donate/index.php?group_id=264165', null, '_blank');">Donate</button>
</p>
<br style="clear: both; height: 1px;" />