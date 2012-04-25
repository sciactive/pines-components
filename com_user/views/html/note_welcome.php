<?php
/**
 * Displays a welcome note to the user.
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
$this->title = 'Welcome to '.htmlspecialchars($pines->config->system_name);
$this->note = 'You are now registered and logged in.';
?>
<div>
	<?php echo htmlspecialchars($pines->config->com_user->reg_message_welcome); ?>
</div>