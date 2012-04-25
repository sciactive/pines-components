<?php
/**
 * Dislpays a message regarding incorrect permissions.
 *
 * @package Components
 * @subpackage user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Incorrect User Permission Settings';
?>
<p>It appears that your default component is denying you permission to use it. This is most likely caused by incorrect permission settings in your user account. Please notify an administrator about this error.</p>