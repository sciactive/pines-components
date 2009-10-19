<?php
/**
 * Provides a form with options for sending a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$page->head("<script src=\"{$config->rela_location}components/com_newsletter/js/jquery.js\" type=\"text/javascript\"></script>");
$page->head("<script src=\"{$config->rela_location}components/com_newsletter/js/jquery.checkboxtree.js\" type=\"text/javascript\"></script>");
$page->head("<script type=\"text/javascript\">
jQuery(document).ready(function(){
	jQuery(\".unorderedlisttree\").checkboxTree({
			collapsedarrow: \"{$config->rela_location}components/com_newsletter/images/img-arrow-collapsed.gif\",
			expandedarrow: \"{$config->rela_location}components/com_newsletter/images/img-arrow-expanded.gif\",
			blankarrow: \"{$config->rela_location}components/com_newsletter/images/img-arrow-blank.gif\",
			checkchildren: true
	});
});
</script>
");
$page->head("<link href=\"{$config->rela_location}components/com_newsletter/css/checktree.css\" media=\"all\" rel=\"stylesheet\" type=\"text/css\" />");
?>
<form class="pform" method="post" action="<?php echo $config->template->url(); ?>">
<fieldset>
    <legend>Sending <?php echo $this->mail->name; ?></legend>
    <div class="element">
        <label><span class="label">From Email</span>
        <input class="field" type="text" name="from" size="20" value="<?php echo htmlentities($config->com_newsletter->default_from); ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Reply to Email</span>
        <input class="field" type="text" name="replyto" size="20" value="<?php echo htmlentities($config->com_newsletter->default_reply_to); ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Subject</span>
        <input class="field" type="text" name="subject" size="20" value="<?php echo htmlentities($this->mail->subject); ?>" /></label>
    </div>
    <div class="element">
        <span class="label">Select Groups</span>
        <span class="note">Click group name to select children as well.</span>
        <div class="group">
        <?php
        $group_select_menu = new menu;
        $config->user_manager->get_group_menu($group_select_menu);
        echo $group_select_menu->render(array('<ul class="unorderedlisttree">', '</ul>'),
                array('<li>', '</li>'),
                array('<ul>', '</ul>'),
                array('<li>', '</li>'),
                "<input class=\"field\" type=\"checkbox\" name=\"group[]\" value=\"#DATA#\" /><label>#NAME#</label>\n",
                '<hr style="visibility: hidden; clear: both;" />');
        /*$sendprep->content(
            $config->user_manager->get_group_tree("<label><input type="checkbox" name="#guid#" />#mark##name# [#groupname#]</label>\n", $config->user_manager->get_group_array())
        ); */
        ?>
        </div>
    </div>
    <div class="element heading">
        <h1>Options</h1>
    </div>
    <div class="element">
        <label><span class="label">Include a link to the mail's web address.</span>
        <span class="note">For online viewing.</span>
        <input class="field" type="checkbox" name="include_permalink" checked /></label>
    </div>
    <div class="element buttons">
        <input type="hidden" name="option" value="com_newsletter" />
        <input type="hidden" name="action" value="send" />
        <input type="hidden" name="mail_id" value="<?php echo $_REQUEST['mail_id']; ?>" />
        <input class="button" type="submit" value="Submit" />
        <input class="button" type="button" onclick="window.location='<?php echo $config->template->url('com_newsletter', 'list'); ?>';" value="Cancel" />
    </div>
</fieldset>
</form>