<?php

/**
 * Easy-Wi Addon
 *
 * @package    WHMCS
 * @author     Ulrich Block <ulrich.block@easy-wi.com>
 * @copyright  Copyright (c) Ulrich Block
 * @license    http://www.gnu.org/licenses/gpl-3.0
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

if (!defined("DS")) {
    define("DS", DIRECTORY_SEPARATOR);
}

if (!defined("WHMCS_MAIN_DIR")) {
    define("WHMCS_MAIN_DIR", dirname(__FILE__) . DS);
}

if (!class_exists("EasyWi")) {
    require_once(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "inc" . DS . "easywi.php");
}

define("CLIENTAREA",true);
//define("FORCESSL",true); // Uncomment to force the page to use https://

require_once(WHMCS_MAIN_DIR . DS . "init.php");

$easyWiObject = new EasyWi();

if ($easyWiObject->protectionModule == "Yes") {

    // Get the configured language: $whmcs->get_client_language() Fallback to $CONFIG["Language"] Else use english
    if (isset($_SESSION["Language"]) && preg_match("/[\w]{1,}/", $_SESSION["Language"]) && file_exists(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "lang" . DS . $_SESSION["Language"] . ".php")) {
        require_once(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "lang" . DS . $_SESSION["Language"] . ".php");
    } else if (preg_match("/[\w]{1,}/", $CONFIG["Language"]) && file_exists(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "lang" . DS . $CONFIG["Language"] . ".php")) {
        require_once(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "lang" . DS . $CONFIG["Language"] . ".php");
    } else {
        require_once(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "lang" . DS . "english.php");
    }

    $ca = new WHMCS_ClientArea();

    $ca->setPageTitle($easyWiObject->protectionTitle);

    $ca->addToBreadCrumb("index.php", $whmcs->get_lang("globalsystemname"));
    $ca->addToBreadCrumb("protectioncheck.php", $easyWiObject->protectionTitle);

    $ca->initPage();

    $inputHighlighting = "";
    $address = "";

    if (isset($_POST["address"])) {

        @list($ip, $port) = explode(":", $_POST["address"]);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) and preg_match("/^(0|([1-9]\d{0,3}|[1-5]\d{4}|[6][0-5][0-5]([0-2]\d|[3][0-5])))$/", $port)) {

            $address = "{$ip}:{$port}";
            // Clean old cached queries from DB
            $query = "DELETE FROM `mod_easywi_protection_mode_cache` WHERE TIMESTAMPDIFF(SECOND,`checked_at`,CURRENT_TIMESTAMP) > 20";
            full_query($query);

            // Check if REST call is still cached
            $query = "SELECT `response` FROM `mod_easywi_protection_mode_cache` WHERE `address`='{$address}' LIMIT 1";
            $result = full_query($query);
            $data = mysql_fetch_assoc($result);

            if (isset($data["response"]) && strlen($data["response"]) > 0) {

                $protectionStatus = json_decode($data["response"]);

            // If not cached do normal logic
            } else {

                // check if IP:PORT exists at an Easy-Wi product at WHMCS
                $query = "SELECT ip.`relid` FROM `tblcustomfieldsvalues` AS ip INNER JOIN `tblcustomfieldsvalues` AS port ON ip.`relid`=port.`relid` WHERE ip.`value`='{$ip}' AND port.`value`='{$port}' LIMIT 1";
                $result = full_query($query);
                $data = mysql_fetch_assoc($result);

                if (isset($data["relid"]) && $data["relid"] > 0) {

                    // If exists, check if protectionmode is active for the server
                    $vars = $easyWiObject->getServiceDetailsById($data["relid"]);
                    $options = $easyWiObject->configOptionsOverwrite($vars);

                    $protectionStatus = $easyWiObject->protectionApiCall($ip, $port);

                    // Insert for caching
                    $query = "INSERT INTO `mod_easywi_protection_mode_cache` (`address`,`response`) VALUES ('{$address}','" . json_encode($protectionStatus) . "')";
                    full_query($query);
                }
            }

            if (isset($protectionStatus) and is_object($protectionStatus)) {
                if ($protectionStatus->protection == "yes") {

                    $ca->assign("protectionStatus", $protectionStatus);

                    $inputHighlighting = "success";

                } else {
                    $inputHighlighting = "error";
                }
            }
        }

        if ($inputHighlighting == "") {
            $inputHighlighting = "warning";
        }
    }

    $ca->assign("easy_wi_lang", $_ADDONLANG);
    $ca->assign("address", $address);
    $ca->assign("inputHighlighting", $inputHighlighting);

    $ca->setTemplate("easy_wi_protectioncheck");

    $ca->output();

} else {

    header('HTTP/1.1 302 Found');
    header('Location: '. str_replace("protectioncheck.php", "", $_SERVER["DOCUMENT_URI"]));
    die;
}