<?php
/**
 * com_oxygenicons's configuration defaults.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'use_icon_sprite',
		'cname' => 'Use Icon Sprite',
		'description' => 'Use the icon sprites images instead.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'use_icon_sprite_cdn',
		'cname' => 'Use Icon Sprite CDN',
		'description' => 'Use the icon sprites images that is hosted on google cdn.',
		'value' => true,
		'peruser' => true,
	),
);

?>