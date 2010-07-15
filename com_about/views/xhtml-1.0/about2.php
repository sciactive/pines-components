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
<?php echo htmlentities($pines->info->name); ?> is a PHP application framework,
designed to be extensible and easy to manage. It is a
<a href="http://sciactive.com/" onclick="window.open(this.href); return false;">
SciActive</a> project written by Hunter Perrin. It allows rapid development,
highly customizable implementation, easy maintenance, and extreme flexibility.
The manager installs components to add functionality. For example, if the
manager wants to have a user management system, he can install com_user. When
com_user takes over user management for the system, it will prompt users to log
in and only give them permissions they have been allowed.
</p>
<p>
The admin can add features using components, and change the look and feel using
templates. The system will have a package manager, which will automatically
install any required components. <?php echo htmlentities($pines->info->name); ?>
is designed to allow maximum flexibility for the developer, and provide enough
tools and available libraries to make development of even very complex systems
easy. The admin can choose whatever database system he uses, even flat files,
and all the components which use an entity manager (the database abstraction
layer) will work.
</p>
<p id="p_muid_buttons" style="float: right;">
	<button type="button" onclick="window.open('http://sciactive.com/');">SciActive</button>
	<button type="button" onclick="window.open('http://sourceforge.net/projects/pines/');">Project Page</button>
	<button type="button" onclick="window.open('http://pines.sourceforge.net/pines-docs/');">API Documentation</button>
	<button type="button" onclick="window.open('http://sourceforge.net/donate/index.php?group_id=264165');">Donate</button>
</p>
<br style="clear: both; height: 1px;" />