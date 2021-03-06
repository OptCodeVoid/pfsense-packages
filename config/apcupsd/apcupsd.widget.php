<?php

/*
 * apcupsd.widget.php
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2021 Rubicon Communications, LLC (Netgate)
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once("guiconfig.inc");
require_once("pfsense-utils.inc");
require_once("functions.inc");
require_once("/usr/local/www/widgets/include/apcupsd_inc.inc");

if (!function_exists('compose_apc_contents')) {
	function compose_apc_contents($widgetkey) {
		global $user_settings;
		
		if (!isset($user_settings["widgets"][$widgetkey]["apc_temp_dis_type"])) {
			$user_settings["widgets"][$widgetkey]["apc_temp_dis_type"] = "both_deg";
		}		
		if (!isset($user_settings["widgets"][$widgetkey]["apc_host_dis"])) {
			$user_settings["widgets"][$widgetkey]["apc_host_dis"] = "no";
		}
		if (!isset($user_settings["widgets"][$widgetkey]["apc_temp_dis_type_var"])) {
			$user_settings["widgets"][$widgetkey]["apc_temp_dis_type_var"] = "1";
		}
		if (!isset($user_settings["widgets"][$widgetkey]["apc_load_warning_threshold"])) {
			$user_settings["widgets"][$widgetkey]["apc_load_warning_threshold"] = "75";
		}
		if (!isset($user_settings["widgets"][$widgetkey]["apc_load_critical_threshold"])) {
			$user_settings["widgets"][$widgetkey]["apc_load_critical_threshold"] = "90";
		}
		if (!isset($user_settings["widgets"][$widgetkey]["apc_temp_warning_threshold"])) {
			$user_settings["widgets"][$widgetkey]["apc_temp_warning_threshold"] = "27";
		}
		if (!isset($user_settings["widgets"][$widgetkey]["apc_temp_critical_threshold"])) {
			$user_settings["widgets"][$widgetkey]["apc_temp_critical_threshold"] = "40";
		}
		if (!isset($user_settings["widgets"][$widgetkey]["apc_charge_warning_threshold"])) {
			$user_settings["widgets"][$widgetkey]["apc_charge_warning_threshold"] = "50";
		}
		if (!isset($user_settings["widgets"][$widgetkey]["apc_charge_critical_threshold"])) {
			$user_settings["widgets"][$widgetkey]["apc_charge_critical_threshold"] = "15";
		}
		if (!isset($user_settings["widgets"][$widgetkey]["apc_bage_warning_threshold"])) {
			$user_settings["widgets"][$widgetkey]["apc_bage_warning_threshold"] = "365";
		}
		if (!isset($user_settings["widgets"][$widgetkey]["apc_bage_critical_threshold"])) {
			$user_settings["widgets"][$widgetkey]["apc_bage_critical_threshold"] = "720";
		}
		
		if (!(include_once "/usr/local/pkg/apcupsd.inc")) {
			$rtnstr = "";
			$rtnstr .= "<div class=\"panel panel-warning responsive\"><div class=\"panel-heading\"><h2 class=\"panel-title\">apcupsd not installed...</h2></div>\n";
			$rtnstr .= "<pre>\n";
			$rtnstr .= "Please install and configure apcupsd before using this widget.<br />\n";
			$rtnstr .= "</pre>\n";
			$rtnstr .= "</div>\n";
			print($rtnstr);
			exit(1);
		}
		
		if (check_nis_running_apcupsd()) {
			$nisip = (check_nis_ip_apcupsd() != ''? check_nis_ip_apcupsd() : "localhost");
			$nisport = (check_nis_port_apcupsd() != '' ? check_nis_port_apcupsd() : "3551");

			$ph = popen("apcaccess -h {$nisip}:{$nisport} 2>&1", "r" );
			while ($v = fgets($ph)) {
				$results[trim(explode(': ',$v)[0])]=trim(explode(': ',$v)[1]);
			}
			pclose($ph);

			$rtnstr = "";

			$rtnstr .= "<tr><td>Status</td><td colspan=\"3\">\n";
			
			if($results != null) {
				switch ($results['STATUS']) {
					default:
					case 'ONBATT':
						if (str_replace(" Percent", "", $results['BCHARGE']) <= 25) {
							$rtnstr .= "<span class=\"fa fa-battery-empty\" style=\"color:red;font-size:2em;transform: rotate(270deg);\"/><span style=\"color:red;font-style:bold;font-size:2em\">\n";
						} else if (str_replace(" Percent", "", $results['BCHARGE']) <= 50) {
							$rtnstr .= "<span class=\"fa fa-battery-quarter\" style=\"color:red;font-size:2em;transform: rotate(270deg);\"/><span style=\"color:red;font-style:bold;font-size:2em\">\n";
						} else if (str_replace(" Percent", "", $results['BCHARGE']) <= 75) {
							$rtnstr .= "<span class=\"fa fa-battery-half\" style=\"color:red;font-size:2em;transform: rotate(270deg);\"/><span style=\"color:red;font-style:bold;font-size:2em\">\n";
						} else if (str_replace(" Percent", "", $results['BCHARGE']) <= 99) {
							$rtnstr .= "<span class=\"fa fa-battery-three-quarters\" style=\"color:red;font-size:2em;transform: rotate(270deg);\"/><span style=\"color:red;font-style:bold;font-size:2em\">\n";
						} else {
							$rtnstr .= "<span class=\"fa fa-battery-full\" style=\"color:red;font-size:2em;transform: rotate(270deg);\"/><span style=\"color:red;font-style:bold;font-size:2em\">\n";
						}
						break;
					case 'CHARGING':
						if (str_replace(" Percent", "", $results['BCHARGE']) <= 25) {
							$rtnstr .= "<span class=\"fa fa-battery-empty\" style=\"color:orange;font-size:2em;transform: rotate(270deg);\"/><span style=\"color:orange;font-style:bold;font-size:2em\">\n";
						} else if (str_replace(" Percent", "", $results['BCHARGE']) <= 50) {
							$rtnstr .= "<span class=\"fa fa-battery-quarter\" style=\"color:orange;font-size:2em;transform: rotate(270deg);\"/><span style=\"color:orange;font-style:bold;font-size:2em\">\n";
						} else if (str_replace(" Percent", "", $results['BCHARGE']) <= 75) {
							$rtnstr .= "<span class=\"fa fa-battery-half\" style=\"color:orange;font-size:2em;transform: rotate(270deg);\"/><span style=\"color:orange;font-style:bold;font-size:2em\">\n";
						} else if (str_replace(" Percent", "", $results['BCHARGE']) <= 99) {
							$rtnstr .= "<span class=\"fa fa-battery-three-quarters\" style=\"color:orange;font-size:2em;transform: rotate(270deg);\"/><span style=\"color:orange;font-style:bold;font-size:2em\">\n";
						} else {
							$rtnstr .= "<span class=\"fa fa-battery-full\" style=\"color:orange;font-size:2em;transform: rotate(270deg);\"/><span style=\"color:orange;font-style:bold;font-size:2em\">\n";
						}
						break;
					case 'ONLINE':
						$rtnstr .= "<span class=\"fa fa-plug\" style=\"color:green;font-size:2em;\"/><span style=\"color:green;font-style:bold;font-size:2em\">\n";
						break;
				}
				$rtnstr .= "&nbsp;" . $results['STATUS'] . (($user_settings["widgets"][$widgetkey]["apc_host_dis"] == "yes") ? " (" . $nisip . ":" . $nisport . ")" : "") . "</span>\n";
				$rtnstr .= (($results['LASTXFER']!='') ? "<br /><span style=\"padding-left:1em\"/>Last Transfer: &nbsp;" . $results['LASTXFER'] . "\n" : '');
			
				$rtnstr .= "</td></tr><tr><td>Line Voltage</td>\n";
				$rtnstr .= "<td><span class=\"fa fa-bolt\"/>&nbsp;" . $results['LINEV'] . (($results['LINEFREQ']!='') ? ' (' . $results['LINEFREQ'] . ')' : "") . "</td>\n";
				$rtnstr .= "<td>Out Voltage</td>\n";
				$rtnstr .= "<td><span class=\"fa fa-bolt\"/>&nbsp;" . $results['OUTPUTV'] . "</td>\n";
				$rtnstr .= "</tr><tr><td>Load</td><td colspan=\"3\">\n";
				
				$rtnstr .= "<div class=\"progress\">";
				if (str_replace(" Percent", "", $results['LOADPCT']) >= $user_settings["widgets"][$widgetkey]["apc_load_critical_threshold"]) {
					$rtnstr .= "<div id=\"apcupsd_load_meter\" class=\"progress-bar progress-bar-striped progress-bar-success\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . $user_settings["widgets"][$widgetkey]["apc_load_warning_threshold"] . "%\"></div>\n";
					$rtnstr .= "<div id=\"apcupsd_load_meter\" class=\"progress-bar progress-bar-striped progress-bar-warning\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . ($user_settings["widgets"][$widgetkey]["apc_load_critical_threshold"] - $user_settings["widgets"][$widgetkey]["apc_load_warning_threshold"]) . "%\"></div>\n";
					$rtnstr .= "<div id=\"apcupsd_load_meter\" class=\"progress-bar progress-bar-striped progress-bar-danger\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . (str_replace(" Percent", "", $results['LOADPCT']) - $user_settings["widgets"][$widgetkey]["apc_load_critical_threshold"]) . "%\"></div>\n";
				} else if (str_replace(" Percent", "", $results['LOADPCT']) >= $user_settings["widgets"][$widgetkey]["apc_load_warning_threshold"]) {
					$rtnstr .= "<div id=\"apcupsd_load_meter\" class=\"progress-bar progress-bar-striped progress-bar-success\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . $user_settings["widgets"][$widgetkey]["apc_load_warning_threshold"] . "%\"></div>\n";
					$rtnstr .= "<div id=\"apcupsd_load_meter\" class=\"progress-bar progress-bar-striped progress-bar-warning\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . (str_replace(" Percent", "", $results['LOADPCT']) - $user_settings["widgets"][$widgetkey]["apc_load_warning_threshold"]) . "%\"></div>\n";
				} else {
					$rtnstr .= "<div id=\"apcupsd_load_meter\" class=\"progress-bar progress-bar-striped progress-bar-success\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . str_replace(" Percent", "%", $results['LOADPCT']) . "\"></div>\n";
				}
				$rtnstr .= "</div><span class=\"fa fa-angle-double-down\"/>&nbsp;" . str_replace(" Percent", "%", $results['LOADPCT']) . "&nbsp;";
				
				$rtnstr .= "</td></tr><tr><td>Temp</td><td colspan=\"3\">\n";
				$degf = ((substr(($results['ITEMP']), -1, 1) === "C") ? (((substr(($results['ITEMP']), 0, (strlen($results['ITEMP'])-2)))*(9/5))+(32)) : (substr(($results['ITEMP']), 0, (strlen($results['ITEMP'])-2)))) . "°F";
				$degc = ((substr(($results['ITEMP']), -1, 1) === "C") ? (substr(($results['ITEMP']), 0, (strlen($results['ITEMP'])-2))) : (((substr(($results['ITEMP']), 0, (strlen($results['ITEMP'])-2)))-32)*(5/9))) . "°C";
				
				$rtnstr .= "<div class=\"progress\">\n";
				$tempmax = 60;
				if (substr(($degc), 0, (strlen($degc)-2)) >= $user_settings["widgets"][$widgetkey]["apc_temp_critical_threshold"]) {
					$rtnstr .= "<div id=\"apcupsd_temp_meter\" class=\"progress-bar progress-bar-striped progress-bar-success\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . (($user_settings["widgets"][$widgetkey]["apc_temp_warning_threshold"]/$tempmax)*100) . "%\"></div>\n";
					$rtnstr .= "<div id=\"apcupsd_temp_meter\" class=\"progress-bar progress-bar-striped progress-bar-warning\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . ((($user_settings["widgets"][$widgetkey]["apc_temp_critical_threshold"] - $user_settings["widgets"][$widgetkey]["apc_temp_warning_threshold"])/$tempmax)*100) . "%\"></div>\n";
					$rtnstr .= "<div id=\"apcupsd_temp_meter\" class=\"progress-bar progress-bar-striped progress-bar-danger\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . (((ceil(substr(($degc), 0, (strlen($degc)-2))) - $user_settings["widgets"][$widgetkey]["apc_temp_critical_threshold"])/$tempmax)*100) . "%\"></div>\n";
				} else if (substr(($degc), 0, (strlen($degc)-2)) >= $user_settings["widgets"][$widgetkey]["apc_temp_warning_threshold"]) {
					$rtnstr .= "<div id=\"apcupsd_temp_meter\" class=\"progress-bar progress-bar-striped progress-bar-success\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: 50%\"></div>\n";
					$rtnstr .= "<div id=\"apcupsd_temp_meter\" class=\"progress-bar progress-bar-striped progress-bar-warning\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . (((ceil(substr(($degc), 0, (strlen($degc)-2))) - $user_settings["widgets"][$widgetkey]["apc_temp_warning_threshold"])/$tempmax)*100) . "%\"></div>\n";
				} else {
					$rtnstr .= "<div id=\"apcupsd_temp_meter\" class=\"progress-bar progress-bar-striped progress-bar-success\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . ((ceil(substr(($degc), 0, (strlen($degc)-2)))/$tempmax)*100) . "%\"></div>\n";
				}
				
				$rtnstr .= "</div>\n";				
				switch($user_settings['widgets'][$widgetkey]['apc_temp_dis_type']) {
					default:
					case 'degc':
						$rtnstr .= "<span class=\"fa fa-bug\"/>&nbsp;&nbsp;" . $degc . "&nbsp;";
						break;
					case 'degf':
						$rtnstr .= "<span class=\"fa fa-bug\"/>&nbsp;&nbsp;" . $degf . "&nbsp;";
						break;
					case 'both_deg':
						if ($user_settings["widgets"][$widgetkey]["apc_temp_dis_type_var"]=="1") {
							$rtnstr .= "<span class=\"fa fa-bug\"/>&nbsp;&nbsp;" . $degc . "&nbsp;(" . $degf . ")&nbsp;";
						} else {
							$rtnstr .= "<span class=\"fa fa-bug\"/>&nbsp;&nbsp;" . $degf . "&nbsp;(" . $degc . ")&nbsp;";
						}
						break;
				}
				$rtnstr .= "</td></tr><tr><td>Battery Charge</td>\n";
				
				if (str_replace(" Percent", "", $results['BCHARGE']) <= $user_settings["widgets"][$widgetkey]["apc_charge_critical_threshold"]) {
					$rtnstr .= "<td colspan=\"3\"><div class=\"progress\"><div id=\"apcupsd_bcharge_meter\" class=\"progress-bar progress-bar-striped progress-bar-danger\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . str_replace(" Percent", "%", $results['BCHARGE']) . "\"></div></div>\n";
				} else if (str_replace(" Percent", "", $results['BCHARGE']) <= $user_settings["widgets"][$widgetkey]["apc_charge_warning_threshold"]) {
					$rtnstr .= "<td colspan=\"3\"><div class=\"progress\"><div id=\"apcupsd_bcharge_meter\" class=\"progress-bar progress-bar-striped progress-bar-warning\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . str_replace(" Percent", "%", $results['BCHARGE']) . "\"></div></div>\n";
				} else {
					$rtnstr .= "<td colspan=\"3\"><div class=\"progress\"><div id=\"apcupsd_bcharge_meter\" class=\"progress-bar progress-bar-striped progress-bar-success\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . str_replace(" Percent", "%", $results['BCHARGE']) . "\"></div></div>\n";
				}
				$rtnstr .= "<span class=\"fa fa-battery-full\" />&nbsp;" . str_replace(" Percent", "%", $results['BCHARGE']) . "\n";
				
				$rtnstr .= "<span class=\"fa fa-bolt\" style=\"padding-left:1em\" />&nbsp;" . $results['BATTV'] . "</td>\n";
				$rtnstr .= "</tr><tr><td>Time Remaining</td>";
				$rtnstr .= "<td colspan=\"3\"><span class=\"fa fa-clock-o\"/>&nbsp;" . $results['TIMELEFT'] . "</td>\n";
				$rtnstr .= "</tr><tr><td>Battery Age</td>";
				
				$rtnstr .= "<td colspan=\"3\">\n";
				$batt_org = (new DateTime($results['BATTDATE']));
				$dtnow = (new DateTime());
				//$batt_age_str = ($batt_org->diff($dtnow))->format("Year:%y;Month:%m;Day:%d;Hour:%h;Minute:%i;Second:%s");
				$batt_age_str = ($batt_org->diff($dtnow))->format("Year:%y;Month:%m;Day:%d;Hour:%h");
				$batt_age_fstr = '';
				$batt_age = array();
				
				foreach(explode(";", $batt_age_str) as $name=>$v) {
					$batt_age[trim(explode(":",$v)[0])]=trim(explode(":",$v)[1]);
					
					if (trim(explode(":",$v)[1]) != 0) {
						$batt_age_fstr .= (trim(explode(":",$v)[1])) . "&nbsp;" . (trim(explode(":",$v)[0])) . ((trim(explode(":",$v)[1]) != 1) ? "s" : "") . "&nbsp;";
					}
				}
				
				if($batt_age['Day'] >= $user_settings["widgets"][$widgetkey]["apc_bage_critical_threshold"]) {
					$rtnstr .= "<span style=\"color:red;font-style:bold;font-size:1em;\"><span class=\"fa fa-calendar-times-o\"/>";
				} else if ($batt_age['Day'] >= $user_settings["widgets"][$widgetkey]["apc_bage_warning_threshold"]) {
					$rtnstr .= "<span style=\"color:orange;font-style:bold;font-size:1em;\"><span class=\"fa fa-calendar-minus-o\"/>";
				} else {
					$rtnstr .= "<span style=\"color:green;font-style:bold;font-size:1em;\"><span class=\"fa fa-calendar-check-o\"/>";
				}
				$rtnstr .= "&nbsp;" . $batt_age_fstr . "&nbsp;(" . $batt_org->format("m/d/Y") . ")</span><br />\n";
				
				$rtnstr .= ((!$results['SELFTEST'] == 'OK') ? "<span style=\"color:orange;font-style:bold;font-size:0.9em;padding-left:1em\"/><span class=\"fa fa-exclamation-triangle\"/>" : "<span class=\"fa fa-check-square\" style=\"color:green;font-style:bold;font-size:0.9em;padding-left:1em\">\n");
				$rtnstr .= "&nbsp;Last Test:&nbsp;" . $results['SELFTEST'] . "</span>\n";
				$rtnstr .= "</td></tr>\n";
			} else {
				$rtnstr .= "<div class=\"panel panel-warning responsive\"><div class=\"panel-heading\"><h2 class=\"panel-title\">Status information from apcupsd</h2></div>\n";
				$rtnstr .= "<pre>\n";
				$rtnstr .= "Error retrieving data... <br />\n";
				$rtnstr .= "</pre>\n";
				$rtnstr .= "</div>\n";
			}
		} else {
			$rtnstr .= "<div class=\"panel panel-warning responsive\"><div class=\"panel-heading\"><h2 class=\"panel-title\">Status information from apcupsd</h2></div>\n";
			$rtnstr .= "<pre>\n";
			$rtnstr .= "Network Information Server (NIS) not running, in order to run apcaccess on localhost, you need to enable it on APCupsd General settings. <br />\n";
			$rtnstr .= "</pre>\n";
			$rtnstr .= "</div>\n";
		}
		return($rtnstr);
	}
}

if ($_REQUEST && $_REQUEST['ajax']) {
    print(compose_apc_contents($_REQUEST['widgetkey']));
	exit;
}

if ($_POST['widgetkey']) {
	set_customwidgettitle($user_settings);

	if (!is_array($user_settings["widgets"][$_POST['widgetkey']])) {
		$user_settings["widgets"][$_POST['widgetkey']] = array();
	}
	if (isset($_POST["apc_temp_dis_type"])) {
		$user_settings["widgets"][$_POST['widgetkey']]["apc_temp_dis_type"] = $_POST["apc_temp_dis_type"];
	}
	if (isset($_POST["apc_host_dis"])){
		$user_settings["widgets"][$_POST['widgetkey']]["apc_host_dis"] = $_POST["apc_host_dis"];
	}
	if (isset($_POST["apc_temp_dis_type_var"])) {
		$user_settings["widgets"][$_POST['widgetkey']]["apc_temp_dis_type_var"] = $_POST["apc_temp_dis_type_var"];
	}
	if (isset($_POST["apc_load_warning_threshold"])) {
		$user_settings["widgets"][$_POST['widgetkey']]["apc_load_warning_threshold"] = $_POST["apc_load_warning_threshold"];
	}
	if (isset($_POST["apc_load_critical_threshold"])) {
		$user_settings["widgets"][$_POST['widgetkey']]["apc_load_critical_threshold"] = $_POST["apc_load_critical_threshold"];
	}
	if (isset($_POST["apc_temp_warning_threshold"])) {
		$user_settings["widgets"][$_POST['widgetkey']]["apc_temp_warning_threshold"] = $_POST["apc_temp_warning_threshold"];
	}
	if (isset($_POST["apc_temp_critical_threshold"])) {
		$user_settings["widgets"][$_POST['widgetkey']]["apc_temp_critical_threshold"] = $_POST["apc_temp_critical_threshold"];
	}
	if (isset($_POST["apc_charge_warning_threshold"])) {
		$user_settings["widgets"][$_POST['widgetkey']]["apc_charge_warning_threshold"] = $_POST["apc_charge_warning_threshold"];
	}
	if (isset($_POST["apc_charge_critical_threshold"])) {
		$user_settings["widgets"][$_POST['widgetkey']]["apc_charge_critical_threshold"] = $_POST["apc_charge_critical_threshold"];
	}
	if (isset($_POST["apc_bage_warning_threshold"])) {
		$user_settings["widgets"][$_POST['widgetkey']]["apc_bage_warning_threshold"] = $_POST["apc_bage_warning_threshold"];
	}
	if (isset($_POST["apc_bage_critical_threshold"])) {
		$user_settings["widgets"][$_POST['widgetkey']]["apc_bage_critical_threshold"] = $_POST["apc_bage_critical_threshold"];
	}

	save_widget_settings($_SESSION['Username'], $user_settings["widgets"], gettext("Updated apcupsd widget settings via dashboard."));

	header("Location: /");
	exit(0);
}
$widgetperiod = isset($config['widgets']['period']) ? $config['widgets']['period'] * 1000 : 10000;

?>
<table class="table table-hover table-striped table-condensed">
	<tbody id="<?=htmlspecialchars($widgetkey)?>-apcupsdimpbody">
		<?PHP gettext("Loading..."); ?>
	</tbody>
</table>
<!-- <a id="apcupsd_apcaccess_refresh" href="#" class="fa fa-refresh" style="display: none;"></a> -->
</div><div id="<?=$widget_panel_footer_id?>" class="panel-footer collapse">
<form action="/widgets/widgets/apcupsd.widget.php" method="post" class="form-horizontal">
	<?=gen_customwidgettitle_div($widgetconfig['title']); ?>
	<div class="form-group">
		<label class="col-sm-4 control-label"><?=gettext("Display NIS IP:Port")?></label>
		<div class="col-sm-6">
			<div class="radio">
				<label><input name="apc_host_dis" type="radio" id="apc_host_dis" style="padding-right:0.5em" value="yes" <?=(($user_settings["widgets"][$widgetkey]["apc_host_dis"] == "yes") ? "checked" : ""); ?> /> <?=gettext("Yes")?></label>
				<label><input name="apc_host_dis" type="radio" id="apc_host_dis" style="padding-left:1em" value="no" <?=(($user_settings["widgets"][$widgetkey]["apc_host_dis"] == "no") ? "checked" : ""); ?> /> <?=gettext("No")?></label>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-4 control-label"><?=gettext('Temperature')?></label>
		<div class="col-sm-6">
			<div class="radio">
				<label><input name="apc_temp_dis_type" type="radio" id="apc_temp_dis_type_degf" value="degc" <?=(($user_settings["widgets"][$widgetkey]["apc_temp_dis_type"] == "degc") ? "checked" : ""); ?> /> <?=gettext("Use °C")?></label>
			</div>
			<div class="radio">
				<label><input name="apc_temp_dis_type" type="radio" id="apc_temp_dis_type_degc" value="degf" <?=(($user_settings["widgets"][$widgetkey]["apc_temp_dis_type"] == "degf") ? "checked" : ""); ?> /><?=gettext("Use °F")?></label>
			</div>
			<div class="radio">
				<label>
					<input name="apc_temp_dis_type" type="radio" id="apc_temp_dis_type_both_deg" value="both_deg" <?=(($user_settings["widgets"][$widgetkey]["apc_temp_dis_type"] == "both_deg") ? "checked" : ""); ?> /><?=gettext("Both: ")?>
					<select name="apc_temp_dis_type_var" id="apc_temp_dis_type_both_deg_var">
						<option value="1" <?=(($user_settings["widgets"][$widgetkey]["apc_temp_dis_type_var"] == "1") ? "selected" : ""); ?> >°C (°F)</option>
						<option value="2" <?=(($user_settings["widgets"][$widgetkey]["apc_temp_dis_type_var"] == "2") ? "selected" : ""); ?> >°F (°C)</option>
					</select>
				</label>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-4 control-label"><?=gettext('Load Levels')?></label>
		<div class="col-sm-6">
			<div class="col-sm-4">
				<label><?=gettext('Warning')?><input type="text"name="apc_load_warning_threshold" id="apc_load_warning_threshold" value="<?= gettext($user_settings["widgets"][$widgetkey]["apc_load_warning_threshold"]); ?>" class="form-control" /></label>
			</div>
			<div class="col-sm-4">
				<label><?=gettext('Critical')?><input type="text" name="apc_load_critical_threshold" id="apc_load_critical_threshold" value="<?= gettext($user_settings["widgets"][$widgetkey]["apc_load_critical_threshold"]); ?>" class="form-control" /></label>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-4 control-label"><?=gettext('Temp Levels')?></label>
		<div class="col-sm-7">
			<div class="col-sm-5">
				<label><?=gettext('Warning (°C)')?><input type="text" maxlength="2" size="2"  name="apc_temp_warning_threshold" id="apc_temp_warning_threshold" value="<?= gettext($user_settings["widgets"][$widgetkey]["apc_temp_warning_threshold"]); ?>" class="form-control" /></label>
			</div>
			<div class="col-sm-5">
				<label><?=gettext('Critical (°C)')?><input type="text" maxlength="2" size="2" name="apc_temp_critical_threshold" id="apc_temp_critical_threshold" value="<?= gettext($user_settings["widgets"][$widgetkey]["apc_temp_critical_threshold"]); ?>" class="form-control" /></label>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-4 control-label"><?=gettext('Charge Levels')?></label>
		<div class="col-sm-6">
			<div class="col-sm-4">
				<label><?=gettext('Warning')?><input type="text" name="apc_charge_warning_threshold" id="apc_charge_warning_threshold" value="<?= gettext($user_settings["widgets"][$widgetkey]["apc_charge_warning_threshold"]); ?>" class="form-control" /></label>
			</div>
			<div class="col-sm-4">
				<label><?=gettext('Critical')?><input type="text" name="apc_charge_critical_threshold" id="apc_charge_critical_threshold" value="<?= gettext($user_settings["widgets"][$widgetkey]["apc_charge_critical_threshold"]); ?>" class="form-control" /></label>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-4 control-label"><?=gettext('Battery Age Levels')?></label>
		<div class="col-sm-6">
			<div class="col-sm-4">
				<label><?=gettext('Warning')?><input type="text" name="apc_bage_warning_threshold" id="apc_bage_warning_threshold" value="<?= gettext($user_settings["widgets"][$widgetkey]["apc_bage_warning_threshold"]); ?>" class="form-control" /></label>
			</div>
			<div class="col-sm-4">
				<label><?=gettext('Critical')?><input type="text" name="apc_bage_critical_threshold" id="apc_bage_critical_threshold" value="<?= gettext($user_settings["widgets"][$widgetkey]["apc_bage_critical_threshold"]); ?>" class="form-control" /></label>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div align="center">
			<input type="hidden" name="widgetkey" value="<?=htmlspecialchars($widgetkey); ?>">
			<button type="submit" class="btn btn-primary"><i class="fa fa-save icon-embed-btn"></i><?=gettext('Save')?></button>
		</div>
	</div>
</form>

<script type="text/javascript">
events.push(function()
{
	function apcupsd_refresh_callback(s){
		$(<?= json_encode('#' . $widgetkey . '-apcupsdimpbody')?>).html(s);
	}

	var postdata = {
		ajax: "ajax",
		widgetkey : <?=json_encode($widgetkey)?>
	};

	var refreshObject = new Object();
	refreshObject.name = "RefreshAPCUPSD";
	refreshObject.url = "/widgets/widgets/apcupsd.widget.php";
	refreshObject.callback = apcupsd_refresh_callback;
	refreshObject.parms = postdata;
	refreshObject.freq = 1;

	register_ajax(refreshObject);
});
</script>
