<?php
/**
 * Test the TinyMCE standard editor.
 *
 * @package Pines
 * @subpackage com_tinymce
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->editor->load();

$module = new module('com_tinymce', 'test', 'content');

?>