<?php

/**
 * Easy-Wi Addon
 *
 * @package    WHMCS
 * @author     Ulrich Block <ulrich.block@easy-wi.com>
 * @copyright  Copyright (c) Ulrich Block
 * @license    http://www.gnu.org/licenses/gpl-3.0
 * @version    1.9
 * @link       https://www.easy-wi.com.com/
 *
 * The Easy-WI WHMCS addon is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Easy-WI WHMCS addon is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Easy-WI.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Diese Datei ist Teil vom The Easy-WI WHMCS Addon.
 *
 * Das Easy-WI WHMCS Addon ist Freie Software: Sie koennen es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder spaeteren
 * veroeffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * Das Easy-WI WHMCS Addon wird in der Hoffnung, dass es nuetzlich sein wird, aber
 * OHNE JEDE GEWAEHELEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gewaehrleistung der MARKTFAEHIGKEIT oder EIGNUNG FUER EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License fuer weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 */

#ini_set('display_errors',1);
#error_reporting(E_ALL|E_STRICT);

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if (!defined("DS")) {
    define("DS", DIRECTORY_SEPARATOR);
}

if (!defined("WHMCS_MAIN_DIR")) {
    define("WHMCS_MAIN_DIR", substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), "modules" . DS . "servers")));
}

if (!class_exists("EasyWi")) {
    require_once(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "inc" . DS . "easywi.php");
}

# DEV documentation can be found at http://docs.whmcs.com/Provisioning_Module_Developer_Docs

