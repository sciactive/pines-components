/**
 * Simple JS Option
 * 
 * Toggle in Configuration.
 *
 * Adjusted footer.
 *
 * @package Templates\simple
 * @license http://opensource.org/licenses/MIT
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright Angela Murrell
 */
setTimeout(function(){
    var el = $("#footer");
    el.height($(document).height() - el.offset().top - 1);
}, 1000);