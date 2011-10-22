<?php
/**
 * com_raffle's configuration defaults.
 *
 * @package Pines
 * @subpackage com_raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'global_raffles',
		'cname' => 'Globalize Raffles',
		'description' => 'Ensure that every user can access all raffles by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
);

?>