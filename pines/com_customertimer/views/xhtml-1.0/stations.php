<?php
/**
 * Shows customer stations.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Customer Stations';
?>
<style type="text/css">
	/* <![CDATA[ */
	.station_layout {
		position: relative;
	}
	.station_layout .station_layout_bg {
		float: left;
		width: 100%;
		height: auto;
	}
	.station_layout .station {
		position: absolute;
	}
	.station_layout .station .station_id {
		margin: 5px;
		font: bold large sans-serif;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		var station_floor = $(".station_layout .station_floor");
		
		var stations = [
			{"id": "1", "left": "5.9178743961352656%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			{"id": "2a", "left": "18.115942028985507%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			{"id": "2b", "left": "18.115942028985507%", "top": "15.749235474006115%", "width": "7.85024154589372%", "height": "12.385321100917432%"},
			{"id": "3", "left": "28.6231884057971%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			{"id": "4a", "left": "38.88888888888889%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			{"id": "4b", "left": "38.88888888888889%", "top": "15.749235474006115%", "width": "7.85024154589372%", "height": "12.385321100917432%"},
			{"id": "5", "left": "49.27536231884058%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			{"id": "6a", "left": "59.78260869565217%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			{"id": "6b", "left": "59.78260869565217%", "top": "15.749235474006115%", "width": "7.85024154589372%", "height": "12.385321100917432%"},
			{"id": "7", "left": "70.04830917874396%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			{"id": "8", "left": "80.31400966183575%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},

			{"id": "9a", "left": "89.97584541062802%", "top": "13.608562691131498%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			{"id": "9b", "left": "89.97584541062802%", "top": "20.18348623853211%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			{"id": "9c", "left": "89.97584541062802%", "top": "26.758409785932724%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			{"id": "9d", "left": "89.97584541062802%", "top": "33.33333333333333%", "width": "9.782608695652174%", "height": "6.574923547400612%"},

			{"id": "10a", "left": "89.97584541062802%", "top": "60.55045871559633%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			{"id": "10b", "left": "89.97584541062802%", "top": "67.12538226299695%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			{"id": "10c", "left": "89.97584541062802%", "top": "73.70030581039755%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			{"id": "10d", "left": "89.97584541062802%", "top": "80.27522935779816%", "width": "9.782608695652174%", "height": "6.422018348623854%"},

			{"id": "11", "left": "80.55555555555556%", "top": "87.46177370030581%", "width": "7.729468599033816%", "height": "11.467889908256881%"},
			{"id": "12", "left": "70.28985507246377%", "top": "87.46177370030581%", "width": "7.729468599033816%", "height": "11.467889908256881%"},
			{"id": "13a", "left": "59.90338164251208%", "top": "87.46177370030581%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			{"id": "13b", "left": "59.90338164251208%", "top": "73.70540140601108%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			{"id": "14", "left": "49.39613526570048%", "top": "87.46177370030581%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			{"id": "15a", "left": "39.1304347826087%", "top": "87.46177370030581%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			{"id": "15b", "left": "39.1304347826087%", "top": "73.70540140601108%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			{"id": "16", "left": "5.9178743961352656%", "top": "85.93272171253823%", "width": "7.85024154589372%", "height": "12.996941896024464%"},

			{"id": "17", "left": "0.36231884057971015%", "top": "74.6177370030581%", "width": "10.144927536231885%", "height": "9.938837920489296%"},
			{"id": "18", "left": "0.36231884057971015%", "top": "62.84403669724771%", "width": "10.144927536231885%", "height": "9.785932721712538%"},
			{"id": "19", "left": "0.36231884057971015%", "top": "28.287461773700306%", "width": "10.144927536231885%", "height": "10.397553516819572%"},
			{"id": "20", "left": "0.36231884057971015%", "top": "15.749235474006115%", "width": "10.144927536231885%", "height": "10.397553516819572%"},
		];
		
		$.each(stations, function(index, station){
			$("<div />", {
				"class": "station id-"+station.id,
				"css": {
					"left": station.left,
					"top": station.top,
					"width": station.width,
					"height": station.height
				},
				"html": $("<div />", {
					"class": "station_id",
					"html": station.id
				}),
				"click": function(){
					alert("You clicked me. I'm station "+station.id+".");
				}
			})
			.button()
			.appendTo(station_floor);
			/*
			.draggable({
				"stop": function(){
					$("#position").html(
						"left: "+$(this).position().left/$(this).parent().parent().width()+"<br />"+
						"top: "+$(this).position().top/$(this).parent().parent().height()+"<br />"+
						"width: "+$(this).width()/$(this).parent().parent().width()+"<br />"+
						"height: "+$(this).height()/$(this).parent().parent().height()
					);
				}
			})
			.resizable({
				"stop": function(){
					$("#position").html(
						"left: "+$(this).position().left/$(this).parent().parent().width()+"<br />"+
						"top: "+$(this).position().top/$(this).parent().parent().height()+"<br />"+
						"width: "+$(this).width()/$(this).parent().parent().width()+"<br />"+
						"height: "+$(this).height()/$(this).parent().parent().height()
					);
				}
			});
			*/
		});
	});
	// ]]>
</script>
<div class="station_layout">
	<img src="<?php echo $pines->config->rela_location; ?>components/com_customertimer/includes/station_floor.png" class="station_layout_bg" alt="Station Layout" />
	<div class="station_floor"></div>
	<br class="spacer" />
</div>
<!--
<div id="position"></div>
-->