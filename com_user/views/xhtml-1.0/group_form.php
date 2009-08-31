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
<form class="pform" method="post" id="group_details" action="<?php echo $config->template->url(); ?>">
<fieldset>
    <legend><?php echo $this->heading; ?></legend>
    <div class="element heading">
        <p>Provide group details in this form.</p>
    </div>
    <div class="element">
        <label><span class="label">Group Name</span>
        <input class="field" type="text" name="groupname" size="20" value="<?php echo $this->groupname; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Display Name</span>
        <input class="field" type="text" name="name" size="20" value="<?php echo $this->name; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Email</span>
        <input class="field" type="text" name="email" size="20" value="<?php echo $this->email; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Parent</span>
        <select class="field" name="parent" size="1">
            <option value="none">--No Parent--</option>
            <?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->parent); ?>
        </select></label>
    </div>

    <?php if ( $this->display_abilities ) { ?>
    <input type="hidden" name="abilities" value="true" />
    <div class="element heading">
        <h1>Abilities</h1>
    </div>
    <?php foreach ($this->sections as $cur_section) {
        $section_abilities = $config->ability_manager->get_abilities($cur_section);
        if ( count($section_abilities) ) { ?>
            <div class="element"><span class="label">Abilities for <em><?php echo $cur_section; ?></em></span>
                <div class="group">
                    <?php foreach ($section_abilities as $cur_ability) { ?>
                    <label><input class="field" type="checkbox" name="<?php echo $cur_section; ?>[]" value="<?php echo $cur_ability['ability']; ?>"
                        <?php if ( array_search($cur_section.'/'.$cur_ability['ability'], $this->group_abilities) !== false ) { ?>
                            checked="checked"
                        <?php } ?>
                         />&nbsp;<?php echo $cur_ability['title'] . ' <small>(' . $cur_ability['description'] . ')</small>'; ?></label><br />
                    <?php } ?>
                </div>
            </div>
        <?php }
    }
    } ?>

	<div class="element buttons">
        <?php if ( !is_null($this->id) ) { ?>
        <input type="hidden" name="group_id" value="<?php echo $this->id; ?>" />
        <?php } ?>
        <input type="hidden" name="option" value="<?php echo $this->new_option; ?>" />
        <input type="hidden" name="action" value="<?php echo $this->new_action; ?>" />
        <input type="submit" value="Submit" />
        <input type="button" onclick="window.location='<?php echo $config->template->url('com_user', 'managegroups'); ?>';" value="Cancel" />
    </div>

</fieldset>
</form>