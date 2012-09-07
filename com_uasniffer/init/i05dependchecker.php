<?php
/**
 * Add a browser dependency checker.
 *
 * @package Components\uasniffer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Check the client's user agent string.
 *
 * Uses simple_parse() to provide simple logic.
 *
 * @param string $value The value to check.
 * @param bool $help Whether to return the help for this checker.
 * @return bool|array The result of the check, or the help array.
 */
function com_uasniffer__browser_check($value, $help = false) {
	global $pines;
	if ($help) {
		$return = array();
		$return['cname'] = 'Browser Checker';
		$return['description'] = <<<'EOF'
Check against the client's browser.

This checker uses the "user agent string" submitted by the user's browser. This
is a fairly reliable way to determine what browser and device they are using to
view the site. You can use this to provide different content or layouts
depending on the browser or device the client is using. For example, you can
make a module show only to Internet Explorer users, or make a header that
doesn't show if the user is on a mobile phone.
EOF;
		$return['syntax'] = <<<'EOF'
The following values are available:

* `desktop` - The client is using a desktop browser, or the client requested the desktop version from a mobile browser.
* `desktop-real` - The client is using a desktop browser.
* `mobile` - The client is using a mobile browser, or the client requested the mobile version.
* `mobile-real` - The client is using a mobile browser.
* `tablet` - The client is using a tablet, such as iPad or Android tablet.
* `gameconsole` - The client is using a gaming console.
* `bot` - The client is a web crawler.
* `os-windows` - The client is using Windows.
* `os-linux` - The client is using Linux.
* `os-mac` - The client is using Macintosh.
* `os-solaris` - The client is using Solaris.
* `os-bsd` - The client is using BSD.
* `os-ios` - The client is using iOS (iPhone, iPad, iPod).
* `os-android` - The client is using Android.
* `os-winphone` - The client is using Windows Phone.
* `browser-ie` - The client is using Internet Explorer.
* `browser-ie6` - The client is using Internet Explorer 6.
* `browser-ie7` - The client is using Internet Explorer 7.
* `browser-ie8` - The client is using Internet Explorer 8.
* `browser-ie9` - The client is using Internet Explorer 9.
* `browser-firefox` - The client is using Firefox.
* `browser-safari` - The client is using Safari.
* `browser-chrome` - The client is using Chrome.
* `browser-opera` - The client is using Opera.
* `console-ds` - The client is using a Nintendo DS.
* `console-ps3` - The client is using a Sony PlayStation 3.
* `console-psp` - The client is using a Sony PSP.
* `console-wii` - The client is using a Nintendo Wii.
EOF;
		$return['examples'] = <<<'EOF'
browser-ie7
:	Check that the user is using Internet Explorer 7.

mobile&os-android&!tablet
:	Check that the user is using an Android smartphone.

os-mac&(browser-firefox|browser-safari)
:	Check that the user is using a Mac with either Firefox or Safari.
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
		return $pines->depend->simple_parse($value, 'com_uasniffer__browser_check');
	switch ($value) {
		default:
			// Just check that there is a user agent string.
			return isset($_SERVER['HTTP_USER_AGENT']);
			break;
		case 'desktop':
		case 'desktop-real':
			// If it's not mobile, we'll just assume it's desktop.
			$truth = !preg_match('/(IEMobile|Windows CE|NetFront|PlayStation|PLAYSTATION|like Mac OS X|MIDP|UP\.Browser|Symbian|Nintendo|Android)/', $_SERVER['HTTP_USER_AGENT']);
			if ($value == 'desktop-real')
				return $truth;
			return $truth xor ($_COOKIE['com_uasniffer_switch'] == 'true');
			break;
		case 'mobile':
		case 'mobile-real':
			if ($pines->config->com_uasniffer->use_simple_mobile_detection) {
				// This nice regex is available at http://johannburkard.de/blog/www/mobile/simple-mobile-phone-detection.html.
				$truth = preg_match('/(IEMobile|Windows CE|NetFront|PlayStation|PLAYSTATION|like Mac OS X|MIDP|UP\.Browser|Symbian|Nintendo|Android)/', $_SERVER['HTTP_USER_AGENT']);
			} else {
				// These beautiful regexes are generously provided by http://detectmobilebrowser.com.
				$truth = (
					preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $_SERVER['HTTP_USER_AGENT']) ||
					preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($_SERVER['HTTP_USER_AGENT'], 0, 4))
				);
			}
			if ($value == 'mobile-real')
				return $truth;
			return $truth xor ($_COOKIE['com_uasniffer_switch'] == 'true');
			break;
		case 'tablet':
			// Sometimes Android doesn't put the term "Mobile" in tablet UAs.
			if (preg_match('/android/i', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/mobile/i', $_SERVER['HTTP_USER_AGENT']))
				return true;
			return preg_match('/(tablet|ipad|GT-P1000|SGH-T849|SHW-M180S|SCH-I800|xoom)/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'gameconsole':
			return preg_match('/(bunjalloo|playstation|wii)/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'bot':
			return preg_match('/(spider|crawl|slurp|bot)/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'os-windows':
			return preg_match('/windows/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'os-linux':
			return preg_match('/linux/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'os-mac':
			return preg_match('/mac\s+os\s+[x9]/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'os-solaris':
			return preg_match('/solaris/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'os-bsd':
			return preg_match('/bsd/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'os-ios':
			return preg_match('/(iPhone|iPad|iPod).*like Mac OS X/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'os-android':
			return preg_match('/android/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'os-winphone':
			return preg_match('/windows\s+phone/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'browser-ie':
			return (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/Opera/', $_SERVER['HTTP_USER_AGENT']));
			break;
		case 'browser-ie6':
			return (preg_match('/MSIE 6\.0/', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/Opera/', $_SERVER['HTTP_USER_AGENT']));
			break;
		case 'browser-ie7':
			return (preg_match('/MSIE 7\.0/', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/Opera/', $_SERVER['HTTP_USER_AGENT']));
			break;
		case 'browser-ie8':
			return (preg_match('/MSIE 8\.0/', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/Opera/', $_SERVER['HTTP_USER_AGENT']));
			break;
		case 'browser-ie9':
			return (preg_match('/MSIE 9\.0/', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/Opera/', $_SERVER['HTTP_USER_AGENT']));
			break;
		case 'browser-firefox':
			return (preg_match('/Firefox/', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/Opera/', $_SERVER['HTTP_USER_AGENT']));
			break;
		case 'browser-safari':
			return (preg_match('/Safari/', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/Android/', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/Opera/', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/Chrom(e|ium)/', $_SERVER['HTTP_USER_AGENT']));
			break;
		case 'browser-chrome':
			return preg_match('/Chrom(e|ium)/', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'browser-opera':
			return preg_match('/Opera/', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'console-ds':
			return preg_match('/Nintendo\s+DS/', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'console-ps3':
			return preg_match('/(ps3|playstation\s+3)/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'console-psp':
			return preg_match('/playstation\s+portable/i', $_SERVER['HTTP_USER_AGENT']);
			break;
		case 'console-wii':
			return preg_match('/wii/i', $_SERVER['HTTP_USER_AGENT']);
			break;
	}
}

$pines->depend->checkers['browser'] = 'com_uasniffer__browser_check';

?>