function easywi_ConfigOptions() {

    $easyWiObject = new EasyWi();

    $configarray = array(
        "type" => array("FriendlyName" => "Product type", "Type" => "dropdown", "Options" => "Game server,Voice server,TSDNS,Webspace,MySQL", "Description" => ""),
        "slots" => array("FriendlyName" => "Slots", "Type" => "text", "Description" => "Can be overwritten by a configurable option with the name 'slots'. (optional)"),
        "cpu" => array("FriendlyName" => "CPU Cores", "Type" => "text", "Description" => "Sum of allowed cores. Can be overwritten by a configurable option with the name 'cpu'. (optional)."),
        "ram" => array("FriendlyName" => "Ram", "Type" => "text", "Description" => "Amount in MB. Can be overwritten by a configurable option with the name 'ram'. (optional)."),
        "hdd" => array("FriendlyName" => "HDD", "Type" => "text", "Description" => "Amount in MB. Can be overwritten by a configurable option with the name 'hdd'. (optional)."),
        "traffic" => array("FriendlyName" => "Traffic", "Type" => "text", "Description" => "Amount in MB. Can be overwritten by a configurable option with the name 'traffic'. (optional)."),
        "bindRoot" => array("FriendlyName" => "Limit to specific root(s)", "Type" => "yesno", "Description" => "Limit product to specified list of root servers."),
    );

    $masterlist = $easyWiObject->getMasterList(array(
            'gameServer' => true,
            'mysqlServer' => true,
            'tsdnsServer' => true,
            'voiceServer' => true,
            'webspaceServer' => true
        )
    );

    $serverTab = '<style type="text/css">';
    $serverTab .= '
#tabsEasyWi ul {
    display: block;
    height: 23px;
    line-height: 22px;
    list-style-type: none;
    margin: 0;
    padding: 0;
    font-size: 13px;
    border-bottom: 1px solid #CCCCCC;
}
#tabsEasyWi ul li {
    float: left;
    padding: 0;
    margin: 0 0 0 5px;
}
#tabsEasyWi ul li a, #tabsEasyWi ul li a:visited {
    display: block;
    background-color: #efefef;
    border-top: 1px solid #ccc;
    border-left: 1px solid #ccc;
    border-right: 1px solid #ccc;
    padding: 0 10px;
    margin: 0;
    color: #000;
    text-decoration: none;
    -moz-border-radius: 4px 4px 0 0;
    -webkit-border-radius: 4px 4px 0 0;
    -o-border-radius: 4px 4px 0 0;
    border-radius: 4px 4px 0 0;
}
#tabsEasyWi ul li a:hover {
    background-color: #EDF1F8;
    color: #000;
    text-decoration: underline;
}
#tabsEasyWi ul li.tabselected a, #tabsEasyWi ul li.tabselected a:visited, #tabsEasyWi ul li.tabselected a:hover {
    background-color: #fff;
    border: 1px solid #ccc;
    border-bottom: 1px solid #fff;
    color: #000;
    font-weight: bold;
}
.tab-custom-easy-wi, .tab-content-easy-wi {
    text-align: left;
    margin: 2px;
}
.tab-custom-easy-wi table td, .tab-custom-easy-wi table th, .tab-content-easy-wi table td, .tab-content-easy-wi table th {
    border: 1px solid #ccc;
    padding: 2px;
}';
    $serverTab .= '</style>';
    $serverTab .= '<div id="tabsEasyWi"><ul class="nav nav-tabs">';
    $serverTab .= '<li id="tab1nav" class="active"><a href="#tab-easy-wi-1">Game</a></li>';
    $serverTab .= '<li id="tab2nav"><a href="#tab-easy-wi-2">TSDNS</a></li>';
    $serverTab .= '<li id="tab3nav"><a href="#tab-easy-wi-3">Voice</a></li>';
    $serverTab .= '<li id="tab4nav"><a href="#tab-easy-wi-4">Web</a></li>';
    $serverTab .= '<li id="tab5nav"><a href="#tab-easy-wi-5">MySQL</a></li>';
    $serverTab .= '</ul></div>';


    $serverTab .= '<div id="tab-easy-wi-1" class="tab-content-easy-wi">';
    if (count($masterlist["gameServer"]) > 0) {
        $serverTab .= '<table><tr><th>ID</th><th>Server</th><th>Description</th><th>Usage</th><th>Game shorten</th></tr>';
        foreach ($masterlist["gameServer"] as $v) {
            $serverTab .= "<tr><td>{$v['id']}</td><td>{$v['ip']}</td><td>{$v['description']}</td><td>{$v['installedserver']}/{$v['maxserver']}</td><td>{$v['games']}</td></tr>";
        }
        $serverTab .= '</table>';
    } else {
        $serverTab .= "No server found!";
    }
    $serverTab .= '</div>';

    $serverTab .= '<div id="tab-easy-wi-2" class="tab-content-easy-wi">';
    if (count($masterlist["tsdnsServer"]) > 0) {
        $serverTab .= '<table><tr><th>ID</th><th>Server</th><th>Description</th><th>Usage</th></tr>';
        foreach ($masterlist["tsdnsServer"] as $v) {
            $serverTab .= "<tr><td>{$v['id']}</td><td>{$v['ip']}</td><td>{$v['description']}</td><td>{$v['installedDNS']}/{$v['maxDNS']}</td></tr>";
        }
        $serverTab .= '</table>';
    } else {
        $serverTab .= "No server found!";
    }
    $serverTab .= '</div>';

    $serverTab .= '<div id="tab-easy-wi-3" class="tab-content-easy-wi">';
    if (count($masterlist["voiceServer"]) > 0) {
        $serverTab .= '<table><tr><th>ID</th><th>Server</th><th>Description</th><th>Usage</th></tr>';
        foreach ($masterlist["voiceServer"] as $v) {
            $serverTab .= "<tr><td>{$v['id']}</td><td>{$v['ip']}</td><td>{$v['description']}</td><td>{$v['installedserver']}/{$v['maxserver']}</td></tr>";
        }
        $serverTab .= '</table>';
    } else {
        $serverTab .= "No server found!";
    }
    $serverTab .= '</div>';

    $serverTab .= '<div id="tab-easy-wi-4" class="tab-content-easy-wi">';
    if (count($masterlist["webspaceServer"]) > 0) {
        $serverTab .= '<table><tr><th>ID</th><th>Server</th><th>Description</th><th>Usage</th></tr>';
        foreach ($masterlist["webspaceServer"] as $v) {
            $serverTab .= "<tr><td>{$v['id']}</td><td>{$v['ip']}</td><td>{$v['description']}</td><td>{$v['installedVhosts']}/{$v['maxVhost']}</td></tr>";
        }
        $serverTab .= '</table>';
    } else {
        $serverTab .= "No server found!";
    }
    $serverTab .= '</div>';

    $serverTab .= '<div id="tab-easy-wi-5" class="tab-content-easy-wi">';
    if (count($masterlist["mysqlServer"]) > 0) {
        $serverTab .= '<table><tr><th>ID</th><th>Server</th><th>Description</th><th>Usage</th></tr>';
        foreach ($masterlist["mysqlServer"] as $v) {
            $serverTab .= "<tr><td>{$v['id']}</td><td>{$v['ip']}</td><td>{$v['description']}</td><td>{$v['dbsInstalled']}/{$v['maxDBs']}</td></tr>";
        }
        $serverTab .= '</table>';
    } else {
        $serverTab .= "No server found!";
    }
    $serverTab .= '</div>';

    $serverTab .= '<script>
        jQuery(document).ready(function(){

            jQuery(".tab-content-easy-wi").hide();

            if (jQuery(location).attr("hash").substr(1)!="") {
                var activeTab = jQuery(location).attr("hash");
                jQuery("ul").find("li").removeClass("open");
                jQuery("ul.nav li").removeClass("active");
                jQuery(activeTab + "nav").addClass("active");
                jQuery(activeTab).show();
            } else {
                jQuery("#tabsEasyWi ul.nav .nav-tabs li:first").addClass("active").show();
                jQuery(".tab-content-easy-wi:first").show();
            }

            jQuery("#tabsEasyWi ul.nav li").click(function() {
                jQuery("ul").find("li").removeClass("open");
                jQuery("ul.nav li").removeClass("active");
                jQuery(this).addClass("active");
                var activeTab = jQuery(this).find("a").attr("href");
                if (activeTab.substr(0,1)=="#" && activeTab.substr(1)!="") {
                    jQuery(".tab-content-easy-wi").hide();
                    jQuery(activeTab).fadeIn();
                    return false;
                } else {
                    return true;
                }
            });
        });
    </script>';

    $configarray["rootIDs"] = array("FriendlyName" => "RootID(s)", "Type" => "text", "Description" => "IDs in a comma seperated list (optional)" . $serverTab, "Size" => "30", "Default" => "");

    $configarray["private"] = array("FriendlyName" => "Private Server", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Can be set for game and voice server. Will enforce password protection. Can be overwritten by a configurable option with the name 'private'. (optional).", "Default" => "No");

    $configarray["brandname"] = array("FriendlyName" => "Brandname", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Can be set for game and voice server. Will enforce your branding in the server name. Can be overwritten by a configurable option with the name 'brandname'. (optional).", "Default" => "No");

    $gameTypeArray = array();

    foreach ($masterlist["games"] as $k => $v) {
        $gameTypeArray[] = "<tr><td>{$v}</td><td>{$k}</td></tr>";
    }

    $configarray["gameTypes"] = array("FriendlyName" => "Games", "Type" => "text", "Description" => "Comma seperated list (required for game products)<div class='tab-custom-easy-wi'><table><tr><th>Game</th><th>Shorten</th></tr>" . implode("", $gameTypeArray) ."</table></div>", "Size" => "30", "Default" => "");

    $configarray["preInstalled"] = array("FriendlyName" => "Install game", "Type" => "text", "Description" => "Shorten for the game that should be installed by default. Others will be installed once the user switches at Easy-Wi. If not specified none will be pre installed. Can be overwritten by a configurable option with the name 'preinstalled'. (optional).", "Default" => "");

    $configarray["tvEnable"] = array("FriendlyName" => "TV allowed", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Should SourceTV be allowed? Can be overwritten by a configurable option with the name 'tvenable'. (optional).", "Default" => "No");

    $configarray["protected"] = array("FriendlyName" => "Protection Mode", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Should the protection mode required by the gaming league ESL be allowed? Can be overwritten by a configurable option with the name 'protected'. (optional).", "Default" => "No");

    $configarray["eacAllowed"] = array("FriendlyName" => "EAC", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Should commercial software Easy Anti Cheat be allowed? Can be overwritten by a configurable option with the name 'eac'. (optional).", "Default" => "No");

    $configarray[""] = array();

    $configarray["forceBanner"] = array("FriendlyName" => "Force host banner", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Can be used with Teamspeak 3. Can be overwritten by a configurable option with the name 'forcebanner'. (optional).", "Default" => "No");

    $configarray["forceButton"] = array("FriendlyName" => "Force host button", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Can be used with Teamspeak 3. Can be overwritten by a configurable option with the name 'forcebutton'. (optional).", "Default" => "No");

    $configarray["forceWelcome"] = array("FriendlyName" => "Force welcome message", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Can be used with Teamspeak 3. Can be overwritten by a configurable option with the name 'forcewelcome'. (optional).", "Default" => "No");

    $configarray["homeDirLabel"] = array("FriendlyName" => "Home dir label", "Type" => "text", "Description" => "Can be used with game servers. Can be overwritten by a configurable option with the name 'homelabel'. (optional).", "Default" => "home");

    return $configarray;
}

# Unfortunately we cannot create a global product CRUD hook. So as a workaround abstraction within the easy-wi class.

# -> create
function easywi_CreateAccount($vars) {

    $easyWiObject = new EasyWi();

    return $easyWiObject->orderProvision($vars);
}

# -> set to inactive
function easywi_SuspendAccount($vars) {

    $easyWiObject = new EasyWi();

    return $easyWiObject->orderProvision($vars);
}

# -> set to active
function easywi_UnsuspendAccount($vars) {

    $easyWiObject = new EasyWi();

    return $easyWiObject->orderProvision($vars);
}

# -> remove
function easywi_TerminateAccount($vars) {

    $easyWiObject = new EasyWi();

    return $easyWiObject->orderProvision($vars);
}

# -> modify
function easywi_ChangePackage($vars) {

    $easyWiObject = new EasyWi();

    return $easyWiObject->orderProvision($vars);
}

function easywi_ClientAreaCustomButtonArray() {
    $buttonarray = array(
        "Reset Easy-Wi Login" => "pwreset"
    );
    return $buttonarray;
}

function easywi_ClientArea($vars) {

    $easyWiObject = new EasyWi();

    return array(
        "templatefile" => "clientarea",
        'vars' => array(
            "serviceid" => $vars["serviceid"],
            "vars" => $vars,
            "EasyWiLink" => $easyWiObject->url,
            "customFields" => $easyWiObject->getCustomServiceFields($vars["serviceid"]),
        ),
    );
}

function easywi_pwreset($vars) {

    if (isset($_POST["password"]) && isset($_POST["passwordRepeat"])) {

        if ($_POST["password"] != $_POST["passwordRepeat"]) {

            return "Passwords do not match";

        } else if (strlen($_POST["password"]) == 0) {

            return "No password given";

        } else if ($_POST["password"] == $_POST["passwordRepeat"] && !preg_match('/^[\w\[\]\-\@\(\)\<\>\!\"\.$%&\/=\?*+#]{6,255}$/', trim($_POST["password"]))) {

            return "Password is too short or contains illegal characters";

        } else if ($_POST["password"] == $_POST["passwordRepeat"] && preg_match('/^[\w\[\]\-\@\(\)\<\>\!\"\.$%&\/=\?*+#]{6,255}$/', trim($_POST["password"]))) {

            $userDetails = $vars["clientsdetails"];
            $userDetails["password"] = trim($_POST["password"]);

            $easyWiObject = new EasyWi();

            return ($easyWiObject->changePassword($userDetails, true)) ? "success" : "Could not update Easy-Wi user data";

        }
        return "Unknown error";

    }

    $easyWiObject = new EasyWi();

    return array(
        "templatefile" => "clientarea_password",
        'vars' => array(
            "serviceid" => $vars["serviceid"],
            "vars" => $vars,
            "EasyWiLink" => $easyWiObject->url,
            "customFields" => $easyWiObject->getCustomServiceFields($vars["serviceid"]),
        ),
    );

}