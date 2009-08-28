<?php
/**
 * Provides a form for the user to login.
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
<form class="pform" name="login" method="post" action="<?php echo $config->template->url(); ?>">
    <fieldset>
        <legend>Login to <?php echo $config->option_title; ?></legend>
        <div class="element heading">
            <p>Please enter your credentials to login.</p>
        </div>
        <div class="element">
            <label><span>Username</span>
            <input type="text" name="username" size="20" /></label>
        </div>
        <div class="element">
            <label><span>Password</span>
            <?php echo ($config->com_user->empty_pw ? '<span class="note">May be blank.</span>' : ''); ?>
            <input type="password" name="password" size="20" /></label>
        </div>
        <div class="element buttons">
            <input type="hidden" name="option" value="com_user" />
            <input type="hidden" name="action" value="login" />
            <?php if ( isset($_REQUEST['url']) ) { ?>
            <input type="hidden" name="url" value="<?php echo htmlentities(urlencode($_REQUEST['url'])); ?>" />
            <?php } ?>
            <input type="submit" name="submit" value="Login" />
            <input type="reset" name="reset" value="Reset" />
        </div>
    </fieldset>
</form>