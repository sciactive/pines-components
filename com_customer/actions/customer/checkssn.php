<?php
/**
 * Determine if a ssn is available.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_customer->check_ssn)
	throw new HttpClientException(null, 404);

$pines->page->override = true;
header('Content-Type: application/json');

if (!empty($_REQUEST['id']))
	$id = intval($_REQUEST['id']);

$pines->page->override_doc(json_encode($pines->com_customer->check_ssn($_REQUEST['ssn'], $id)));

?>