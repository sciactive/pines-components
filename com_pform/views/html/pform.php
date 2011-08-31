<?php
/**
 * A view to load Pines Form.
 *
 * @package Pines
 * @subpackage com_pform
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
/*
<link href="<?php echo htmlspecialchars($pines->config->location); ?>components/com_pform/includes/pform.css" media="all" rel="stylesheet" type="text/css" />
<!--[if lt IE 8]>
<link href="<?php echo htmlspecialchars($pines->config->location); ?>components/com_pform/includes/pform-ie-lt-8.css" media="all" rel="stylesheet" type="text/css" />
<![endif]-->
<!--[if lt IE 6]>
<link href="<?php echo htmlspecialchars($pines->config->location); ?>components/com_pform/includes/pform-ie-lt-6.css" media="all" rel="stylesheet" type="text/css" />
<![endif]-->
*/
?>
<script type="text/javascript">// <![CDATA[
pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pform/includes/pform.css");
// ]]></script>
<!--[if lt IE 8]>
<script type="text/javascript">// <![CDATA[
pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pform/includes/pform-ie-lt-8.css");
// ]]></script>
<![endif]-->
<!--[if lt IE 6]>
<script type="text/javascript">// <![CDATA[
pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pform/includes/pform-ie-lt-6.css");
// ]]></script>
<![endif]-->