<?php
/**
 * Add the dependency checker for a package.
 *
 * @package Components\package
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Check if a package is installed and check its version.
 *
 * Don't use this checker to check for components. There is already a better
 * checker for that.
 *
 * You can either check only that the package is installed, by using its
 * name, or that the package's version matches a certain version/range.
 *
 * Operators should be placed betwen the package name and the version
 * number to test. Such as, "cms-suite>=1.1.0". The available operators
 * are:
 *
 * - =
 * - <
 * - >
 * - <=
 * - >=
 * - <>
 *
 * Uses simple_parse() to provide simple logic.
 *
 * @param string $value The value to check.
 * @param bool $help Whether to return the help for this checker.
 * @return bool|array The result of the check, or the help array.
 */
function com_package__check_package($value, $help = false) {
	global $pines;
	if ($help) {
		$return = array();
		$return['cname'] = 'Package Checker';
		$return['description'] = <<<'EOF'
Check if a package is installed and check its version.
EOF;
		$return['syntax'] = <<<'EOF'
You can either check only that the package is installed by using its name, or
that the package's version matches a certain version/range.

Operators should be placed between the package name and the version number to
test. Such as, "cms-suite>=1.1.0". The available operators are:

* `=`
* `<`
* `>`
* `<=`
* `>=`
* `<>`
EOF;
			$return['examples'] = <<<'EOF'
com_user
:	Check that the com_user package is installed.

com_repository>=1.0.0&com_repository-data
:	Check that the com_repository package is installed and that it is at least
	version 1.0.0, and that the com_repository-data package is installed.

com_customer&!com_storefront
:	Check that the com_customer package is installed, and that the
	com_storefront package is not.
EOF;
		$return['simple_parse'] = true;
		return $return;
	}
	if (
			strpos($value, '&') !== false ||
			strpos($value, '|') !== false ||
			strpos($value, '!') !== false ||
			strpos($value, '(') !== false ||
			strpos($value, ')') !== false
		)
		return $pines->depend->simple_parse($value, 'com_package__check_package');
	$package_name = preg_replace('/([a-z0-9_-]+)([<>=]{1,2})(.+)/S', '$1', $value);
	$package = com_package_package::factory($package_name);
	if (!isset($package) || !$package->is_installed())
		return false;
	if ($package_name != $value) {
		$compare = preg_replace('/([a-z0-9_-]+)([<>=]{1,2})(.+)/S', '$2', $value);
		$required = preg_replace(' /([a-z0-9_-]+)([<>=]{1,2})(.+)/S', '$3', $value);
		return version_compare($package->info['version'], $required, $compare);
	}
	return true;
}

$pines->depend->checkers['package'] = 'com_package__check_package';

?>