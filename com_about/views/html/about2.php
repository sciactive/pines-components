<?php
/**
 * Displays Pines' own "about" information.
 *
 * @package Pines
 * @subpackage com_about
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "About {$pines->info->name}";
$this->note = "Version {$pines->info->version}";
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		if (!$.fn.buttonset)
			return;
		$("#p_muid_buttons").buttonset().children("button").button();
	});
	// ]]>
</script>
<p>
<?php echo htmlspecialchars($pines->info->name); ?> is a PHP application
framework from
<a href="http://sciactive.com/" onclick="window.open(this.href); return false;">SciActive</a>,
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
<p id="p_muid_buttons" style="float: right;">
	<button type="button" onclick="window.open('http://sciactive.com/');">SciActive</button>
	<button type="button" onclick="window.open('http://sourceforge.net/projects/pines/');">Project Page</button>
	<button type="button" onclick="window.open('http://pines.sourceforge.net/pines-docs/');">API Documentation</button>
	<button type="button" onclick="window.open('http://sourceforge.net/donate/index.php?group_id=264165');">Donate</button>
</p>
<br style="clear: both; height: 1px;" />