<?php
/**
 * Notify user of unsafe file permissions.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Unsafe File Permissions Detected';
?>
<p>
	Unsafe file permissions have been detected on the certificate authorities
	directory. This directory and its contents should not be writable by Pines.
</p>
<p>
	The best solution is to set this directory to a different user and group by running:<br />
	<code style="font-size: .8em;">chown -Rv root:root <?php echo htmlspecialchars(P_BASE_PATH); ?>components/com_plaza/includes/cache/certs/authorities/</code><br />
</p>
<p>
	This directory is used to store authority certificates. These certificates
	are used to determine whether a package has been compromised before
	installing it. Therefore, if this directory is writable, a malicious script
	could add its own certificate, allowing it to install software from any
	source it chooses.
</p>