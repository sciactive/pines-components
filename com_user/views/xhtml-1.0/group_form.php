<?php
/**
 * Provides a form for the user to edit a group.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<form method="post" id="group_details" action="<?php echo $config->template->url(); ?>">
<div class="stylized stdform">
<h2><?php echo $this->heading; ?></h2>
<p>Provide group details in this form.</p>
<?php if ( !is_null($this->id) ) { ?>
<input type="hidden" name="group_id" value="<?php echo $this->id; ?>" />
<?php } ?>
<label>Group Name<input type="text" name="groupname" value="<?php echo $this->groupname; ?>" /></label>
<label>Display Name<input type="text" name="name" value="<?php echo $this->name; ?>" /></label>
<label>Email<input type="text" name="email" value="<?php echo $this->email; ?>" /></label>
<label>Parent
<select name="parent">
<option value="none">--No Parent--</option>
<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->parent); ?>
</select>
</label>
<?php if ( $this->display_abilities ) { ?>
    <input type="hidden" name="abilities" value="true" />
    <label>Abilities</label><br />
    <?php foreach ($this->sections as $cur_section) {
        $section_abilities = $config->ability_manager->get_abilities($cur_section);
        if ( count($section_abilities) ) { ?>
        <table width="100%">
            <thead><tr><th colspan="2"><?php echo $cur_section; ?></th></tr></thead>
            <tbody>
                <?php foreach ($section_abilities as $cur_ability) { ?>
                <tr><td><label><input type="checkbox" name="<?php echo $cur_section; ?>[]" value="<?php echo $cur_ability['ability']; ?>"
                    <?php if ( array_search($cur_section.'/'.$cur_ability['ability'], $this->group_abilities) !== false ) { ?>
                        checked="checked"
                    <?php } ?>
                     />&nbsp;<?php echo $cur_ability['title']; ?></label></td><td style="width: 80%;"><?php echo $cur_ability['description']; ?></td></tr>
                <?php } ?>
            </tbody>
        </table>
        <?php }
    } ?>
<?php } ?>
<input type="hidden" name="option" value="<?php echo $this->new_option; ?>" />
<input type="hidden" name="action" value="<?php echo $this->new_action; ?>" />
<input type="submit" value="Submit" />
<input type="button" onclick="window.location='<?php echo $config->template->url('com_user', 'managegroups'); ?>';" value="Cancel" />
<div class="spacer"></div>
</div>
</form>