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
	.station_layout .station .name {
		display: block;
		float: left;
		font: normal small sans-serif;
	}
	.station_layout .station .points_remain {
		display: block;
		float: right;
		margin: 2px;
		font: bold small sans-serif;
	}
	.station_layout .station .station_id {
		display: block;
		margin: 5px;
		font: bold large sans-serif;
	}
	.station_layout .station.filled .station_id {
		float: left;
		margin: 2px;
		font: normal small sans-serif;
	}
	.station_layout .station.filled span.ui-button-text {
		padding: 0.4em 0.2em;
	}

	/* Status Dependent Styles */
	.station_layout .station.ok {
		background: none green;
		color: black;
	}
	.station_layout .station.warning {
		background: none yellow;
		color: black;
	}
	.station_layout .station.critical {
		background: none red;
		color: white;
	}
	.station_layout.warning {
		background: none goldenrod;
	}
	.station_layout.critical {
		background: none crimson;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		var station_layout = $(".station_layout");
		var station_floor = $(".station_layout .station_floor");
		
		var stations = {
			"1": {"left": "5.9178743961352656%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"2a": {"left": "18.115942028985507%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"2b": {"left": "18.115942028985507%", "top": "15.749235474006115%", "width": "7.85024154589372%", "height": "12.385321100917432%"},
			"3": {"left": "28.6231884057971%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"4a": {"left": "38.88888888888889%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"4b": {"left": "38.88888888888889%", "top": "15.749235474006115%", "width": "7.85024154589372%", "height": "12.385321100917432%"},
			"5": {"left": "49.27536231884058%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"6a": {"left": "59.78260869565217%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"6b": {"left": "59.78260869565217%", "top": "15.749235474006115%", "width": "7.85024154589372%", "height": "12.385321100917432%"},
			"7": {"left": "70.04830917874396%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"8": {"left": "80.31400966183575%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},

			"9a": {"left": "89.97584541062802%", "top": "13.608562691131498%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			"9b": {"left": "89.97584541062802%", "top": "20.18348623853211%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			"9c": {"left": "89.97584541062802%", "top": "26.758409785932724%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			"9d": {"left": "89.97584541062802%", "top": "33.33333333333333%", "width": "9.782608695652174%", "height": "6.574923547400612%"},

			"10a": {"left": "89.97584541062802%", "top": "60.55045871559633%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			"10b": {"left": "89.97584541062802%", "top": "67.12538226299695%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			"10c": {"left": "89.97584541062802%", "top": "73.70030581039755%", "width": "9.782608695652174%", "height": "6.422018348623854%"},
			"10d": {"left": "89.97584541062802%", "top": "80.27522935779816%", "width": "9.782608695652174%", "height": "6.422018348623854%"},

			"11": {"left": "80.55555555555556%", "top": "87.46177370030581%", "width": "7.729468599033816%", "height": "11.467889908256881%"},
			"12": {"left": "70.28985507246377%", "top": "87.46177370030581%", "width": "7.729468599033816%", "height": "11.467889908256881%"},
			"13a": {"left": "59.90338164251208%", "top": "87.46177370030581%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			"13b": {"left": "59.90338164251208%", "top": "73.70540140601108%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			"14": {"left": "49.39613526570048%", "top": "87.46177370030581%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			"15a": {"left": "39.1304347826087%", "top": "87.46177370030581%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			"15b": {"left": "39.1304347826087%", "top": "73.70540140601108%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			"16": {"left": "5.9178743961352656%", "top": "85.93272171253823%", "width": "7.85024154589372%", "height": "12.996941896024464%"},

			"17": {"left": "0.36231884057971015%", "top": "74.6177370030581%", "width": "10.144927536231885%", "height": "9.938837920489296%"},
			"18": {"left": "0.36231884057971015%", "top": "62.84403669724771%", "width": "10.144927536231885%", "height": "9.785932721712538%"},
			"19": {"left": "0.36231884057971015%", "top": "28.287461773700306%", "width": "10.144927536231885%", "height": "10.397553516819572%"},
			"20": {"left": "0.36231884057971015%", "top": "15.749235474006115%", "width": "10.144927536231885%", "height": "10.397553516819572%"}
		};
		
		$.each(stations, function(station_id, station){
			station.element = $("<div />", {
				"class": "station id-"+station_id,
				"css": {
					"left": station.left,
					"top": station.top,
					"width": station.width,
					"height": station.height
				},
				"html": $("<span />", {
					"class": "station_id",
					"html": station_id
				}),
				"click": function(){
					$.ajax({
						url: "<?php echo pines_url('com_customertimer', 'login_json'); ?>",
						type: "POST",
						data: {"id": 259, "station": station_id},
						dataType: "json",
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured while trying to log the user in:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (!data) {
								alert("The user couldn't be logged in.");
							} else {
								alert("The user has been logged in.");
								update_status();
							}
						}
					});
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

			station.insert_customer = function (customer){
				station.customer = customer;
				station.element.addClass("filled").children("span.ui-button-text").append($("<span />", {
					"class": "points_remain"
				})).append($("<span />", {
					"class": "name"
				}));
				station.update_customer(customer);
			};
			station.check_customer = function (customer){
				if (!station.customer)
					return false;
				return (customer.guid == station.customer.guid);
			};
			station.update_customer = function (customer){
				station.element.find(".name").html(customer.name);
				station.element.find(".points_remain").html(customer.points_remain);
				if (customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_critical; ?>) {
					station.element.removeClass("ok").removeClass("warning").addClass("critical");
					worst_status = "critical";
				} else if (customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_warning; ?>) {
					station.element.removeClass("ok").addClass("warning").removeClass("critical");
					if (worst_status != "critical")
						worst_status = "warning";
				} else {
					station.element.addClass("ok").removeClass("warning").removeClass("critical");
				}
				if (customer.points_remain <= 0)
					station.element.effect("pulsate");
			};
			station.remove_customer = function (){
				station.customer = null;
				station.element.children("span.ui-button-text").children(".name").remove();
				station.element.children("span.ui-button-text").children(".points_remain").remove();
				station.element.removeClass("filled").removeClass("ok").removeClass("warning").removeClass("critical");
			};
		});

		var worst_status;
		var timer;
		function update_status() {
			$.ajax({
				url: "<?php echo pines_url('com_customertimer', 'status_json'); ?>",
				type: "GET",
				dataType: "json",
				complete: function(){
					window.clearTimeout(timer);
					timer = setTimeout(update_status, 5000);
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to refresh the status:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					worst_status = "ok";
					$.each(stations, function(station_id, station){
						var cur_customer = false;
						$.each(data, function(){
							if (station_id == this.station)
								cur_customer = this;
						});
						if (!cur_customer)
							return station.remove_customer();
						if (station.check_customer(cur_customer)) {
							station.update_customer(cur_customer);
						} else {
							station.remove_customer();
							station.insert_customer(cur_customer);
						}
					});
					if (worst_status == "critical")
						station_layout.removeClass("warning").addClass("critical");
					else if (worst_status == "warning")
						station_layout.addClass("warning").removeClass("critical");
					else
						station_layout.removeClass("warning").removeClass("critical");
				}
			});
		}

		update_status();
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