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

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if (!defined("DS")) {
    define("DS", DIRECTORY_SEPARATOR);
}

if (!defined("WHMCS_MAIN_DIR")) {
    define("WHMCS_MAIN_DIR", substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), "modules" . DS . "addons")));
}

if (!class_exists("EasyWi")) {
    require_once(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "inc" . DS . "easywi.php");
}

function easywi_config() {

    $version = "1.9";

    $easyWiObject = new EasyWi();
    $newestVersion = $easyWiObject->checkForUpdate();

    $description = "This addon allows replication from WHMCS to Easy-Wi and to pull data from Easy-Wi into WHMCS.";
    if ($version < $newestVersion) {
        $description .= " You are running the outdated version {$version} while the most recent one is {$newestVersion}.";
    }

    $configarray = array(
        "name" => "Easy-Wi",
        "description" => $description,
        "version" => $version,
        "author" => "Ulrich Block",
        "language" => "english",
        "premium" => true,
    );

    $configarray["fields"]["license"] = array ("FriendlyName" => "License key", "Type" => "text", "Size" => "30", "Description" => "", "Default" => "");
    $configarray["fields"]["provisioning"] = array ("FriendlyName" => "Provisioning", "Type" => "dropdown", "Options" => "Yes,No", "Description" => "Activate provisioning. If set to no, no actions will be send to the Easy-Wi installation.");
    $configarray["fields"]["url"] = array ("FriendlyName" => "Easy-Wi URL", "Type" => "text", "Size" => "30", "Description" => "", "Default" => "https://yourdomain.tld/easy-wi/");
    $configarray["fields"]["user"] = array ("FriendlyName" => "API user", "Type" => "text", "Size" => "30", "Description" => "", "Default" => "apiUser");
    $configarray["fields"]["password"] = array ("FriendlyName" => "API password", "Type" => "password", "Size" => "30", "Description" => "");
    $configarray["fields"]["timeout"] = array ("FriendlyName" => "API call timeout", "Type" => "text", "Size" => "30", "Description" => "", "Default" => "10");
    $configarray["fields"]["idPrefix"] = array ("FriendlyName" => "External ID prefix", "Type" => "text", "Size" => "30", "Description" => "Required to ensure ID uniqueness at Easy-WI.", "Default" => "whmcs");
    $configarray["fields"]["syncUsers"] = array ("FriendlyName" => "Sync users", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Set to no if only Easy-Wi relevant users should be synced.", "Default" => "No");
    $configarray["fields"]["removeUsers"] = array ("FriendlyName" => "Remove users", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "If \"Sync users\" is set to no and no product at Easy-Wi is left, user will be removed.", "Default" => "No");
    $configarray["fields"]["mapByEmail"] = array ("FriendlyName" => "Email mapping", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "If user cannot be created map to existing Easy-Wi user account by e-mail.", "Default" => "No");
    $configarray["fields"]["syncPasswords"] = array ("FriendlyName" => "Sync passwords", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Set to yes to change the Easy-Wi password as well in case the WHMCS password is changed.", "Default" => "No");
    $configarray["fields"]["useHooks"] = array ("FriendlyName" => "Use WHMCS hooks", "Type" => "dropdown", "Options" => "Yes,No", "Description" => "By default product provisioning does not act on form change. Set to yes if saving a form should trigger replication to Easy-Wi.", "Default" => "Yes");
    $configarray["fields"]["logAPIResponses"] = array ("FriendlyName" => "Log API responses", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Store the API responses at the tblactivitylog table.", "Default" => "No");
    $configarray["fields"]["statePending"] = array ("FriendlyName" => "Action for pending state", "Type" => "dropdown", "Options" => "Delete,Inactive", "Description" => "Remove from Easy-Wi or set product to inactive when order is set to pending. Not relevant for initial pending state.", "Default" => "Delete");
    $configarray["fields"]["stateFraud"] = array ("FriendlyName" => "Action for fraud state", "Type" => "dropdown", "Options" => "Delete,Inactive", "Description" => "Remove from Easy-Wi or set product to inactive when order is set to fraud.", "Default" => "Delete");

    $serverUrl = $_SERVER['SERVER_NAME'];

    $configarray["fields"]["lendModule"] = array ("FriendlyName" => "Activate lending module", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Allows you to lend servers at the URL <a href='{$serverUrl}/lendserver.php' target='_blank'>{$_SERVER['SERVER_NAME']}/lendserver.php</a>", "Default" => "No");
    $configarray["fields"]["lendTitle"] = array ("FriendlyName" => "Lendpage title", "Type" => "text", "Size" => "30", "Description" => "Here you can configure the page title for the lending page.", "Default" => "Lendserver");
    $configarray["fields"]["protectionModule"] = array ("FriendlyName" => "Activate protection check module", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "Allows users to check the protection mode of a server at the URL <a href='{$serverUrl}/protectioncheck.php' target='_blank'>{$_SERVER['SERVER_NAME']}/protectioncheck.php</a>", "Default" => "No");
    $configarray["fields"]["protectionTitle"] = array ("FriendlyName" => "Protection title", "Type" => "text", "Size" => "30", "Description" => "Here you can configure the page title for the lending page.", "Default" => "Protection check");

    return $configarray;
}

function easywi_activate() {

    $successArray = array();

    $query = "CREATE TABLE IF NOT EXISTS `mod_easywi_user_synced` (`user_id` int(10) unsigned NOT NULL,`synced` enum('Y','N') NOT NULL DEFAULT 'N', PRIMARY KEY (`user_id`)) ENGINE=InnoDB";
    full_query($query);

    $successArray[] = "Created table mod_easywi_service_synced";

    $query = "CREATE TABLE IF NOT EXISTS `mod_easywi_service_synced` (`user_id` int(10) unsigned NOT NULL,`service_id` int(10) unsigned NOT NULL,`synced` enum('Y','N') NOT NULL DEFAULT 'N', PRIMARY KEY (`user_id`,`service_id`)) ENGINE=InnoDB";
    full_query($query);

    $query = "CREATE TABLE IF NOT EXISTS `mod_easywi_options_name_alias` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`technical_name` varchar(255) NOT NULL,`alias` varchar(255) NOT NULL, PRIMARY KEY (`id`), UNIQUE (`alias`)) ENGINE=InnoDB";
    full_query($query);

    $successArray[] = "Created table mod_easywi_options_name_alias";

    $query = "CREATE TABLE IF NOT EXISTS `mod_easywi_value_name_alias` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `option_alias_id` int(10) unsigned NOT NULL ,`technical_name` varchar(255) NOT NULL,`alias` varchar(255) NOT NULL, PRIMARY KEY (`id`), UNIQUE (`alias`)) ENGINE=InnoDB";
    full_query($query);

    $successArray[] = "Created table mod_easywi_options_name_mapping";

    $query = "CREATE TABLE IF NOT EXISTS `mod_easywi_protection_mode_cache` (`address` varchar(21) NOT NULL,`response` blob,`checked_at` timestamp DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`address`)) ENGINE=InnoDB";
    full_query($query);

    $successArray[] = "Created table mod_easywi_protection_mode_cache";

    $query = "CREATE TABLE IF NOT EXISTS `mod_easywi_lendserver_cache` (`address` varchar(21) NOT NULL,`type` enum('G','V'),`response` blob,`checked_at` timestamp DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`address`,`type`)) ENGINE=InnoDB";
    full_query($query);

    $query = "CREATE TABLE IF NOT EXISTS `mod_easywi_lendserver_cookies` (`cookie` varchar(40) NOT NULL,`gs` enum('Y','N') DEFAULT 'N',`vo` enum('Y','N') DEFAULT 'N,`last_changed` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY (`cookie`)) ENGINE=InnoDB";
    full_query($query);

    $successArray[] = "Created table mod_easywi_lendserver_cache";

    # Return Result
    return array(
        "status" => "info",
        "description" => "Easy-Wi.com Addon successfully installed: " . implode(", ", $successArray)
    );

}

function easywi_deactivate() {

    # Remove Custom DB Tables
    $query = "DROP TABLE IF EXISTS `mod_easywi_user_synced`";
    full_query($query);

    $query = "DROP TABLE IF EXISTS `mod_easywi_order_synced`";
    full_query($query);

    $query = "DROP TABLE IF EXISTS `mod_easywi_options_name_alias`";
    full_query($query);

    $query = "DROP TABLE IF EXISTS `mod_easywi_value_name_alias`";
    full_query($query);

    $query = "DROP TABLE IF EXISTS `mod_easywi_protection_mode_cache`";
    full_query($query);

    $query = "DROP TABLE IF EXISTS `mod_easywi_lendserver_cache`";
    full_query($query);

    $query = "DROP TABLE IF EXISTS `mod_easywi_lendserver_cookies`";
    full_query($query);

    # Return Result
    return array(
        "status" => "success",
        "description" => "Removed tables: mod_easywi_user_synced, mod_easywi_order_synced and mod_easywi_newest_version"
    );
}

function easywi_upgrade ($vars) {

    $version = $vars['version'];

    # Run SQL Updates for V1.1 to V1.2
    if ($version < 1.2) {
        $query = "CREATE TABLE IF NOT EXISTS `mod_easywi_value_name_alias` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `option_alias_id` int(10) unsigned NOT NULL ,`technical_name` varchar(255) NOT NULL,`alias` varchar(255) NOT NULL, PRIMARY KEY (`id`), UNIQUE (`option_alias_id`,`alias`)) ENGINE=InnoDB";
        $result = mysql_query($query);
    }

    # Run SQL Updates for V1.5 to V1.6
    if ($version < 1.6) {
        $query = "ALTER TABLE `mod_easywi_value_name_alias` DROP INDEX `alias`";
        $result = mysql_query($query);

        $query = "ALTER TABLE `mod_easywi_value_name_alias` ADD UNIQUE (`option_alias_id`,`alias`)";
        $result = mysql_query($query);
    }

    # Run SQL Updates for V1.5 to V1.6
    if ($version < 1.8) {
        $query = "DROP TABLE IF EXISTS `mod_easywi_newest_version`";
        $result = mysql_query($query);
    }
}

function easywi_output($vars) {

    // Create Easy-Wi object, so we can see config options
    $easyWiObject = new EasyWi();

    if ($easyWiObject->removeUsers == "Yes" && isset($_GET["method"]) && ($_GET["method"] == "userlocal" || $_GET["method"] == "usereasywi")) {
        echo "<div  style='margin:0;padding:10px;background-color:#FBEEEB;border:1px dashed #cc0000;font-weight: bold;color: #cc0000;font-size:14px;text-align: center;'>";
        echo $vars['_lang']['syncNoPointExternal'];
        echo "</div>";
    }

    // Help text and navigation buttons
    echo "<div  style='margin:10px;padding:10px;background-color:#D9EDF7;border:1px #BCE8F1;font-weight: bold;color: #3A87AD;font-size:14px;text-align: center;'>";

    if (isset($_GET["method"])) {

        if ($_GET["method"] == "masterlist") {
            echo $vars['_lang']['helpMasterlist'];
        } else if ($_GET["method"] == "userlocal") {
            echo $vars['_lang']['helpUserLocal'];
        } else if ($_GET["method"] == "usereasywi") {
            echo $vars['_lang']['helpUserEasyWi'];
        } else if ($_GET["method"] == "alias") {
            echo $vars['_lang']['helpAliases'];
        } else if ($_GET["method"] == "valueAlias") {
            echo $vars['_lang']['helpValueAliases'];
        }

    } else {
        echo $vars['_lang']['intro'];
    }
    echo "</div>";

    echo "<div>";
    echo "{$vars['_lang']['show']}: ";
    echo "<a href='{$vars['modulelink']}&amp;method=userlocal'><button type='button' class='btn'>{$vars['_lang']['userlocal']}</button></a> ";
    echo "<a href='{$vars['modulelink']}&amp;method=usereasywi'><button type='button' class='btn'>{$vars['_lang']['usereasywi']}</button></a> ";
    echo "<a href='{$vars['modulelink']}&amp;method=masterlist'><button type='button' class='btn'>{$vars['_lang']['masterlist']}</button></a> ";
    echo "<a href='{$vars['modulelink']}&amp;method=alias'><button type='button' class='btn'>{$vars['_lang']['alias']}</button></a> ";
    echo "<a href='{$vars['modulelink']}&amp;method=valueAlias'><button type='button' class='btn'>{$vars['_lang']['valueAlias']}</button></a> ";//TODO
    echo "</div>";

    if (isset($_GET["method"]) && $_GET["method"] == "masterlist") {

        // Check which listing is needed
        $show = array(
            'gameServer' => true,
            'mysqlServer' => true,
            'tsdnsServer' => true,
            'voiceServer' => true,
            'webspaceServer' => true
        );

        if (isset($_GET["show"]) && is_array($_GET["show"])) {
            foreach ($show as $k => $v) {
                $show[$k] = (in_array($k, $_GET["show"])) ? true : false;
            }
        }

        // Retrieve masterdata from Easy-Wi installation
        $details = $easyWiObject->getMasterList($show);

        echo "<hr>";
        echo "<div>";

        // Type selector
        echo "<form method='get' action='{$vars['modulelink']}'>";

        echo "<input type='hidden' name='module' value='easywi'>";
        echo "<input type='hidden' name='method' value='masterlist'>";

        echo "{$vars['_lang']['list']}: ";

        $checked = ($show["gameServer"]) ? 'checked' : '';
        echo "{$vars['_lang']['games']} <input type='checkbox' name='show[]' value='gameServer' onChange='this.form.submit()' $checked>, ";

        $checked = ($show["mysqlServer"]) ? 'checked' : '';
        echo "MySQL <input type='checkbox' name='show[]' value='mysqlServer' onChange='this.form.submit()' $checked>, ";

        $checked = ($show["tsdnsServer"]) ? 'checked' : '';
        echo "TSDNS <input type='checkbox' name='show[]' value='tsdnsServer' onChange='this.form.submit()' $checked>, ";

        $checked = ($show["voiceServer"]) ? 'checked' : '';
        echo "Voice <input type='checkbox' name='show[]' value='voiceServer' onChange='this.form.submit()' $checked>, ";

        $checked = ($show["webspaceServer"]) ? 'checked' : '';
        echo "Webspace <input type='checkbox' name='show[]' value='webspaceServer' onChange='this.form.submit()' $checked>";
        echo "</form>";

        echo "</div>";

        // Gameserver related information
        if ($show["gameServer"]) {

            echo "<hr>";
            echo "<div>";
            echo "<h2>{$vars['_lang']['games']}</h2>";

            echo "<div>";

            $count = (isset($details['games'])) ? count($details['games']) : 0;

            if ($count > 0) {

                echo "<p>{$vars['_lang']['gameInstalled']}";

                $i = 0;

                foreach ($details['games'] as $k => $v) {

                    echo ($i == 0) ? "$v ($k)" : ", $v ($k)";

                    $i++;
                }

                echo "</p>";


            } else {
                echo $vars['_lang']['gamesNotInstalled'];
            }

            echo "</div>";


            echo "<div class='addMargin'>";

            // Gameserver masterserver list
            $count = (isset($details['gameServer'])) ? count($details['gameServer']) : 0;

            if ($count > 0) {

                echo "<h3>{$vars['_lang']['masterlist']}</h3>";
                echo "<div class='tablebg'>";

                echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
                echo "<thead><tr><th>{$vars['_lang']['ip']}</th><th>{$vars['_lang']['id']}</th><th>{$vars['_lang']['maxserver']}</th><th>{$vars['_lang']['installedserver']}</th><th>{$vars['_lang']['maxslots']}</th><th>{$vars['_lang']['installedslots']}</th><th>{$vars['_lang']['description']}</th></tr></thead>";
                echo "<tbody>";

                foreach ($details['gameServer'] as $server) {

                    echo "<tr>";
                    echo "<td>{$server['ip']}</td>";
                    echo "<td>{$server['id']}</td>";
                    echo "<td>{$server['maxserver']}</td>";
                    echo "<td>{$server['installedserver']}</td>";
                    echo "<td>{$server['maxslots']}</td>";
                    echo "<td>{$server['installedslots']}</td>";
                    echo "<td>{$server['description']}</td>";

                    echo "</tr>";
                }

                echo "</tbody></table>";

                echo "</div>";

            } else {
                echo $vars['_lang']['gamesNotInstalledMaster'];
            }

            echo "</div>";
        }

        if ($show["mysqlServer"]) {

            echo "<hr>";
            echo "<div>";
            echo "<h2>MySQL</h2>";

            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
            echo "<thead><tr><th>{$vars['_lang']['ip']}</th><th>{$vars['_lang']['id']}</th><th>{$vars['_lang']['maxDBs']}</th><th>{$vars['_lang']['dbsInstalled']}</th><th>{$vars['_lang']['description']}</th></tr></thead>";
            echo "<tbody>";

            foreach ($details['mysqlServer'] as $server) {

                echo "<tr>";
                echo "<td>{$server['ip']}</td>";
                echo "<td>{$server['id']}</td>";
                echo "<td>{$server['maxDBs']}</td>";
                echo "<td>{$server['dbsInstalled']}</td>";
                echo "<td>{$server['description']}</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";

            echo "</div>";
        }

        if ($show["tsdnsServer"]) {

            echo "<hr>";
            echo "<div>";
            echo "<h2>TSDNS</h2>";

            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
            echo "<thead><tr><th>{$vars['_lang']['ip']}</th><th>{$vars['_lang']['id']}</th><th>{$vars['_lang']['maxDNS']}</th><th>{$vars['_lang']['installedDNS']}</th><th>{$vars['_lang']['description']}</th></tr></thead>";
            echo "<tbody>";


            foreach ($details['tsdnsServer'] as $server) {

                echo "<tr>";
                echo "<td>{$server['ip']}</td>";
                echo "<td>{$server['id']}</td>";
                echo "<td>{$server['maxDNS']}</td>";
                echo "<td>{$server['installedDNS']}</td>";
                echo "<td>{$server['description']}</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";

            echo "</div>";

        }

        if ($show["voiceServer"]) {

            echo "<hr>";
            echo "<div>";
            echo "<h2>{$vars['_lang']['voiceServer']}</h2>";

            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
            echo "<thead><tr><th>{$vars['_lang']['ip']}</th><th>{$vars['_lang']['id']}</th><th>{$vars['_lang']['maxserver']}</th><th>{$vars['_lang']['installedserver']}</th><th>{$vars['_lang']['maxslots']}</th><th>{$vars['_lang']['installedslots']}</th><th>{$vars['_lang']['description']}</th></tr></thead>";
            echo "<tbody>";

            foreach ($details['voiceServer'] as $server) {

                echo "<tr>";
                echo "<td>{$server['ip']}</td>";
                echo "<td>{$server['id']}</td>";
                echo "<td>{$server['maxserver']}</td>";
                echo "<td>{$server['installedserver']}</td>";
                echo "<td>{$server['maxslots']}</td>";
                echo "<td>{$server['installedslots']}</td>";
                echo "<td>{$server['description']}</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";

            echo "</div>";

        }

        if ($show["webspaceServer"]) {

            echo "<hr>";
            echo "<div>";
            echo "<h2>{$vars['_lang']['webMaster']}</h2>";

            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
            echo "<thead><tr><th>{$vars['_lang']['ip']}</th><th>{$vars['_lang']['id']}</th><th>{$vars['_lang']['maxVhost']}</th><th>{$vars['_lang']['installedVhosts']}</th><th>{$vars['_lang']['maxHDD']}</th><th>{$vars['_lang']['hddUsage']}</th><th>{$vars['_lang']['description']}</th></tr></thead>";
            echo "<tbody>";

            foreach ($details['webspaceServer'] as $server) {

                echo "<tr>";
                echo "<td>{$server['ip']}</td>";
                echo "<td>{$server['id']}</td>";
                echo "<td>{$server['maxVhost']}</td>";
                echo "<td>{$server['installedVhosts']}</td>";
                echo "<td>{$server['maxHDD']}</td>";
                echo "<td>{$server['hddUsage']}</td>";
                echo "<td>{$server['description']}</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";

            echo "</div>";

        }
        echo "</div>";

    } else if (isset($_GET["method"]) && ($_GET["method"] == "userlocal" || $_GET["method"] == "usereasywi")) {

        // Check which listing is needed
        $show = array(
            'synced' => true,
            'notSynced' => true
        );

        if (isset($_GET["show"]) && is_array($_GET["show"])) {
            foreach ($show as $k => $v) {
                $show[$k] = (in_array($k, $_GET["show"])) ? true : false;
            }
        }

        // Check amount of listing
        $showAmount = 20;

        if (isset($_GET["amount"])) {

            $showAmountTemp = (int) $_GET["amount"];

            if ($showAmountTemp > 0) {
                $showAmount = $showAmountTemp;
            }
        }

        // Check amount of listing
        $showStart = 0;

        if (isset($_GET["start"])) {
            $showStart = (int) $_GET["start"];
        }

        echo "<hr>";

        echo "<div>";

        // Type selector
        echo "<form method='get' action='{$vars['modulelink']}'>";

        echo "<input type='hidden' name='module' value='easywi'>";

        if ($_GET["method"] == "usereasywi") {
            echo "<input type='hidden' name='method' value='usereasywi'>";
        } else {
            echo "<input type='hidden' name='method' value='userlocal'>";
        }

        echo "{$vars['_lang']['list']}: ";

        $checked = ($show["synced"]) ? 'checked' : '';
        echo "{$vars['_lang']['synced']} <input type='checkbox' name='show[]' value='synced' onChange='this.form.submit()' $checked>, ";

        $checked = ($show["notSynced"]) ? 'checked' : '';
        echo "{$vars['_lang']['notSynced']}  <input type='checkbox' name='show[]' value='notSynced' onChange='this.form.submit()' $checked>";

        echo "</form>";

        echo "</div>";

        echo "<hr>";

        if ($_GET["method"] == "userlocal") {

            if ($show["notSynced"] && $show["synced"]) {
                $where = "";
            } else if ($show["notSynced"]) {
                $where = "WHERE `synced` IS NULL";
            } else if ($show["synced"]) {
                $where = "WHERE `synced`='Y'";
            }

            $query = "SELECT COUNT(1) AS `amount` FROM `tblclients` {$where}";
            $result = full_query($query);
            $data = mysql_fetch_assoc($result);

            // we need to calculate the limts for amount
            $start = $showStart + 1;
            $start = ($start > $data["amount"]) ? $data["amount"] : $start;

            $end = $showStart + $showAmount;
            $end = ($end > $data["amount"]) ? $data["amount"] : $end;

            // Now calculate the pagination

            // Total amount of pages
            $pagesAmount = round($data["amount"] / $showAmount);
            $pagesAmount = ($pagesAmount < 1) ? 1 : $pagesAmount;

            echo "<div>";
            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";

            echo "<tr>";

            echo "<td width='50%' align='left'>" . str_replace(array("%s", "%e", "%t"), array($start, $end, $data["amount"]), $vars["_lang"]["recordsShowing"]) . "</td>";
            echo "<td width='25%' align='right'>";
            echo "<form method='get' action='{$vars['modulelink']}' style='margin:0;padding:0;'>";
            echo "<input type='hidden' name='module' value='easywi'>";
            echo "<input type='hidden' name='method' value='userlocal'>";
            echo "<input type='hidden' name='start' value='{$showStart}'>";
            echo "<select name='amount' onChange='this.form.submit()' <input style='display:inline!important;'>";
            echo ($showAmount == 10) ? "<option selected>10</option>" : "<option>10</option>";
            echo ($showAmount == 20) ? "<option selected>20</option>" : "<option>20</option>";
            echo ($showAmount == 50) ? "<option selected>50</option>" : "<option>50</option>";
            echo "</select>";
            echo " &nbsp;{$vars['_lang']['recordsPage']}";
            echo "</form>";
            echo "</td>";


            echo "<td width='25%' align='right'>";
            echo "<form method='get' action='{$vars['modulelink']}'>";
            echo "<input type='hidden' name='module' value='easywi'>";
            echo "<input type='hidden' name='method' value='userlocal'>";
            echo "<input type='hidden' name='amount' value='{$showAmount}'>";
            echo "<select name='start' onChange='this.form.submit()'>";

            // generate select options
            $i = 0;

            while ($i < $pagesAmount) {

                $start = $i * $showAmount;

                $selected = ($start == $showStart) ? "selected" : "";

                $i++;

                echo "<option value='{$start}' {$selected}>{$i}</option>";
            }

            echo "</select>";
            echo "&nbsp;{$vars['_lang']['recordsJump']}";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            echo "</div>";


            // Start displaying users here
            echo "<div class='tablebg'>";

            echo "<script language='JavaScript'>function toggle(source){checkboxes=document.getElementsByName('userID[]');for(var i=0, n=checkboxes.length;i<n;i++){checkboxes[i].checked=source.checked;}}</script>";

            echo "<form method='post' action='{$vars['modulelink']}&amp;method=userMassSyncWHMCSEasyWi'>";

            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
            echo "<thead><tr><th><input type='checkbox' name='selectAll' value='' onClick='toggle(this);'></th><th>{$vars['_lang']['localID']}</th><th>{$vars['_lang']['active']}</th><th>{$vars['_lang']['lastName']}</th><th>{$vars['_lang']['firstName']}</th><th>{$vars['_lang']['email']}</th><th>{$vars['_lang']['syncStatus']}</th><th>{$vars['_lang']['action']}</th></tr></thead>";
            echo "<tbody>";

            $query = "SELECT `id`,`firstname`,`lastname`,`email`,`status`,`synced` FROM `tblclients` AS c LEFT JOIN `mod_easywi_user_synced` AS s ON s.`user_id`=c.`id` {$where} LIMIT $showStart,$showAmount";
            $result = full_query($query);
            while ($row = mysql_fetch_assoc($result)) {

                $syncStatus = ($row["synced"] == "Y") ? $vars['_lang']['synced'] : $vars['_lang']['notSynced'];

                echo "<tr>";
                echo "<td><input type='checkbox' name='userID[]' value='{$row['id']}'></td>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['status']}</td>";
                echo "<td>{$row['lastname']}</td>";
                echo "<td>{$row['firstname']}</td>";
                echo "<td>{$row['email']}</td>";
                echo "<td>{$syncStatus}</td>";
                echo "<td>";
                echo "<a href='{$vars['modulelink']}&amp;method=userSyncWHMCS&amp;userID={$row['id']}'><button type='button' class='btn btn-small'><img src='images/icons/admins.png' alt='' width='14' height='14' class='absmiddle' /> {$vars['_lang']['syncUser']}</button></a>";
                echo ($row["synced"] == "Y") ? " <a href='{$vars['modulelink']}&amp;method=serverSync&amp;userID={$row['id']}'><button type='button' class='btn btn-small'><img src='images/icons/search.png' alt='' width='14' height='14' class='absmiddle' /> {$vars['_lang']['checkServer']}</button></a>" : "";
                echo "</td>";
                echo "</tr>";
            }

            echo "<tr>";
            echo "<td colspan='9'><button type='submit' class='btn btn-small'>{$vars['_lang']['syncSelected']}</button></td>";
            echo "</tr>";

            echo "</tbody>";
            echo "</table>";

            echo "</form>";

            echo "</div>";

        } else {

            $userList = $easyWiObject->getUserList(array("start" => $showStart, "amount" => $showAmount, "showSynced" => $show["synced"], "showNotSynced" => $show["notSynced"]));

            if (is_array($userList)) {

                echo "<div>";
                echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";

                echo "<tr>";

                // we need to calculate the limts for amount
                $start = $userList["start"] + 1;
                $start = ($start > $userList["totalAmount"]) ? $userList["totalAmount"] : $start;

                $end = $userList["start"] + $userList["amount"];
                $end = ($end > $userList["totalAmount"]) ? $userList["totalAmount"] : $end;

                echo "<td width='50%' align='left'>" . str_replace(array("%s", "%e", "%t"), array($start, $end, $userList["totalAmount"]), $vars["_lang"]["recordsShowing"]) . "</td>";
                echo "<td width='25%' align='right'>";
                echo "<form method='get' action='{$vars['modulelink']}' style='margin:0;padding:0;'>";
                echo "<input type='hidden' name='module' value='easywi'>";
                echo "<input type='hidden' name='method' value='usereasywi'>";
                echo "<input type='hidden' name='start' value='{$showStart}'>";
                echo "<select name='amount' onChange='this.form.submit()' <input style='display:inline!important;'>";
                echo ($showAmount == 10) ? "<option selected>10</option>" : "<option>10</option>";
                echo ($showAmount == 20) ? "<option selected>20</option>" : "<option>20</option>";
                echo ($showAmount == 50) ? "<option selected>50</option>" : "<option>50</option>";
                echo "</select>";
                echo " &nbsp;{$vars['_lang']['recordsPage']}";
                echo "</form>";
                echo "</td>";

                // Now calculate the pagination

                // Total amount of pages
                $pagesAmount = round($userList["totalAmount"] / $showAmount);
                $pagesAmount = ($pagesAmount < 1) ? 1 : $pagesAmount;


                echo "<td width='25%' align='right'>";
                echo "<form method='get' action='{$vars['modulelink']}'>";
                echo "<input type='hidden' name='module' value='easywi'>";
                echo "<input type='hidden' name='method' value='usereasywi'>";
                echo "<input type='hidden' name='amount' value='{$showAmount}'>";
                echo "<select name='start' onChange='this.form.submit()'>";

                // generate select options
                $i = 0;

                while ($i < $pagesAmount) {

                    $start = $i * $showAmount;

                    $selected = ($start == $showStart) ? "selected" : "";

                    $i++;

                    echo "<option value='{$start}' {$selected}>{$i}</option>";
                }

                echo "</select>";
                echo "&nbsp;{$vars['_lang']['recordsJump']}";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
                echo "</table>";
                echo "</div>";


                // Start displaying users here
                echo "<div class='tablebg'>";

                echo "<script language='JavaScript'>function toggle(source){checkboxes=document.getElementsByName('userID[]');for(var i=0, n=checkboxes.length;i<n;i++){checkboxes[i].checked=source.checked;}}</script>";

                echo "<form method='post' action='{$vars['modulelink']}&amp;method=userMassSyncEasyWiWHMCS'>";

                echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
                echo "<thead><tr><th><input type='checkbox' name='selectAll' value='' onClick='toggle(this);'></th><th>{$vars['_lang']['id']}</th><th>{$vars['_lang']['externalID']}</th><th>{$vars['_lang']['localID']}</th><th>{$vars['_lang']['active']}</th><th>{$vars['_lang']['loginName']}</th><th>{$vars['_lang']['email']}</th><th>{$vars['_lang']['syncStatus']}</th><th>{$vars['_lang']['action']}</th></tr></thead>";
                echo "<tbody>";

                if (isset($userList["userList"]) && is_array($userList["userList"])) {

                    $idPrefixLength = strlen($easyWiObject->idPrefix);

                    foreach ($userList["userList"] as $user) {

                        // Write into $_SESSION to keep the post vars to a minimum and avoid the need to do more http(s) requests to Easy-Wi
                        $_SESSION["easyWiToWHMCSSync"]["user"][$user['id']] = $user;

                        $syncStatus = $vars['_lang']['notSynced'];

                        $whmcsUserID = false;

                        if (substr($user["externalID"], 0 , $idPrefixLength) == $easyWiObject->idPrefix) {

                            @list($prefix, $whmcsUserID) = explode(":", $user["externalID"]);

                            $syncFlag = $easyWiObject->isUserSynced($whmcsUserID);

                            if ($syncFlag == "Y" && $easyWiObject->localClientDetails($whmcsUserID) !== false) {

                                $syncStatus = $vars['_lang']['synced'];

                            } else {
                                $syncStatus = ($syncFlag == "Y") ? $vars['_lang']['notSyncedLocalMissing'] : $vars['_lang']['notSyncedLocal'];
                            }

                        }

                        echo "<tr>";
                        echo ($syncStatus == $vars['_lang']['synced']) ? "<td></td>" : "<td><input type='checkbox' name='userID[]' value='{$user['id']}'></td>";
                        echo "<td>{$user['id']}</td>";
                        echo ($syncStatus == $vars['_lang']['synced']) ?  "<td>{$user['externalID']}</td>" : "<td>{$user['externalID']}</td>";
                        echo ($syncStatus == $vars['_lang']['synced']) ? "<td>{$whmcsUserID}</td>" : "<td></td>";
                        echo "<td>{$vars['_lang'][$user['active']]}</td>";
                        echo "<td>{$user['cname']}</td>";
                        echo "<td>{$user['mail']}</td>";
                        echo "<td>{$syncStatus}</td>";
                        echo ($syncStatus == $vars['_lang']['synced']) ? "<td><a href='{$vars['modulelink']}&amp;method=serverSync&amp;userID={$whmcsUserID}'><button type='button' class='btn btn-small'><img src='images/icons/search.png' alt='' width='14' height='14' class='absmiddle' /> {$vars['_lang']['checkServer']}</button></a></td>" : "<td><a href='{$vars['modulelink']}&amp;method=userSync&amp;easywiID={$user['id']}&amp;userID={$whmcsUserID}'><button type='button' class='btn btn-small'><img src='images/icons/admins.png' alt='' width='14' height='14' class='absmiddle' /> {$vars['_lang']['syncUser']}</button></a></td>";
                        echo "</tr>";
                    }
                }

                echo "<tr>";
                echo "<td colspan='8'><button type='submit' class='btn btn-small'>{$vars['_lang']['syncSelected']}</button></td>";
                echo "</tr>";
                echo "</tbody>";
                echo "</table>";
                echo "</form>";
                echo "</div>";

            } else {
                echo "<div>{$userList}</div>";
            }
        }

    } else if (isset($_GET["method"]) && ($_GET["method"] == "userMassSyncWHMCSEasyWi" || $_GET["method"] == "userSyncWHMCS")) {

        echo "<hr>";

        if (($_GET["method"] == "userMassSyncWHMCSEasyWi" && isset($_POST["userID"]) && is_array($_POST["userID"])) || ($_GET["method"] == "userSyncWHMCS" && isset($_GET["userID"])) && is_numeric($_GET["userID"])) {

            $idList = ($_GET["method"] == "userMassSyncWHMCSEasyWi" && isset($_POST["userID"]) && is_array($_POST["userID"])) ? $_POST["userID"] : array($_GET["userID"]);

            // Start displaying users here
            echo "<div class='tablebg'>";

            echo "<form method='post' action='{$vars['modulelink']}&amp;method=userMassSyncWHMCSEasyWi'>";

            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
            echo "<thead><tr><th>{$vars['_lang']['localID']}</th><th>{$vars['_lang']['email']}</th><th>{$vars['_lang']['lastName']}</th><th>{$vars['_lang']['firstName']}</th><th>{$vars['_lang']['action']}</th></tr></thead>";
            echo "<tbody>";

            foreach ($idList as $id) {

                // Attack prevention
                $id = (int) $id;

                $query = "SELECT `firstname`,`lastname`,`email`,`synced` FROM `tblclients` AS c LEFT JOIN `mod_easywi_user_synced` AS s ON s.`user_id`=c.`id` WHERE `id`={$id} LIMIT 1";
                $result = full_query($query);
                while ($row = mysql_fetch_assoc($result)) {

                    echo "<tr>";
                    echo "<td>{$id}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>{$row['lastname']}</td>";
                    echo "<td>{$row['firstname']}</td>";

                    // Options
                    // Show best matches in case the user is not mapped yet

                    echo "<td>";
                    echo "<select name='internalID[{$id}]'>";
                    echo "<option value='doNothing'>{$vars['_lang']['doNothing']}</option>";

                    if ($row['synced'] == "Y") {
                        echo "<option value='resend' selected>{$vars['_lang']['resend']}</option>";
                    } else {

                        $selectedSet = false;
                        $emailFound = false;
                        $WHMCSIDFound = false;

                        $EasyWiUsers = $easyWiObject->getUserDetails($id, $row['email']);

                        foreach ($EasyWiUsers as $user) {

                            if (isset($user["id"]) && is_numeric($user["id"])) {

                                if (trim($id) == trim($user['whmcsID'])) {
                                    $selectedSet = true;
                                    $WHMCSIDFound = true;
                                    $selected = " selected";
                                } else if (trim($row['email']) == trim($user['email'])) {
                                    $selectedSet = true;
                                    $emailFound = true;
                                    $selected = " selected";
                                } else {
                                    $selected = "";
                                }

                                echo "<option value='map:{$id}:{$user['id']}:{$user['whmcsID']}'{$selected}>{$vars['_lang']['syncUser']}: {$user['lastname']} {$user['firstname']}, {$user['email']} ({$user['id']})</option>";
                            }
                        }

                        if ($emailFound == false && $WHMCSIDFound == false) {
                            $selected = ($selectedSet == false) ? " selected" : "";
                            echo "<option value='create'{$selected}>{$vars['_lang']['createNewUser']}</option>";
                        }
                    }

                    echo "</select>";
                    echo "</td>";

                    echo "</tr>";
                }
            }

            echo "</tbody>";
            echo "<tfoot><tr><td colspan='5'><button type='submit' class='btn btn-small'>{$vars['_lang']['syncSelected']}</button></td></tr></tfoot>";
            echo "</table>";
            echo "</form>";
            echo "</div>";

        } else if (isset($_POST["internalID"]) && is_array($_POST["internalID"])) {

            echo "<div class='tablebg'>";

            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
            echo "<thead><tr><th>{$vars['_lang']['localID']}</th><th>{$vars['_lang']['email']}</th><th>{$vars['_lang']['lastName']}</th><th>{$vars['_lang']['firstName']}</th><th>{$vars['_lang']['action']}</th></tr></thead>";
            echo "<tbody>";

            foreach ($_POST["internalID"] as $whmcsID => $action) {

                $whmcsID = (int) $whmcsID;

                @list($action, $whmcsUserID, $EasyWiUserID, $EasyWiStoredWHMCSID) = preg_split('/:/', $action, -1, PREG_SPLIT_NO_EMPTY);

                if ($action != "doNothing") {

                    $syncSuccess = false;

                    // if user with externalID exist, not mapped local and mod/create, we need to empty Easy-Wi user first
                    if ($action == "create" || ($action == "map" && $whmcsUserID != $EasyWiStoredWHMCSID)) {
                        $easyWiObject->cleanExternUserID($whmcsUserID);
                    }

                    if ($action == "create" || $action == "resend") {
                        $syncSuccess = $easyWiObject->addUser(array("userid" => $whmcsID), true, true);
                    } else if ($action == "map") {
                        $syncSuccess = $easyWiObject->modUser(array("userid" => $whmcsID), true, $EasyWiUserID);
                    }

                    $easyWiObject->addUserSyncEntry($whmcsID, ($syncSuccess) ? "Y" : "N");

                    $query = "SELECT `firstname`,`lastname`,`email` FROM `tblclients` WHERE `id`={$whmcsID} LIMIT 1";
                    $result = full_query($query);

                    while ($row = mysql_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>{$whmcsID}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['lastname']}</td>";
                        echo "<td>{$row['firstname']}</td>";
                        echo ($syncSuccess) ? "<td>{$vars['_lang']['syncSuccess']}</td>" : "<td></td>";
                        echo "</tr>";
                    }
                }
            }

            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        }

    } else if (isset($_GET["method"]) && ($_GET["method"] == "userMassSyncEasyWiWHMCS" || $_GET["method"] == "userSync")) {

        $idPrefixLength = strlen($easyWiObject->idPrefix);

        echo "<hr>";

        if (($_GET["method"] == "userMassSyncEasyWiWHMCS" && isset($_POST["userID"]) && is_array($_POST["userID"])) || ($_GET["method"] == "userSync" && isset($_GET["easywiID"])) && is_numeric($_GET["easywiID"])) {

            $idList = ($_GET["method"] == "userMassSyncEasyWiWHMCS" && isset($_POST["userID"]) && is_array($_POST["userID"])) ? $_POST["userID"] : array($_GET["easywiID"]);

            // Start displaying users here
            echo "<div class='tablebg'>";

            echo "<form method='post' action='{$vars['modulelink']}&amp;method=userMassSyncEasyWiWHMCS'>";

            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
            echo "<thead><tr><th>{$vars['_lang']['id']}</th><th>{$vars['_lang']['externalID']}</th><th>{$vars['_lang']['email']}</th><th>{$vars['_lang']['action']}</th><th>{$vars['_lang']['syncUserBestMatch']}</th></tr></thead>";
            echo "<tbody>";

            foreach ($idList as $id) {

                // Attack prevention
                $id = (int) $id;

                if (isset($_SESSION["easyWiToWHMCSSync"]["user"][$id]["externalID"]) && isset($_SESSION["easyWiToWHMCSSync"]["user"][$id]["mail"])) {

                    // we need to filter in order to prevent any kind of attack
                    $externalID = (is_numeric($_SESSION["easyWiToWHMCSSync"]["user"][$id]["externalID"])) ? $_SESSION["easyWiToWHMCSSync"]["user"][$id]["externalID"] : '';
                    $email = (filter_var($_SESSION["easyWiToWHMCSSync"]["user"][$id]["mail"], FILTER_VALIDATE_EMAIL)) ? $_SESSION["easyWiToWHMCSSync"]["user"][$id]["mail"] : '';

                    echo "<tr>";
                    echo "<td>{$id}</td>";
                    echo "<td>{$externalID}</td>";
                    echo "<td>{$email}</td>";

                    echo "<td>";
                    echo "<select name='internalID[{$id}]'>";
                    echo "<option value='doNothing'>{$vars['_lang']['doNothing']}</option>";

                    $localUserDetails = array();

                    if (substr($externalID, 0 , $idPrefixLength) == $easyWiObject->idPrefix) {

                        @list($prefix, $whmcsUserID) = explode(":", $externalID);

                        $localUserDetailsTemp = $easyWiObject->localClientDetails($whmcsUserID);

                        if ($localUserDetailsTemp !== false) {
                            $localUserDetails[$localUserDetailsTemp["id"]] = $localUserDetailsTemp;
                        }
                    }

                    foreach ($easyWiObject->searchLocalClientByMail($email) as $userData) {
                        $localUserDetails[$userData["id"]] = $userData;
                    }

                    $preselected = false;
                    $key = key($localUserDetails);

                    foreach($localUserDetails as $userData) {
                        if ($key == $userData['id']) {
                            $preselected = true;
                            $selected = "selected";
                        } else {
                            $selected = "";
                        }

                        echo "<option value='{$userData['id']}' $selected>{$userData['id']}, {$userData['email']}, {$userData['firstname']} {$userData['lastname']}</option>";
                    }

                    $selected = (!$preselected) ? "selected" : "";

                    echo "<option value='create' $selected>{$vars['_lang']['createNewUser']}</option>";

                    echo "</select>";
                    echo "</td>";

                    echo (count($localUserDetails) > 0) ? "<td>{$localUserDetails[$key]['id']}, {$localUserDetails[$key]['email']}, {$localUserDetails[$key]['firstname']} {$localUserDetails[$key]['lastname']}</td>" : "<td></td>";

                    echo "</tr>";
                }
            }

            echo "</tbody>";

            echo "<tfoot><tr><td colspan='6'><button type='submit' class='btn btn-small'>{$vars['_lang']['syncSelected']}</button></td></tr></tfoot>";

            echo "</table>";

            echo "</form>";

            echo "</div>";

        } else if (isset($_POST["internalID"]) && is_array($_POST["internalID"])) {

            echo "<div class='tablebg'>";

            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
            echo "<thead><tr><th>{$vars['_lang']['id']}</th><th>{$vars['_lang']['localID']}</th><th>{$vars['_lang']['email']}</th><th>{$vars['_lang']['action']}</th></tr></thead>";
            echo "<tbody>";

            foreach ($_POST["internalID"] as $easyWiID => $action) {

                $easyWiID = (int) $easyWiID;

                if (isset($_SESSION["easyWiToWHMCSSync"]["user"][$easyWiID]) && is_array($_SESSION["easyWiToWHMCSSync"]["user"][$easyWiID])) {

                    $syncSuccess = false;

                    if ($action == "create") {
                        $userID = $easyWiObject->addLocalUser($_SESSION["easyWiToWHMCSSync"]["user"][$easyWiID]);
                    } else {
                        $userID = ($easyWiObject->localClientDetails((int) $action) !== false) ? (int) $action : 0;
                    }

                    if (is_numeric($userID) && $userID > 0) {
                        $syncSuccess = $easyWiObject->addUser(array("userid" => $userID), true);
                    }

                    echo "<tr>";

                    if ($syncSuccess) {
                        echo "<td>{$easyWiID}</td><td>{$userID}</td><td>{$_SESSION['easyWiToWHMCSSync']['user'][$easyWiID]['mail']}</td><td>{$vars['_lang']['syncSuccess']}</td>";
                    } else {
                        echo "<td>{$easyWiID}</td><td></td><td>{$_SESSION['easyWiToWHMCSSync']['user'][$easyWiID]['mail']}</td><td>{$vars['_lang']['syncFailed']}</td>";
                    }

                    echo "<tr>";

                    unset($_SESSION["easyWiToWHMCSSync"]["user"][$easyWiID]);
                }
            }

            echo "</tbody>";
            echo "</table>";

            echo "</div>";
        }

    } else if (isset($_GET["method"]) && $_GET["method"] == "serverSync") {

        echo "<hr>";

        $whmcsUserID = (isset($_GET["userID"])) ? (int) $_GET["userID"] : 0;

        if ($whmcsUserID > 0 && $easyWiObject->isUserSynced($whmcsUserID) == "Y") {

            // check if internalID is an array. If not looks like an attack.
            if (isset($_POST["internalID"]) && is_array($_POST["internalID"])) {

                echo "<div class='tablebg'>";

                echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
                echo "<thead><tr><th>{$vars['_lang']['id']}</th><th>{$vars['_lang']['localID']}</th><th>{$vars['_lang']['easyWiDetails']}</th><th>{$vars['_lang']['action']}</th></tr></thead>";
                echo "<tbody>";

                foreach ($_POST["internalID"] as $type => $services) {

                    // check if we have an array and type is allowed
                    if (in_array($type, array('voice', 'tsdns', 'gserver', 'mysql', 'webspace')) && is_array($services)) {

                        foreach ($services as $easyWiID => $service) {

                            $syncSuccess = false;

                            $easyWiID = (int) $easyWiID;
                            @list($action, $whmcsID) = explode(":", $service);

                            $whmcsID = (int) $whmcsID;

                            if ($action == "product" || $action == "service") {
                                echo "<tr>";
                                echo "<td>{$easyWiID}</td>";
                            }

                            // Product means create new order and depencies
                            if ($action == "product") {

                                // At this point we could use the internal API. In service to prevent multiple API calls, we do it by hand
                                // Allowed Easy-Wi WHMCS addon options are "Game server,Voice server,TSDNS,Webspace,MySQL"
                                // The productID is here already put into $whmcsID and given with the POST request

                                // Check if requests makes sense
                                $table = "tblproducts";
                                $fields = "id";
                                $where = array("id" => $whmcsID);
                                $result = select_query($table, $fields, $where);
                                $data = mysql_fetch_assoc($result);

                                if ($data["id"] == $whmcsID) {

                                    // First we need to generate an ordernum
                                    $mtMaxRand = mt_getrandmax();

                                    $orderNum = mt_rand(1000000000, $mtMaxRand);

                                    while ($easyWiObject->orderNumExists($orderNum)) {
                                        $orderNum = mt_rand(1000000000, $mtMaxRand);
                                    }

                                    // Add new order
                                    $table = "tblorders";
                                    $values = array(
                                        "ordernum" => $orderNum,
                                        "userid" => $whmcsUserID,
                                        "contactid" => 0,
                                        "date" => date("Y-m-d H:i:S"),
                                        "orderdata" => "a:0:{}",
                                        "amount" => "0.00",
                                        "invoiceid" => "0",
                                        "status" => "Active",
                                        "ipaddress" => "127.0.0.1",
                                    );

                                    $newOrderID = insert_query($table, $values);

                                    // Add new hosting entry
                                    $table = "tblhosting";
                                    $values = array(
                                        "userid" => $whmcsUserID,
                                        "orderid" => $newOrderID,
                                        "packageid" => $whmcsID,
                                        "regdate" => date("Y-m-d"),
                                        "domainstatus" => "Active"
                                    );

                                    $newServiceID = insert_query($table, $values);

                                    // We need check for config options and insert accordingly
                                    // tblproductconfiggroups, tblproductconfiglinks, tblproductconfigoptions tblproductconfigoptionssub define, what we can set, tblhostingconfigoptions is the targets

                                    // We need to pick up the alias mapping first in order to be able to insert correct values
                                    $aliases = array();

                                    $table = "mod_easywi_options_name_alias";
                                    $fields = "technical_name,alias";

                                    $result = select_query($table, $fields, array());
                                    while ($row = mysql_fetch_assoc($result)) {
                                        $aliases[strtolower($row["alias"])][] = strtolower($row["technical_name"]);
                                    }

                                    $query = "SELECT o.`optionname`,s.`id` AS `optionid`,s.`configid` FROM `tblproductconfiggroups` AS g INNER JOIN `tblproductconfiglinks` AS l ON l.`gid`=g.`id` INNER JOIN `tblproductconfigoptions` AS o ON o.`gid`=l.`gid` AND o.`qtyminimum`>0 INNER JOIN `tblproductconfigoptionssub` AS s ON s.`configid`=o.`id` WHERE l.`pid`={$whmcsID}";
                                    $result = full_query($query);
                                    while ($row = mysql_fetch_assoc($result)) {

                                        $checkForOption = (isset($aliases[strtolower($row["optionname"])])) ? $aliases[strtolower($row["optionname"])] : strtolower($row["optionname"]);

                                        $qty = (isset($_SESSION['easyWiToWHMCSSync'][$type][$easyWiID][$checkForOption])) ? $_SESSION['easyWiToWHMCSSync'][$type][$easyWiID][$checkForOption] : $row["qtyminimum"];

                                        $table = "tblhostingconfigoptions";
                                        $values = array(
                                            "relid" => $newServiceID,
                                            "configid" => $row["configid"],
                                            "optionid" => $row["optionid"],
                                            "qty" => $qty
                                        );

                                        insert_query($table, $values);
                                    }


                                    $query = "SELECT o.`optionname`,s.`id` AS `optionid`,s.`configid`,s.`optionname` AS `optionvalue` FROM `tblproductconfiggroups` AS g INNER JOIN `tblproductconfiglinks` AS l ON l.`gid`=g.`id` INNER JOIN `tblproductconfigoptions` AS o ON o.`gid`=l.`gid` AND o.`qtyminimum`=0 INNER JOIN `tblproductconfigoptionssub` AS s ON s.`configid`=o.`id` WHERE l.`pid`={$whmcsID}";
                                    $result = full_query($query);
                                    while ($row = mysql_fetch_assoc($result)) {

                                        $checkForOption = (isset($aliases[strtolower($row["optionname"])])) ? $aliases[strtolower($row["optionname"])] : strtolower($row["optionname"]);

                                        if (isset($_SESSION['easyWiToWHMCSSync'][$type][$easyWiID][$checkForOption]) && ($_SESSION['easyWiToWHMCSSync'][$type][$easyWiID][$checkForOption] == $row["optionvalue"] || $easyWiObject->easyWiActiveToWHMCS($_SESSION['easyWiToWHMCSSync'][$type][$easyWiID][$checkForOption]) == $row["optionvalue"])) {

                                            $table = "tblhostingconfigoptions";
                                            $values = array(
                                                "relid" => $newServiceID,
                                                "configid" => $row["configid"],
                                                "optionid" => $row["optionid"],
                                                "qty" => 0
                                            );

                                            insert_query($table, $values);
                                        }
                                    }

                                    // Set sync for service
                                    $syncSuccess = ($easyWiObject->mapWHMCSServiceToEasyWi($newServiceID, $easyWiID)) ? true : false;

                                    echo "<td>{$newServiceID}</td>";
                                }

                                // Service means we need to map to an existing order and service
                            } else if ($action == "service") {

                                // Class methods need to be extended to be able to sync servers with Easy-Wi IDs, so we can map an existing server to a product
                                $syncSuccess = ($easyWiObject->mapWHMCSServiceToEasyWi($whmcsID, $easyWiID)) ? true : false;

                                echo "<td>{$whmcsID}</td>";
                            }

                            if ($action == "product" || $action == "service") {

                                if ($type == "gserver") {

                                    echo "<td>{$_SESSION['easyWiToWHMCSSync'][$type][$easyWiID]['ip']}:{$_SESSION['easyWiToWHMCSSync'][$type][$easyWiID]['port']}</td>";

                                } else if ($type == "voice") {

                                    echo "<td>{$_SESSION['easyWiToWHMCSSync'][$type][$easyWiID]['ip']}:{$_SESSION['easyWiToWHMCSSync'][$type][$easyWiID]['port']} ({$_SESSION['easyWiToWHMCSSync'][$type][$easyWiID]['dns']})</td>";

                                } else if ($type == "tsdns") {

                                    echo "<td>{$_SESSION['easyWiToWHMCSSync'][$type][$easyWiID]['dns']} ({$_SESSION['easyWiToWHMCSSync'][$type][$easyWiID]['ip']}:{$_SESSION['easyWiToWHMCSSync'][$type][$easyWiID]['port']})</td>";

                                } else if ($type == "mysql") {

                                    echo "<td>{$_SESSION['easyWiToWHMCSSync'][$type][$easyWiID]['dbname']}</td>";

                                } else if ($type == "webspace") {

                                    echo "<td>{$_SESSION['easyWiToWHMCSSync'][$type][$easyWiID]['dns']}</td>";

                                } else {
                                    echo "<td></td>";
                                }

                                unset($_SESSION['easyWiToWHMCSSync'][$type][$easyWiID]);

                                echo ($syncSuccess) ? "<td>{$vars['_lang']['syncSuccess']}</td>" : "<td>{$vars['_lang']['syncFailed']}</td>";

                                echo "</tr>";
                            }
                        }
                    }
                }

                echo "</tbody>";
                echo "</table>";
                echo "</div>";

            } else {

                $idPrefixLength = strlen($easyWiObject->idPrefix);

                $server = $easyWiObject->getUserServers($whmcsUserID);

                echo "<div class='tablebg'>";

                echo "<form method='post' action='{$vars['modulelink']}&amp;method=serverSync&amp;userID={$whmcsUserID}'>";

                echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
                echo "<thead><tr><th>{$vars['_lang']['id']}</th><th>{$vars['_lang']['externalID']}</th><th>{$vars['_lang']['type']}</th><th>{$vars['_lang']['active']}</th><th>{$vars['_lang']['easyWiDetails']}</th><th>{$vars['_lang']['syncStatus']}</th><th>{$vars['_lang']['action']}</th></tr></thead>";
                echo "<tbody>";

                if (is_array($server)) {

                    $typeArray = array(
                        "gserver" => "Game server",
                        "voice" => "Voice server",
                        "tsdns" => "TSDNS",
                        "mysql" => "MySQL",
                        "webspace" => "Webspace"
                    );

                    foreach ($server as $type => $entries) {

                        $productList = $easyWiObject->getProductsByType($type);

                        foreach ($entries as $entry) {

                            $_SESSION['easyWiToWHMCSSync'][$type][$entry["id"]] = $entry;

                            $syncStatus = $vars['_lang']['notSynced'];

                            $whmcsServiceID = false;

                            if (substr($entry["externalID"], 0 , $idPrefixLength) == $easyWiObject->idPrefix) {

                                @list($prefix, $whmcsServiceID) = explode(":", $entry["externalID"]);

                                $syncFlag = $easyWiObject->isServiceSynced($whmcsUserID, $whmcsServiceID);

                                if ($syncFlag == "Y" && $easyWiObject->localClientDetails($whmcsUserID) !== false && $easyWiObject->isServiceSynced($whmcsUserID, $whmcsServiceID) !== false) {

                                    $syncStatus = $vars['_lang']['synced'];

                                } else {
                                    $syncStatus = ($syncFlag == "Y") ? $vars['_lang']['notSyncedLocalMissing'] : $vars['_lang']['notSyncedLocal'];
                                }
                            }

                            echo "<tr>";
                            echo "<td>{$entry['id']}</td>";
                            echo ($syncStatus == $vars['_lang']['synced']) ?  "<td>{$entry['externalID']}</td>" : "<td>{$entry['externalID']}</td>";
                            echo "<td>{$typeArray[$type]}</td>";
                            echo "<td>{$vars['_lang'][$entry['active']]}</td>";

                            if ($type == "gserver") {

                                $name = "{$entry['ip']}:{$entry['port']}";

                            } else if ($type == "voice") {

                                $name = "{$entry['ip']}:{$entry['port']} ({$entry['dns']})";

                            } else if ($type == "tsdns") {

                                $name = "{$entry['dns']} ({$entry['ip']}:{$entry['port']})";

                            } else if ($type == "mysql") {

                                $name = $entry['dbname'];

                            } else if ($type == "webspace") {

                                $name = $entry['dns'];

                            } else {
                                $name = "";
                            }

                            echo "<td>{$name}</td>";

                            echo "<td>{$syncStatus}</td>";

                            // In case of not synced product we need to check if a product type is defined in general. If yes we can start searching for fitting products, which are not in sync.
                            if ($syncStatus == $vars['_lang']['synced']) {
                                echo "<td></td>";
                            } else {

                                if (is_array($productList) && count($productList) > 0) {

                                    echo "<td>";

                                    $options = array();

                                    $shortenNotOK = array();

                                    foreach ($productList as $productID => $productDetails) {

                                        $productOK = true;

                                        if ($type == "gserver") {

                                            $shortenNotOKTemp = array();
                                            $externalShortens = preg_split("/,/", $entry['shorten'], -1, PREG_SPLIT_NO_EMPTY);

                                            foreach ($externalShortens as $externalShorten) {
                                                if (!in_array($externalShorten, $productDetails["shorten"])) {
                                                    $shortenNotOKTemp[] = $externalShorten;
                                                }
                                            }

                                            if (count($shortenNotOKTemp) == 0) {

                                                $options[] = "<option value='product:{$productID}'>{$vars['_lang']['createNewOrderFor']}: {$productDetails['name']}</option>";

                                            } else {

                                                $productOK = false;

                                                $shortenNotOK[$productDetails['name']] = implode(", ", $shortenNotOKTemp);
                                            }

                                        } else {
                                            $options[] = "<option value='product:{$productID}'>{$vars['_lang']['createNewOrderFor']}: {$productDetails['name']}</option>";
                                        }

                                        // Now that we know this product is ok for usage, we can search for not synced orders belonging to the user and offer them for sync.
                                        if ($productOK) {
                                            foreach ($easyWiObject->getUserOrdersById($whmcsUserID, $productID) as $orderID => $orderDescription) {

                                                $selected = ($orderID == $whmcsServiceID) ? " selected" : "";

                                                $options[] = "<option value='service:{$orderID}'{$selected}>{$vars['_lang']['mapToOrder']}: {$orderDescription}</option>";
                                            }
                                        }
                                    }

                                    if (count($options) > 0) {
                                        echo "<select name='internalID[{$type}][{$entry['id']}]'>";
                                        echo "<option value='doNothing'>{$vars['_lang']['doNothing']}</option>";
                                        echo implode("", $options);
                                        echo "</select>";

                                    } else {

                                        echo "{$vars['_lang']['productNotExistingShorten']}<br>";

                                        foreach ($shortenNotOK as $k => $v) {
                                            echo "<b>$k</b>: $v<br>";
                                        }
                                    }

                                    echo "</td>";

                                } else {
                                    echo "<td>{$vars['_lang']['productNotExisting']}</td>";
                                }
                            }

                            echo "</tr>";
                        }
                    }
                }

                echo "</tbody>";

                echo "<tfoot><tr><td colspan='7'><button type='submit' class='btn btn-small'>{$vars['_lang']['syncSelectedOrders']}</button></td></tr></tfoot>";

                echo "</table>";
                echo "</div>";

            }

        } else {
            echo "<div  style='margin:0;padding:10px;background-color:#FBEEEB;border:1px dashed #cc0000;font-weight: bold;color: #cc0000;font-size:14px;text-align: center;'>";
            echo $vars['_lang']['syncNoPointUserSync'];
            echo "</div>";
        }

    } else if (isset($_GET["method"]) && $_GET["method"] == "alias") {

        echo "<hr>";

        $technicalNames = array('slots','cpu','ram','hdd','traffic','private','brandname','tvenable','protected','eac','forcebanner','forcebutton','forcewelcome','homelabel','preinstalled');

        if (isset($_GET["action"]) && $_GET["action"] == "add") {

            if (isset($_POST["alias"]) && isset($_POST["technical_name"])) {

                if (!in_array($_POST["technical_name"], $technicalNames)) {
                    $error = $vars['_lang']['errorTechnicalName'];
                } else if (strlen($_POST["alias"]) == 0) {
                    $error = $vars['_lang']['errorName'];
                }

                if (isset($error)) {
                    echo "<div  style='margin:0;padding:10px;background-color:#FBEEEB;border:1px dashed #cc0000;font-weight: bold;color: #cc0000;font-size:14px;text-align: center;'>";
                    echo $error;
                    echo "</div>";
                } else {

                    $table = "mod_easywi_options_name_alias";

                    $values = array(
                        "technical_name" => $_POST["technical_name"],
                        "alias" => $_POST["alias"]
                    );

                    insert_query($table, $values);

                    echo $vars['_lang']['aliasAdded'];
                }

            } else {

                echo "<div style='float:left;width:100%;'>";

                echo "<form method='post' action='{$vars['modulelink']}&amp;method=alias&amp;action=add' class='form-horizontal'>";
                echo "<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>";

                echo "<tr>";
                echo "<td>{$vars['_lang']['technicalName']}</td>";
                echo "<td style='width:85%;'>";
                echo "<select name='technical_name'>";

                foreach ($technicalNames as $name) {
                    echo "<option>{$name}</option>";
                }

                echo "</select>";
                echo "</td>";
                echo "</tr>";

                echo "<tr>";
                echo "<td>{$vars['_lang']['alias']}</td><td><input type='text' name='alias' placeholder='Alias' /></td>";
                echo "</tr>";

                echo "<tr>";
                echo "<td></td><td><button type='submit' class='btn'><img src='images/icons/add.png' alt='' width='14' height='14' class='absmiddle' /> {$vars['_lang']['add']}</button></td>";
                echo "</tr>";

                echo "</table>";
                echo "</form>";
                echo "</div><div class='clear'></div>";
            }

        } else if (isset($_GET["action"]) && $_GET["action"] == "del" && isset($_GET["id"])) {

            $id = (int) $_GET["id"];

            $query = "DELETE FROM `mod_easywi_options_name_alias` WHERE `id`={$id}";

            full_query($query);

            echo $vars['_lang']['aliasRemoved'];

        } else if (isset($_GET["action"]) && $_GET["action"] == "mod" && isset($_GET["id"])) {

            $id = (int) $_GET["id"];

            if (isset($_POST["alias"]) && isset($_POST["technical_name"])) {

                if (!in_array($_POST["technical_name"], $technicalNames)) {
                    $error = $vars['_lang']['errorTechnicalName'];
                } else if (strlen($_POST["alias"]) == 0) {
                    $error = $vars['_lang']['errorName'];
                }

                if (isset($error)) {
                    echo "<div  style='margin:0;padding:10px;background-color:#FBEEEB;border:1px dashed #cc0000;font-weight: bold;color: #cc0000;font-size:14px;text-align: center;'>";
                    echo $error;
                    echo "</div>";
                } else {

                    $table = "mod_easywi_options_name_alias";

                    $update = array(
                        "technical_name" => $_POST["technical_name"],
                        "alias" => $_POST["alias"]
                    );

                    $where = array("id" => $id);

                    update_query($table, $update, $where);

                    echo $vars['_lang']['aliasEdited'];
                }

            } else {

                $table = "mod_easywi_options_name_alias";
                $fields = "technical_name,alias";
                $where = array("id" => $id);

                $result = select_query($table, $fields, $where);
                while ($row = mysql_fetch_assoc($result)) {

                    echo "<div style='float:left;width:100%;'>";

                    echo "<form method='post' action='{$vars['modulelink']}&amp;method=alias&amp;action=mod&amp;id={$id}' class='form-horizontal'>";
                    echo "<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>";

                    echo "<tr>";
                    echo "<td>{$vars['_lang']['technicalName']}</td>";
                    echo "<td style='width:85%;'>";
                    echo "<select name='technical_name'>";
                    foreach ($technicalNames as $name) {
                        echo ($name == $row["technical_name"]) ? "<option selected>{$name}</option>" : "<option>{$name}</option>";
                    }
                    echo "</select>";
                    echo "</td>";
                    echo "</tr>";

                    echo "<tr>";
                    echo "<td>{$vars['_lang']['alias']}</td><td><input type='text' name='alias' value='{$row['alias']}' /></td>";
                    echo "</tr>";

                    echo "<tr>";
                    echo "<td></td><td><button type='submit' class='btn'><img src='images/icons/add.png' alt='' width='14' height='14' class='absmiddle' /> {$vars['_lang']['add']}</button></td>";
                    echo "</tr>";

                    echo "</table>";
                    echo "</form>";
                    echo "</div><div class='clear'></div>";
                }
            }

        } else {

            echo "<div style='margin:5px;'>";
            echo "{$vars['_lang']['alias']} <a href='{$vars['modulelink']}&amp;method=alias&amp;action=add'><button type='button' class='btn'><img src='images/icons/add.png' alt='' width='14' height='14' class='absmiddle' /> {$vars['_lang']['add']}</button></a>";
            echo "</div>";

            echo "<div class='tablebg'>";
            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
            echo "<thead><tr><th>{$vars['_lang']['technicalName']}</th><th>{$vars['_lang']['alias']}</th><th>{$vars['_lang']['action']}</th></tr></thead>";

            $table = "mod_easywi_options_name_alias";
            $fields = "id,technical_name,alias";
            $where = array();

            $result = select_query($table, $fields, $where);
            while ($row = mysql_fetch_assoc($result)) {
                echo "<tr><td>{$row['technical_name']}</td><td>{$row['alias']}</td><td><a href='{$vars['modulelink']}&amp;method=alias&amp;action=mod&amp;id={$row['id']}'><button type='button' class='btn'><img src='images/icons/configoptions.png' alt='' width='14' height='14' class='absmiddle' /></button></a> <a href='{$vars['modulelink']}&amp;method=alias&amp;action=del&amp;id={$row['id']}'><button type='button' class='btn'><img src='images/icons/delete.png' alt='' width='14' height='14' class='absmiddle' /></button></a></td></tr>";
            }

            echo "<tbody>";
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        }

    } else if (isset($_GET["method"]) && $_GET["method"] == "valueAlias") {

        echo "<hr>";

        $technicalNames = array();

        $table = "mod_easywi_options_name_alias";
        $fields = "id,technical_name";

        $result = select_query($table, $fields, array());
        while ($row = mysql_fetch_assoc($result)) {
            $technicalNames[$row["id"]] = $row["technical_name"];
        }

        if (isset($_GET["action"]) && $_GET["action"] == "add") {

            if (isset($_POST["technical_id"]) && isset($_POST["value_alias"]) && isset($_POST["technical_name"])) {

                if (!isset($technicalNames[$_POST["technical_id"]])) {
                    $error = $vars['_lang']['errorTechnicalName'];
                } else if (strlen($_POST["technical_name"]) == 0) {
                    $error = $vars['_lang']['errorTechnicalName'];
                } else if (strlen($_POST["value_alias"]) == 0) {
                    $error = $vars['_lang']['errorName'];
                }

                if (isset($error)) {
                    echo "<div  style='margin:0;padding:10px;background-color:#FBEEEB;border:1px dashed #cc0000;font-weight: bold;color: #cc0000;font-size:14px;text-align: center;'>";
                    echo $error;
                    echo "</div>";
                } else {

                    $table = "mod_easywi_value_name_alias";

                    $values = array(
                        "option_alias_id" => $_POST["technical_id"],
                        "technical_name" => $_POST["technical_name"],
                        "alias" => $_POST["value_alias"]
                    );

                    insert_query($table, $values);

//TODO
                    $entryAdded = false;

                    $table = "mod_easywi_value_name_alias";
                    $fields = "id";
                    $result = select_query($table, $fields, $values);
                    while ($row = mysql_fetch_assoc($result)) {
                        $entryAdded = true;
                    }

                    if ($entryAdded) {
                        echo $vars['_lang']['aliasAdded'];
                    } else {

                        echo "<div  style='margin:0;padding:10px;background-color:#FBEEEB;border:1px dashed #cc0000;font-weight: bold;color: #cc0000;font-size:14px;text-align: center;'>";
                        echo "Inserting data failed";
                        echo "</div>";
                    }
                }

            } else {
                echo "<div style='float:left;width:100%;'>";

                echo "<form method='post' action='{$vars['modulelink']}&amp;method=valueAlias&amp;action=add' class='form-horizontal'>";
                echo "<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>";

                echo "<tr>";
                echo "<td>{$vars['_lang']['alias']}</td>";
                echo "<td style='width:85%;'>";
                echo "<select name='technical_id'>";

                foreach ($technicalNames as $id => $name) {
                    echo "<option value='{$id}'>{$name}</option>";
                }

                echo "</select>";
                echo "</td>";
                echo "</tr>";

                echo "<tr>";
                echo "<td>{$vars['_lang']['technicalName']}</td><td><input type='text' name='technical_name' placeholder='{$vars['_lang']['technicalName']}' /></td>";
                echo "</tr>";

                echo "<tr>";
                echo "<td>{$vars['_lang']['valueAlias']}</td><td><input type='text' name='value_alias' placeholder='{$vars['_lang']['valueAlias']}' /></td>";
                echo "</tr>";

                echo "<tr>";
                echo "<td></td><td><button type='submit' class='btn'><img src='images/icons/add.png' alt='' width='14' height='14' class='absmiddle' /> {$vars['_lang']['add']}</button></td>";
                echo "</tr>";

                echo "</table>";
                echo "</form>";
                echo "</div><div class='clear'></div>";
            }

        } else if (isset($_GET["action"]) && $_GET["action"] == "del" && isset($_GET["id"])) {

            $id = (int) $_GET["id"];

            $query = "DELETE FROM `mod_easywi_value_name_alias` WHERE `id`={$id} LIMIT 1";

            full_query($query);

            echo $vars['_lang']['aliasRemoved'];

        } else if (isset($_GET["action"]) && $_GET["action"] == "mod" && isset($_GET["id"])) {

            $id = (int) $_GET["id"];

            if (isset($_POST["technical_id"]) && isset($_POST["value_alias"]) && isset($_POST["technical_name"])) {

                if (!isset($technicalNames[$_POST["technical_id"]])) {
                    $error = $vars['_lang']['errorTechnicalName'];
                } else if (strlen($_POST["technical_name"]) == 0) {
                    $error = $vars['_lang']['errorTechnicalName'];
                } else if (strlen($_POST["value_alias"]) == 0) {
                    $error = $vars['_lang']['errorName'];
                }

                if (isset($error)) {
                    echo "<div  style='margin:0;padding:10px;background-color:#FBEEEB;border:1px dashed #cc0000;font-weight: bold;color: #cc0000;font-size:14px;text-align: center;'>";
                    echo $error;
                    echo "</div>";
                } else {

                    $table = "mod_easywi_value_name_alias";

                    $update = array(
                        "option_alias_id" => $_POST["technical_id"],
                        "technical_name" => $_POST["technical_name"],
                        "alias" => $_POST["value_alias"]
                    );

                    $where = array("id" => $id);

                    update_query($table, $update, $where);

                    echo $vars['_lang']['aliasEdited'];
                }

            } else {

                $table = "mod_easywi_value_name_alias";
                $fields = "technical_name,alias";
                $where = array("id" => $id);

                $result = select_query($table, $fields, $where);
                while ($row = mysql_fetch_assoc($result)) {

                    echo "<div style='float:left;width:100%;'>";

                    echo "<form method='post' action='{$vars['modulelink']}&amp;method=valueAlias&amp;action=mod&amp;id={$id}' class='form-horizontal'>";
                    echo "<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>";

                    echo "<tr>";
                    echo "<td>{$vars['_lang']['alias']}</td>";
                    echo "<td style='width:85%;'>";
                    echo "<select name='technical_id'>";

                    foreach ($technicalNames as $techicalId => $name) {
                        echo ($id == $techicalId) ? "<option value='{$techicalId}' selected='selected'>{$name}</option>" : "<option value='{$techicalId}'>{$name}</option>";
                    }

                    echo "</select>";
                    echo "</td>";
                    echo "</tr>";

                    echo "<tr>";
                    echo "<td>{$vars['_lang']['technicalName']}</td><td><input type='text' name='technical_name' placeholder='{$vars['_lang']['technicalName']}' value='{$row['technical_name']}' /></td>";
                    echo "</tr>";

                    echo "<tr>";
                    echo "<td>{$vars['_lang']['valueAlias']}</td><td><input type='text' name='value_alias' placeholder='{$vars['_lang']['valueAlias']}' value='{$row['alias']}' /></td>";
                    echo "</tr>";

                    echo "<tr>";
                    echo "<td></td><td><button type='submit' class='btn'><img src='images/icons/add.png' alt='' width='14' height='14' class='absmiddle' /> {$vars['_lang']['add']}</button></td>";
                    echo "</tr>";

                    echo "</table>";
                    echo "</form>";
                    echo "</div><div class='clear'></div>";
                }
            }

        } else {

            echo "<div style='margin:5px;'>";
            echo "{$vars['_lang']['valueAlias']} <a href='{$vars['modulelink']}&amp;method=valueAlias&amp;action=add'><button type='button' class='btn'><img src='images/icons/add.png' alt='' width='14' height='14' class='absmiddle' /> {$vars['_lang']['add']}</button></a>";
            echo "</div>";

            echo "<div class='tablebg'>";
            echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
            echo "<thead><tr><th>{$vars['_lang']['technicalName']}</th><th>{$vars['_lang']['technicalName']}</th><th>{$vars['_lang']['valueAlias']}</th><th>{$vars['_lang']['action']}</th></tr></thead>";

            $query = "SELECT n.`technical_name` AS `option_technical_name`,v.`id`,v.`technical_name`,v.`alias` FROM `mod_easywi_value_name_alias` AS v LEFT JOIN `mod_easywi_options_name_alias` AS n ON n.`id`=v.`option_alias_id` ORDER BY n.`technical_name`,v.`technical_name`";
            $result = full_query($query);
            while ($row = mysql_fetch_assoc($result)) {
                echo "<tr><td>{$row['option_technical_name']}</td><td>{$row['technical_name']}</td><td>{$row['alias']}</td><td><a href='{$vars['modulelink']}&amp;method=valueAlias&amp;action=mod&amp;id={$row['id']}'><button type='button' class='btn'><img src='images/icons/configoptions.png' alt='' width='14' height='14' class='absmiddle' /></button></a> <a href='{$vars['modulelink']}&amp;method=valueAlias&amp;action=del&amp;id={$row['id']}'><button type='button' class='btn'><img src='images/icons/delete.png' alt='' width='14' height='14' class='absmiddle' /></button></a></td></tr>";
            }

            echo "<tbody>";
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        }
    }
}