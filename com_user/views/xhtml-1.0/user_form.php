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
<form method="post" id="user_details" action="<?php echo $config->template->url(); ?>" onsubmit="return verify_form('user_details');">
<div class="stylized stdform">
<h2><?php echo $this->heading; ?></h2>
<p>Provide user details in this form.</p>

<?php if ( !is_null($this->id) ) { ?>
<input type="hidden" name="user_id" value="<?php echo $this->id; ?>" />
<?php } ?>

<label>Username<span class="small">&nbsp;</span><input type="text" name="username" value="<?php echo $this->username; ?>" /></label>

<label>Name<span class="small">&nbsp;</span><input type="text" name="name" value="<?php echo $this->name; ?>" /></label>

<label>Email<span class="small">&nbsp;</span><input type="text" name="email" value="<?php echo $this->email; ?>" /></label>

<?php if (is_null($this->id)) { ?>
<label>Password<span class="small"><?php echo ($config->com_user->empty_pw ? "May be blank." : "&nbsp;"); ?></span>
<?php } else { ?>
<label>Update Password<span class="small">Leave blank, if not changing.</span>
<?php } ?>
<input type="password" name="password" /></label>
<label>Repeat Password<span class="small">&nbsp;</span><input type="password" name="password2" /></label>

<?php if ( $this->display_default_components ) { ?>
<label>Default Component<span class="small">This component will be responsible for the user's home page.</span>
<select name="default_component">
    <?php foreach ($this->default_components as $cur_component) { ?>
    <option value="<?php echo $cur_component; ?>"<?php echo (($this->default_component == $cur_component) ? ' selected="selected"' : ''); ?>><?php echo $cur_component; ?></option>
    <?php } ?>
</select>
</label>
<?php } ?>

<?php if ( $this->display_groups ) { ?>
<?php if (is_null($this->group_array)) { ?>
<label>There are no groups to display.</label>
<?php } else { ?>
<label>Primary Group<span class="small">&nbsp;</span>
<select name="gid" size="1">
<option value="null">-- No Primary Group --</option>
<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->gid); ?>
</select>
</label>

<label>Groups<span class="small">Hold Ctrl or Command to select multiple groups.</span>
<select name="groups[]" multiple="multiple" size="6">
<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->groups); ?>
</select>
</label>
<?php } ?>
<?php } ?>

<?php if ( $this->display_abilities ) { ?>
    <input type="hidden" name="abilities" value="true" />
    <label>Abilities</label><br />
    <label><input type="checkbox" name="inherit_abilities" value="ON" <?php echo ($this->inherit_abilities ? "checked=\"checked\" " : ''); ?>/>Inherit additional abilities from groups.</label><br />
    <?php foreach ($this->sections as $cur_section) {
        $section_abilities = $config->ability_manager->get_abilities($cur_section);
        if ( count($section_abilities) ) { ?>
        <table width="100%">
            <thead><tr><th colspan="2"><?php echo $cur_section; ?></th></tr></thead>
            <tbody>
                <?php foreach ($section_abilities as $cur_ability) { ?>
                <tr><td><label><input type="checkbox" name="<?php echo $cur_section; ?>[]" value="<?php echo $cur_ability['ability']; ?>"
                    <?php if ( array_search($cur_section.'/'.$cur_ability['ability'], $this->user_abilities) !== false ) { ?>
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
<br class="spacer" />
<span><input type="submit" value="Submit" /></span>
<span><input type="button" onclick="window.location='<?php echo $config->template->url('com_user', 'manageusers'); ?>';" value="Cancel" /></span>
<br class="spacer" />
</div>
</form>