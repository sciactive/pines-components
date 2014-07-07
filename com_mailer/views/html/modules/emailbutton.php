<?php
/**
 * The module email button to be placed on grids.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$pines->com_mailer->load_emailbutton();
?>
<div id="p_mailer_send_email" data-url="<?php echo pines_url('com_mailer', 'emailbutton'); ?>">
	
</div>