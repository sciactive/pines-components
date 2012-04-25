<?php
/**
 * List issue types.
 *
 * @package Components
 * @subpackage hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/listissuetypes') )
	punt_user(null, pines_url('com_hrm', 'issue/list'));

$pines->com_hrm->list_issue_types();

?>