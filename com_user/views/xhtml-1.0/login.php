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
<form name="login" method="post" action="<?php echo $config->template->url(); ?>">
    <div class="stylized stdform">
        <h2>Login to <?php echo $config->option_title; ?></h2>
        <p>Please enter your credentials to login.</p>
        <label>Username<span class="small">&nbsp;</span><input type="text" name="username" /></label>
        <label>Password<span class="small"><?php echo ($config->com_user->empty_pw ? "May be blank." : "&nbsp;"); ?></span><input type="password" name="password" /></label>
        <input type="hidden" name="option" value="com_user" />
        <input type="hidden" name="action" value="login" />
        <?php if ( isset($_REQUEST['url']) ) { ?>
        <input type="hidden" name="url" value="<?php echo urlencode($_REQUEST['url']); ?>" />
        <?php } ?>
        <br class="spacer" />
        <span><input type="submit" value="Login" /></span>
        <br class="spacer" />
    </div>
</form>