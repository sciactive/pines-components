<?php
/**
 * Pines Framework news feed.
 *
 * @package Components\about
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Pines News Feed';
?>
<style type="text/css">
#p_muid_widget .twtr-ft img {
	-webkit-box-shadow: 0px 0px 2px rgba(80, 80, 80, .4), inset 0px 0px 30px rgba(80, 80, 80, .4);
	-moz-box-shadow: 0px 0px 2px rgba(80, 80, 80, .4), inset 0px 0px 30px rgba(80, 80, 80, .4);
	box-shadow: 0px 0px 2px rgba(80, 80, 80, .4), inset 0px 0px 30px rgba(80, 80, 80, .4);
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
	padding: 3px;
}
</style>
<script type="text/javascript">
pines.loadjs('https://widgets.twimg.com/j/2/widget.js');
pines(function(){
	var docwrite = document.write;
	document.write = function(c){$("#p_muid_widget").append(c);};
	new TWTR.Widget({
		version: 2,
		type: 'profile',
		rpp: 8,
		interval: 30000,
		width: 'auto',
		height: 300,
		theme: {
			shell: {background: 'none', color: 'inherit'},
			tweets: {background: 'none', color: 'inherit', links: ''}
		},
		features: {scrollbar: true, loop: false, live: false, behavior: 'all'}
	}).render().setUser('pinesframework').start();
	document.write = docwrite;
});
</script>
<div id="p_muid_widget"></div>