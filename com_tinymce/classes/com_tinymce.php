<?php
/**
 * com_tinymce class.
 *
 * @package Pines
 * @subpackage com_tinymce
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_tinymce main class.
 *
 * A standard editor using TinyMCE.
 *
 * @package Pines
 * @subpackage com_tinymce
 */
class com_tinymce extends component {
    /**
     * Whether the TinyMCE JavaScript has been loaded.
     * @access private
     * @var bool $js_loaded
     */
    private $js_loaded = false;

    /**
     * Load the editor.
     *
     * This will transform any textareas with the "peditor" class into editors.
     */
    function load() {
        if (!$this->js_loaded) {
            $module = new module('com_tinymce', 'tinymce', 'content');
            $this->js_loaded = true;
        }
    }
}

?>