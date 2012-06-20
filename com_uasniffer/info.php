<?php
/**
 * com_uasniffer's information.
 *
 * @package Components\uasniffer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'User Agent (Browser) Sniffer',
	'author' => 'SciActive',
	'version' => '1.0.2beta',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Provides browser specific options',
	'description' => 'Uses the client\'s user agent string to determine what browser they\'re using. You can provide browser specific configuration, including configuration for mobile browsers.',
	'depend' => array(
		'pines' => '<2'
	),
);

?>