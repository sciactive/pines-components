<?php
/**
 * Provide display editors for the fields of a Pines Form.
 *
 * @package Pines
 * @subpackage com_pdf
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	var file;
	var pages;
	var displays = [];
	var displayelem;
	var getting_values = false;

	$(function(){
		$("*:not(html, body, #com_pdf_editor, #com_pdf_editor *)").hide();
		$("#com_pdf_editor").parents().show();
		alert("Please note that the preview is approximate and may not exactly resemble what is generated. Remember to test fully.");
	});

	window.load_editor = function(){
		file = window.pdf_file;
		pages = window.pages;
		displays = JSON.parse(window.current_json);
		displayelem = $("#displays");
		for (i=1; i<=pages; i++) {
			$("#page").append("<option value=\""+i+"\">Page "+i+"</option>");
		}
		$("#drag_me").draggable({
			containment: 'parent',
			start: function(event, ui) {
				$("#drag_me").fadeTo("0", .4);
			},
			drag: function(event, ui) {
				getPos();
			},
			stop: function(event, ui) {
				$("#drag_me").fadeTo("0", .8);
				getPos();
				set_values();
			}
		}).resizable({
			handles: 'all',
			autoHide: true,
			delay: 100,
			// Doesn't work.
			//containment: 'parent',
			start: function(event, ui) {
				$("#drag_me").fadeTo("0", .4);
				getPos();
			},
			resize: function(event, ui) {
				getPos();
			},
			stop: function(event, ui) {
				$("#drag_me").fadeTo("0", .8);
				getPos();
				set_values();
			}
		}).fadeTo("0", .8);
		display_init();
	};

	/*
	 * TODO:
	 * Converting these floats to integers causes the left value to
	 * decrement while being resized after manually being entered.
	 */

	function getPos() {
		var elem = $("#drag_me");
		var parent = elem.parent();
		left = parseFloat(elem.css("left").replace("px", "")) / parent.width();
		top = parseFloat(elem.css("top").replace("px", "")) / parent.height();
		width = parseFloat(elem.width()) / parent.width();
		height = parseFloat(elem.height()) / parent.height();
		$("#x").val(left);
		$("#y").val(top);
		$("#width").val(width);
		$("#height").val(height);
	}

	function update_preview() {
		var elem = $("#drag_me");
		var parent = elem.parent();
		var previewimg = $("#previewimg");
		var dindex = displayelem.val();

		previewimg.attr("src", "about:blank");
		if (null !== dindex) {
			previewimg.attr("src", "<?php echo pines_url('com_pdf', 'image', array('file' => '#file#', 'page' => '#page#')) ; ?>".replace("#file#", file).replace("#page#", displays[dindex].page)).load(function(){
				elem.css("left", (displays[dindex].left * parent.width())+"px");
				elem.css("top", (displays[dindex].top * parent.height())+"px");

				elem.width(displays[dindex].width * parent.width());
				elem.height(displays[dindex].height * parent.height());
				elem.css("overflow", displays[dindex].overflow ? "visible" : "hidden");
				elem.css("font-weight", displays[dindex].bold ? "bold" : "normal");
				elem.css("font-style", displays[dindex].italic ? "italic" : "normal");
				elem.css("font-family", displays[dindex].fontfamily);
				elem.css("font-size", displays[dindex].fontsize+"pt");
				elem.css("color", displays[dindex].fontcolor);
				elem.children("#previewtext").html(displays[dindex].addspacing ? "T h i s &nbsp; i s &nbsp; h o w &nbsp; t h e &nbsp; t e x t &nbsp; w i l l &nbsp; l o o k ." : "This is how the text will look.");
				elem.css("border", displays[dindex].border ? "1px solid black" : "0");
				//elem.css("letter-spacing", (displays[dindex].letterspacing == "normal") ? "normal" : (displays[dindex].letterspacing + "px"));
				//elem.css("word-spacing", (displays[dindex].wordspacing == "normal") ? "normal" : (displays[dindex].wordspacing + "px"));
				elem.css("text-align", displays[dindex].textalign);
				elem.css("text-decoration", displays[dindex].textdecoration);
				elem.css("text-transform", displays[dindex].texttransform);
				elem.css("direction", displays[dindex].direction);
				$(this).unbind("load");
			});
		}

		window.update_json(JSON.stringify(displays));
	}

	function set_values() {
		if (getting_values) return;
		var dindex = displayelem.val();
		displays[dindex] = {};

		displays[dindex].page = parseInt($("#page").val());
		displays[dindex].left = parseFloat($("#x").val());
		displays[dindex].top = parseFloat($("#y").val());
		displays[dindex].width = parseFloat($("#width").val());
		displays[dindex].height = parseFloat($("#height").val());
		displays[dindex].overflow = $("#overflow").attr("checked");
		displays[dindex].bold = $("#bold").attr("checked");
		displays[dindex].italic = $("#italic").attr("checked");
		displays[dindex].fontfamily = $("#fontfamily").val();
		displays[dindex].fontsize = parseFloat($("#fontsize").val());
		displays[dindex].fontcolor = $("#fontcolor").val();
		displays[dindex].addspacing = $("#addspacing").attr("checked");
		displays[dindex].border = $("#border").attr("checked");
		//displays[dindex].letterspacing = $("#letterspacing").val();
		displays[dindex].letterspacing = "normal"; //TCPDF does not support this yet.
		//displays[dindex].wordspacing = $("#wordspacing").val();
		displays[dindex].wordspacing = "normal"; //TCPDF does not support this yet.
		displays[dindex].textalign = $("#textalign").val();
		displays[dindex].textdecoration = $("#textdecoration").val();
		displays[dindex].texttransform = $("#texttransform").val();
		displays[dindex].direction = $("#direction").val();

		update_preview();
	}

	function get_values() {
		getting_values = true;
		var dindex = displayelem.val();

		if (null !== dindex) {
			$("#page").val(displays[dindex].page);
			$("#x").val(displays[dindex].left);
			$("#y").val(displays[dindex].top);
			$("#width").val(displays[dindex].width);
			$("#height").val(displays[dindex].height);
			$("#overflow").attr("checked", displays[dindex].overflow);
			$("#bold").attr("checked", displays[dindex].bold);
			$("#italic").attr("checked", displays[dindex].italic);
			$("#fontfamily").val(displays[dindex].fontfamily);
			$("#fontsize").val(displays[dindex].fontsize);
			$("#fontcolor").val(displays[dindex].fontcolor);
			$("#addspacing").attr("checked", displays[dindex].addspacing);
			$("#border").attr("checked", displays[dindex].border);
			//$("#letterspacing").val(displays[dindex].letterspacing);
			//$("#wordspacing").val(displays[dindex].wordspacing);
			$("#textalign").val(displays[dindex].textalign);
			$("#textdecoration").val(displays[dindex].textdecoration);
			$("#texttransform").val(displays[dindex].texttransform);
			$("#direction").val(displays[dindex].direction);
		}

		update_preview();
		getting_values = false;
	}

	function display_add() {
		displays.push({
			"page":1,
			"left":0,
			"top":0,
			"width":0.33,
			"height":0.03,
			"overflow":true,
			"bold":false,
			"italic":false,
			"fontfamily":"Times",
			"fontsize":"12",
			"fontcolor":"black",
			"addspacing":false,
			"border":false,
			"letterspacing":"normal",
			"wordspacing":"normal",
			"textalign":"left",
			"textdecoration":"none",
			"texttransform":"none",
			"direction":"ltr"
		});
		display_init(displays.length - 1);
	}

	function display_remove() {
		var dindex = displayelem.val();
		displays.splice(dindex, 1);
		display_init(dindex - 1);
	}

	function display_init(replace_index) {
		var cur_index = displayelem.val();
		if (replace_index) cur_index = replace_index;
		displayelem.children().remove();
		for (var cur_display in displays) {
			cur_display = parseInt(cur_display);
			displayelem.append("<option value=\""+cur_display+"\" "+(cur_display == cur_index ? "selected=\"selected\"" : "")+">Display "+(cur_display+1)+"</option>");
		}
		if (displays.length == 0) {
			$("#form_div, #preview_div").hide();
			$("#notice_div").show();
		} else {
			$("#notice_div").hide();
			$("#form_div, #preview_div").show();
		}
		get_values();
	}
	// ]]>
