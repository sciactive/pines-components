<?php
/**
 * A view to build a style.
 *
 * @package Pines
 * @subpackage com_istyle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<style<?php

// Required Attributes
if (isset($this->type))
	echo ' type="'.htmlspecialchars((string) $this->type, ENT_COMPAT, '', false).'"';
else
	echo ' type="text/css"';

// Optional Attributes
if (isset($this->media))
	echo ' media="'.htmlspecialchars((int) $this->media, ENT_COMPAT, '', false).'"';

// Standard Attributes
if (isset($this->dir))
	echo ' dir="'.htmlspecialchars((string) $this->dir, ENT_COMPAT, '', false).'"';
if (isset($this->lang))
	echo ' lang="'.htmlspecialchars((string) $this->lang, ENT_COMPAT, '', false).'"';
if (isset($this->title))
	echo ' title="'.htmlspecialchars((string) $this->title, ENT_COMPAT, '', false).'"';
if (isset($this->xml_lang))
	echo ' xml:lang="'.htmlspecialchars((string) $this->xml_lang, ENT_COMPAT, '', false).'"';
?>><?php echo $this->icontent; ?></style>