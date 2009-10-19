<?php
/**
 * Provides a form for the user to edit a user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$page->head("<script type=\"text/javascript\" src=\"".$config->rela_location."components/com_user/js/verify.js\"></script>\n");
?>
<form class="pform" method="post" id="user_details" action="<?php echo $config->template->url($this->new_option, $this->new_action); ?>" onsubmit="return verify_form('user_details');">
<fieldset>
    <legend><?php echo $this->heading; ?></legend>
    <div class="element heading">
        <p>Provide user details in this form.</p>
    </div>
    <div class="element">
        <label><span class="label">Username</span>
        <input class="field" type="text" name="username" size="20" value="<?php echo $this->username; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Name</span>
        <input class="field" type="text" name="name" size="20" value="<?php echo $this->name; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Email</span>
        <input class="field" type="text" name="email" size="20" value="<?php echo $this->email; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label"><?php if (!is_null($this->id)) echo 'Update '; ?>Password</span>
        <?php if (is_null($this->id)) {
            echo ($config->com_user->empty_pw ? '<span class="note">May be blank.</span>' : '');
        } else {
            echo '<span class="note">Leave blank, if not changing.</span>';
        } ?>
        <input class="field" type="password" name="password" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Repeat Password</span>
        <input class="field" type="password" name="password2" size="20" /></label>
    </div>
    <?php if ( $this->display_default_components ) { ?>
    <div class="element">
        <label><span class="label">Default Component</span>
        <span class="note">This component will be responsible for the user's home page.</span>
        <select class="field" name="default_component">
            <?php foreach ($this->default_components as $cur_component) { ?>
            <option value="<?php echo $cur_component; ?>"<?php echo (($this->default_component == $cur_component) ? ' selected="selected"' : ''); ?>><?php echo $cur_component; ?></option>
            <?php } ?>
        </select></label>
    </div>
    <?php } ?>

    <?php if ( $this->display_groups ) { ?>
    <div class="element heading">
        <h1>Groups</h1>
    </div>
    <?php if (is_null($this->group_array)) { ?>
    <div class="element">
        <label><span class="label">There are no groups to display.</span></label>
    </div>
    <?php } else { ?>
    <div class="element">
        <label><span class="label">Primary Group</span>
        <select class="field" name="gid" size="1">
            <option value="null">-- No Primary Group --</option>
            <?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->gid); ?>
        </select></label>
    </div>
    <div class="element">
        <label><span class="label">Groups</span>
        <span class="note">Hold Ctrl (Command on Mac) to select multiple groups.</span>
        <select class="field" name="groups[]" multiple="multiple" size="6">
            <?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->groups); ?>
        </select></label>
    </div>
    <?php }
    } ?>

    <?php if ( $this->display_abilities ) { ?>
    <input type="hidden" name="abilities" value="true" />
    <div class="element heading">
        <h1>Abilities</h1>
    </div>
    <div class="element">
        <label><span class="label">Inherit additional abilities from groups.</span>
        <input class="field" type="checkbox" name="inherit_abilities" value="ON" <?php echo ($this->inherit_abilities ? 'checked="checked" ' : ''); ?>/></label>
    </div>
    <?php foreach ($this->sections as $cur_section) {
        $section_abilities = $config->ability_manager->get_abilities($cur_section);
        if ( count($section_abilities) ) { ?>
            <div class="element"><span class="label">Abilities for <em><?php echo $cur_section; ?></em></span>
                <div class="group">
                    <?php foreach ($section_abilities as $cur_ability) { ?>
                    <label><input class="field" type="checkbox" name="<?php echo $cur_section; ?>[]" value="<?php echo $cur_ability['ability']; ?>"
                        <?php if ( array_search($cur_section.'/'.$cur_ability['ability'], $this->user_abilities) !== false ) { ?>
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
        <input type="hidden" name="user_id" value="<?php echo $this->id; ?>" />
        <?php } ?>
        <input class="button" type="submit" value="Submit" />
        <input class="button" type="button" onclick="window.location='<?php echo $config->template->url('com_user', 'manageusers'); ?>';" value="Cancel" />
    </div>

</fieldset>
</form>