</script>
<div id="com_pdf_editor">
	<div id="left_div" style="float: left; width: 200px;">
		<div id="displays_div" style="float: left; clear: left; margin-bottom: 15px;">
			<label>Displays:<br />
				<select id="displays" onchange="get_values();">
				</select></label>
			<input type="button" class="ui-state-default ui-corner-all" value="+" name="display_add" onclick="display_add();" />
			<input type="button" class="ui-state-default ui-corner-all" value="-" name="display_remove" onclick="display_remove();" />
		</div>
		<div id="form_div" style="float: left; clear: left;">
			<div style="float: left; clear: left;">
				<label>Page:<br />
					<select id="page" name="page" onchange="set_values();">
					</select></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Left: <small>(% of 1)</small><br />
					<input type="text" id="x" name="x" value="0" onkeyup="set_values();" onchange="set_values();" /></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Top: <small>(% of 1)</small><br />
					<input type="text" id="y" name="y" value="0" onkeyup="set_values();" onchange="set_values();" /></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Width: <small>(% of 1)</small><br />
					<input type="text" id="width" name="width" value="0.33" onkeyup="set_values();" onchange="set_values();" /></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Height: <small>(% of 1)</small><br />
					<input type="text" id="height" name="height" value="0.03" onkeyup="set_values();" onchange="set_values();" /></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Overflow: <input type="checkbox" id="overflow" name="overflow" value="ON" onchange="set_values();" checked="checked" /></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Bold: <input type="checkbox" id="bold" name="bold" value="ON" onchange="set_values();" /></label>
				<label>Italic: <input type="checkbox" id="italic" name="italic" value="ON" onchange="set_values();" /></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Font Family:<br />
					<select id="fontfamily_suggest" name="fontfamily_suggest" size="1" onchange="$('#fontfamily').val(this.value); this.value='--'; set_values();">
						<option value="--">Installed Fonts</option>
					<?php
					$fonts = array();
					if ($fontsdir = opendir('components/com_pdf/includes/tcpdf/fonts/')) {
						while (($file = readdir($fontsdir)) !== false) {
							if (substr($file, -4) == '.php')
								$fonts[] = strtolower(basename($file, '.php'));
						}
						closedir($fontsdir);
						sort($fonts);
					} else {
						$fonts[] = 'Can\'t read fonts.';
					}
					foreach ($fonts as $cur_font) {
					?>
						<option value="<?php echo $cur_font; ?>"><?php echo $cur_font; ?></option>
					<?php } ?>
					</select>
					<input type="text" id="fontfamily" name="fontfamily" value="Times" onkeyup="set_values();" /></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Font Size: <small>(pt)</small><br />
					<input type="text" id="fontsize" name="fontsize" value="12" onkeyup="set_values();" /></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Font Color:<br /><small>(Name or Hex)</small><br />
					<input type="text" id="fontcolor" name="fontcolor" value="black" onkeyup="set_values();" /></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Add Spacing: <input type="checkbox" id="addspacing" name="addspacing" value="ON" onchange="set_values();" /></label>
				<br /><small>(For justifying characters.)</small>
			</div>
			<div style="float: left; clear: left;">
				<label>Border: <input type="checkbox" id="border" name="border" value="ON" onchange="set_values();" /></label>
				<br /><small>(A simple black border.)</small>
			</div>
			<!-- <div style="float: left; clear: left;">
				<label>Letter Spacing:<br /><small>("normal" or Pixels)</small><br />
				<input type="text" id="letterspacing" name="letterspacing" value="normal" onkeyup="set_values();" /></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Word Spacing:<br /><small>("normal" or Pixels)</small><br />
				<input type="text" id="wordspacing" name="wordspacing" value="normal" onkeyup="set_values();" /></label>
			</div> -->
			<div style="float: left; clear: left;">
				<label>Text Align:<br />
					<select id="textalign" name="textalign" onchange="set_values(); if (this.value == 'justify') { alert('Even though it can\'t be shown in the preview, single lines will justify.'); }">
						<option value="left">Left</option>
						<option value="center">Center</option>
						<option value="right">Right</option>
						<option value="justify">Justify</option>
					</select></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Text Decoration:<br />
					<select id="textdecoration" name="textdecoration" onchange="set_values();">
						<option value="none">None</option>
						<option value="line-through">Line-Through</option>
						<option value="underline">Underline</option>
					</select></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Text Transform<br />
					<select id="texttransform" name="texttransform" onchange="set_values();">
						<option value="none">None</option>
						<option value="capitalize">Capitalize</option>
						<option value="uppercase">Uppercase</option>
						<option value="lowercase">Lowercase</option>
					</select></label>
			</div>
			<div style="float: left; clear: left;">
				<label>Direction<br />
					<select id="direction" name="direction" onchange="set_values();">
						<option value="ltr">Left to Right</option>
						<option value="rtl">Right to Left</option>
					</select></label>
			</div>
		</div>
	</div>
	<div id="preview_div" style="position: absolute; top: 10px; left: 200px; height: auto; width: auto; border: 3px solid black;">
		<div>
			<div id="drag_me" style="width: 80px; height: 30px; background-color: #0FF; position: absolute;"><span id="previewtext">This is how the text will look.</span></div>
			<img id="previewimg" src="" alt="pdf" />
		</div>
	</div>
	<div id="notice_div" style="float: left; margin-left: 20px; border: 1px dotted gray; color: gray;">
		There are no displays. You can add a display using the buttons to the left.
	</div>
</div>