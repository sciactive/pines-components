<?php
/**
 * Displays user defined "about" information.
 *
 * @package Components
 * @subpackage about
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars("About {$pines->config->system_name}");
?>
<p><?php echo htmlspecialchars($pines->config->com_about->description, ENT_COMPAT, '', false); ?></p>