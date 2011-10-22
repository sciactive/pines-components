<?php
/**
 * Provide a form to generate a repository certificate.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_repository/gencert') )
	punt_user(null, pines_url('com_repository', 'gencert'));

$module = new module('com_repository', 'form_certificate', 'content');

?>