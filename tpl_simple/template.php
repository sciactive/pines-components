<?php
/**
 * Main page of the Simple template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package Templates\simple
 * @license http://opensource.org/licenses/MIT
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright Angela Murrell
 * 
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

header('Content-Type: text/html');
$width = ($pines->config->template->width == 'fluid') ? '-fluid' : ''; 
// Navigation Bar Configuration
if ($pines->config->tpl_simple->mobile_menu == "adjusted") { 
        // The mobile menu option is chosen
        if (empty($nav))
                $nav = "adjusted";
        else
                $nav .= " adjusted";
}
if ($pines->config->tpl_simple->navbar_menu_height > 0) {
        // Menu Height has been set
        if (empty($nav))
                $nav = "menu-height";
        else
                $nav .= " menu-height";
}
if ($pines->config->tpl_simple->footer_type == "fixed") {
        // footer fixed
        if (empty($footer))
                $footer = "footer-fixed";
        else
                $footer .= " footer-fixed";
}// The page variable below still affects the footer so I put it in here.
if ($pines->config->tpl_simple->footer_type == "fixed") {
        // footer fixed
        if (empty($page))
                $page = "footer-fixed";
        else
                $page .= " footer-fixed";
}

$file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT']);
$file_root_location = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);

// Get CSS
// - If using font-awesome, put the font folder into templates/
$css = ($pines->config->compress_cssjs) ? $pines->config->loadcompressedcss : array();
$css[] =  $file_root_location.'templates/'.htmlspecialchars($pines->current_template).'/css/style.css';
$css[] =  $file_root_location.'templates/'.htmlspecialchars($pines->current_template).'/css/custom.css';
if (!empty($pines->config->tpl_simple->font_folder))
    $css[] = $file_root_location.htmlspecialchars($pines->config->tpl_simple->font_folder).'stylesheet.css';

if (!empty($pines->config->tpl_simple->link_css)) {
    $link_css = explode(',', $pines->config->tpl_simple->link_css);
    foreach ($link_css as $cur_link) {
        $css[] = $file_root_location.$cur_link;
    }
}

// Get JS
$js = array();
// The system JS is always loaded first, hard coded in the buildjs file.
$js[] =  $file_root.htmlspecialchars($pines->config->location).'templates/'.htmlspecialchars($pines->current_template).'/js/pnotify.defaults.js';

if ($pines->config->tpl_simple->navbar_trigger == "hover") {
    $js[] =  $file_root_location.'templates/tpl_simple/js/navhover.js';
} 
if ($pines->config->tpl_simple->mobile_menu == "adjusted") {
    $js[] =  $file_root_location.'templates/tpl_simple/js/mobilemenu.js';
} if ($pines->config->tpl_simple->footer_height == "adjusted" && $pines->config->tpl_simple->footer_type != "fixed") {
    $js[] =  $file_root_location.'templates/tpl_simple/js/adjustedfooter.js';
} if (!empty($pines->config->tpl_simple->load_js)) {
    $load_js = explode(',', $pines->config->tpl_simple->load_js);
    foreach ($load_js as $cur_link) {
        $js[] = $file_root_location.$cur_link;
    }
} 
if (is_array($pines->config->loadcompressedjs)) {
    // If you are using compressed JS, you need to add the images
    // from your jquery-ui theme to your template.
    $js = array_merge($pines->config->loadcompressedjs, $js);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo htmlspecialchars($pines->page->get_title()); ?></title>
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo htmlspecialchars($pines->config->location.$pines->config->tpl_simple->favicon_url); ?>" />
    <meta name="HandheldFriendly" content="true" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php 
    // If we compressed everything, then it's all here:
    if ($pines->config->compress_cssjs) { ?>
        <link href="<?php echo $pines->template->load_template_css($css); ?>" media="all" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="<?php echo $pines->template->load_template_js($js); ?>"></script>
        
    <?php } else { 
    // Otherwise we only compressed template custom/style/addin links/system js.
    // So we need to load the script, then load the head modules, then load the 
    // styles.
        ?>
        <script type="text/javascript" src="<?php echo $pines->template->load_template_js($js); ?>"></script>
        <?php echo $pines->page->render_modules('head', 'module_head'); ?>
        <link href="<?php echo $pines->template->load_template_css($css); ?>" media="all" rel="stylesheet" type="text/css" />
    <?php } ?>
</head>
<body>
    <div id="top"><?php
            echo $pines->page->render_modules('top', 'module_header');
            $error = $pines->page->get_error();
            $notice = $pines->page->get_notice();
            if ( $error || $notice ) { ?>
            <script type="text/javascript">
                    pines(function(){
                            <?php
                            if ( $error ) { foreach ($error as $cur_item) {
                                    echo 'pines.error('.json_encode(htmlspecialchars($cur_item)).", \"Error\");\n";
                            } }
                            if ( $notice ) { foreach ($notice as $cur_item) {
                                    echo 'pines.notice('.json_encode(htmlspecialchars($cur_item)).", \"Notice\");\n";
                            } }
                            ?>
                    });
            </script>
            <?php
            }
    ?></div>
    <?php if ($pines->config->tpl_simple->show_navigation) { ?>
    <div id="nav-configure" class="<?php echo $nav; ?>">
            <div id="nav" class="navbar clearfix <?php echo $pines->config->tpl_simple->navbar_fixed ? 'navbar-fixed-top' : ''; ?>">
                    <div class="navbar-inner">
                            <div class="container<?php echo $width; ?>">
                                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                                            <span class="icon-bar"></span>
                                            <span class="icon-bar"></span>
                                            <span class="icon-bar"></span>
                                    </a>
                                    <a class="brand" href="<?php echo htmlspecialchars($pines->config->full_location); ?>">
                                            <?php if ($pines->config->tpl_simple->use_header_image) { ?>
                                            <img src="<?php echo htmlspecialchars($pines->config->tpl_simple->header_image); ?>" alt="<?php echo htmlspecialchars($pines->config->page_title); ?>" />
                                            <?php } else { ?>
                                                    <span>
                                            <?php	switch ($pines->config->tpl_simple->brand_type) {
                                                            case "System Name":
                                                                    echo htmlspecialchars($pines->config->system_name);
                                                                    break;
                                                            case "Page Title":
                                                                    echo htmlspecialchars($pines->config->page_title);
                                                                    break;
                                                            case "Custom":
                                                                    echo htmlspecialchars($pines->config->tpl_simple->brand_name);
                                                                    break;
                                                            } ?>
                                                    </span>	
                                    <?php } ?>
                                    </a>
                                    <div class="nav-collapse">
                                            <?php echo $pines->page->render_modules('main_menu', 'module_head'); ?>
                                    </div>
                            </div>
                    </div>
            </div>
    </div>
    <?php } ?>
    <div id="header">
        <div class="container<?php echo $width; ?>">
                <div class="row-fluid">
                        <div class="span12 positions">
                                <div id="header_position"><?php echo $pines->page->render_modules('header', 'module_header'); ?></div>
                                <div id="header_right"><?php echo $pines->page->render_modules('header_right', 'module_header_right'); ?></div>
                        </div>
                </div>
        </div>
    </div>
    <div id="page" class="<?php echo ($pines->config->tpl_simple->variant == 'full-fluid-page') ? '' : (($width) ? 'container'.$width : 'container');  ?> <?php echo $page; ?>">
            <div class="row-fluid">
                    <div id="breadcrumbs" class="span12"><?php echo $pines->page->render_modules('breadcrumbs', 'module_header'); ?></div>
            </div>
            <div class="row-fluid">
                    <div id="pre_content" class="span12"><?php echo $pines->page->render_modules('pre_content', 'module_header'); ?></div>
            </div>
            <div id="column_container">
                    <div class="row-fluid">
                            <?php if (in_array($pines->config->tpl_simple->variant, array('threecol', 'twocol-sideleft'))) { ?>
                            <div id="left" class="span3">
                                    <?php echo $pines->page->render_modules('left', 'module_side'); ?>
                                    <?php if ($pines->config->tpl_simple->variant == 'twocol-sideleft') { echo $pines->page->render_modules('right', 'module_side'); } ?>&nbsp;
                            </div>
                            <?php } ?>
                            <div class="<?php echo ($pines->config->tpl_simple->variant == 'full-page' || $pines->config->tpl_simple->variant == 'full-fluid-page') ? 'span12' : ($pines->config->tpl_simple->variant == 'threecol' ? 'span6' : 'span9'); ?>">
                                    <div id="content_container">
                                            <div class="row-fluid">
                                                    <div id="content_top_left" class="span6"><?php echo $pines->page->render_modules('content_top_left'); ?></div>
                                                    <div id="content_top_right" class="span6"><?php echo $pines->page->render_modules('content_top_right'); ?></div>
                                            </div>
                                            <div id="content"><?php echo $pines->page->render_modules('content', 'module_content'); ?></div>
                                            <div class="row-fluid">
                                                    <div id="content_bottom_left" class="span6"><?php echo $pines->page->render_modules('content_bottom_left'); ?></div>
                                                    <div id="content_bottom_right" class="span6"><?php echo $pines->page->render_modules('content_bottom_right'); ?></div>
                                            </div>
                                    </div>
                            </div>
                            <?php if (in_array($pines->config->tpl_simple->variant, array('threecol', 'twocol-sideright'))) { ?>
                            <div id="right" class="span3">
                                    <?php if ($pines->config->tpl_simple->variant == 'twocol-sideright') { echo $pines->page->render_modules('left', 'module_side'); } ?>
                                    <?php echo $pines->page->render_modules('right', 'module_side'); ?>&nbsp;
                            </div>
                            <?php } ?>
                    </div>
            </div>
            <div class="row-fluid">
                    <div id="post_content" class="span12"><?php echo $pines->page->render_modules('post_content', 'module_header'); ?></div>
            </div>
    </div>
    <div id="footer" class="clearfix <?php echo $footer;?>">
        <div class="container<?php echo $width; ?>">
                <div class="row-fluid">
                        <div class="span12 positions">
                                <div id="footer_position"><?php echo $pines->page->render_modules('footer', 'module_header'); ?></div>
                                <p id="copyright"><?php echo htmlspecialchars($pines->config->copyright_notice, ENT_COMPAT, '', false); ?></p>
                        </div>
                </div>
        </div>
    </div>
    <div id="bottom"><?php echo $pines->page->render_modules('bottom', 'module_header'); ?></div>
    <link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/print.css" media="print" rel="stylesheet" type="text/css" />
	<?php 
    if ($pines->config->compress_cssjs) {
        // Render this stuff at the bottom because anything important will
        // Already be safely added to the top.
        echo $pines->page->render_modules('head', 'module_head'); 
    } ?>
</body>
</html>