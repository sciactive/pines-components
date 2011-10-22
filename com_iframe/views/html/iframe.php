<?php
/**
 * A view to build an iframe.
 *
 * @package Pines
 * @subpackage com_iframe
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<iframe<?php

// Optional Attributes
if (isset($this->align))
	echo ' align="'.htmlspecialchars((string) $this->align, ENT_COMPAT, '', false).'"';
if (isset($this->frameborder))
	echo ' frameborder="'.htmlspecialchars((int) $this->frameborder, ENT_COMPAT, '', false).'"';
if (isset($this->height))
	echo ' height="'.htmlspecialchars((string) $this->height, ENT_COMPAT, '', false).'"';
if (isset($this->longdesc))
	echo ' longdesc="'.htmlspecialchars((string) $this->longdesc, ENT_COMPAT, '', false).'"';
if (isset($this->marginheight))
	echo ' marginheight="'.htmlspecialchars((string) $this->marginheight, ENT_COMPAT, '', false).'"';
if (isset($this->marginwidth))
	echo ' marginwidth="'.htmlspecialchars((string) $this->marginwidth, ENT_COMPAT, '', false).'"';
if (isset($this->name))
	echo ' name="'.htmlspecialchars((string) $this->name, ENT_COMPAT, '', false).'"';
if (isset($this->scrolling))
	echo ' scrolling="'.htmlspecialchars((string) $this->scrolling, ENT_COMPAT, '', false).'"';
if (isset($this->src))
	echo ' src="'.htmlspecialchars((string) $this->src, ENT_COMPAT, '', false).'"';
if (isset($this->width))
	echo ' width="'.htmlspecialchars((string) $this->width, ENT_COMPAT, '', false).'"';

// Standard Attributes
if (isset($this->class))
	echo ' class="'.htmlspecialchars((string) $this->class, ENT_COMPAT, '', false).'"';
if (isset($this->id))
	echo ' id="'.htmlspecialchars((string) $this->id, ENT_COMPAT, '', false).'"';
if (isset($this->style))
	echo ' style="'.htmlspecialchars((string) $this->style, ENT_COMPAT, '', false).'"';
if (isset($this->title))
	echo ' title="'.htmlspecialchars((string) $this->title, ENT_COMPAT, '', false).'"';

// Extra Attributes
if (isset($this->allowTransparency))
	echo ' allowTransparency="'.htmlspecialchars((string) $this->allowTransparency, ENT_COMPAT, '', false).'"';

?>><?php echo $this->icontent; ?></iframe>