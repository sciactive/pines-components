<?php
/**
 * Displays Pines' own "about" information.
 *
 * @package Pines
 * @subpackage com_about
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "About {$pines->info->name}";
?>
<p><strong>Version <?php echo $pines->info->version; ?> <?php echo $pines->info->identifier; ?></strong></p>
<p>
<?php echo $pines->info->name; ?> is a <a href="http://sciactive.com/">
SciActive</a> project written by Hunter Perrin. It is a PHP application
framework, designed to be an extensible MVC based framework. It allows easy
development, easy implementation, easy maintenance, and extreme flexibility. The
manager drops in components to add the functionality he desires. For example, if
the manager wants to have a user management system, he simply drops in com_user.
When com_user takes over user management for the system, it will prompt users to
log in and only give them permissions they have been allowed.
</p>
<p>
The admin can add functions using the premade components, or write his own
components to provide additional functionality to the system. The system will
have a dependency verifier, which will inform the admin if he is missing
required components and where to get them. <?php echo $pines->info->name; ?>
was designed to allow maximum flexibility for the developer, while still
providing a large enough base product to make development easy. The admin can
choose whatever database environment he uses, even flat files, and thanks to the
database abstraction layer, all the components will still work.
</p>