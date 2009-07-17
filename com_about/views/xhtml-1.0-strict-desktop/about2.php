<?php
/**
 * Displays XROOM's own "about" information.
 *
 * @package XROOM
 * @subpackage com_about
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');
?>
<p><strong>Version <?php echo $config->program_version; ?></strong></p>
<br />
<p>
<?php echo $config->program_title; ?> is a <a href="http://sciactive.com/">
SciActive</a> project written by Hunter Perrin. It is a PHP application
framework. Designed to be an extensible MVC based framework. The manager drops
in components to add the functionality he desires. For example, if the manager
wants to have a user management system, he simply drops in com_user. When
com_user takes over user management for the system, it will prompt users to log
in and only give them permissions they have been allowed.
</p>
<br />
<p>
The admin can add functions using the premade components, or write his own
components to provide additional functionality to the system. The system will
have a dependency verifier, which will inform the admin if he is missing
required components and where to get them. <?php echo $config->program_title; ?>
was designed to allow maximum flexibility for the developer, while still
providing a large enough base product to make development easy. The admin can
choose whatever database environment he uses, even flat files, and thanks to the
database abstraction layer, all the components will still work.
</p>