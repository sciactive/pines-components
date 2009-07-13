<?php
defined('D_RUN') or die('Direct access prohibited');

class tpl_dandelion extends template {
	var $format = 'xhtml-1.0-strict-desktop';
}

$config->template = new tpl_dandelion;

?>