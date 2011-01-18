<?php
/**
 * com_tinymce's configuration defaults.
 *
 * @package Pines
 * @subpackage com_tinymce
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'skin',
		'cname' => 'Skin',
		'description' => 'The skin to use for the advanced theme.',
		'value' => 'default',
		'options' => array(
			'Default' => 'default',
			'Cirkuit' => 'cirkuit',
			'Office 2007 (Blue)' => 'o2k7-blue',
			'Office 2007 (Silver)' => 'o2k7-silver',
			'Office 2007 (Black)' => 'o2k7-black',
		),
		'peruser' => true,
	),
	array(
		'name' => 'features',
		'cname' => 'Features',
		'description' => 'The included plugins and layout of the buttons on the regular editor.',
		'value' => 'default',
		'options' => array(
			'Default' => 'default',
			'Full Featured' => 'full',
			'Minimal' => 'minimal',
			'Custom' => 'custom',
		),
		'peruser' => true,
	),
	array(
		'name' => 'custom_plugins',
		'cname' => 'Custom Plugins',
		'description' => 'The plugins to use when custom features is enabled.',
		'value' => 'pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist',
		'peruser' => true,
	),
	array(
		'name' => 'custom_bar_1',
		'cname' => 'Custom Toolbar 1',
		'description' => 'The button layout for the first toolbar when custom features is enabled.',
		'value' => 'newdocument,|,undo,redo,|,styleselect,formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull',
		'peruser' => true,
	),
	array(
		'name' => 'custom_bar_2',
		'cname' => 'Custom Toolbar 2',
		'description' => 'The button layout for the second toolbar when custom features is enabled.',
		'value' => 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor',
		'peruser' => true,
	),
	array(
		'name' => 'custom_bar_3',
		'cname' => 'Custom Toolbar 3',
		'description' => 'The button layout for the third toolbar when custom features is enabled.',
		'value' => 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen',
		'peruser' => true,
	),
	array(
		'name' => 'custom_bar_4',
		'cname' => 'Custom Toolbar 4',
		'description' => 'The button layout for the fourth toolbar when custom features is enabled.',
		'value' => 'insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak',
		'peruser' => true,
	),
);

?>