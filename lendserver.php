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

if ($easyWiObject->lendModule == "Yes") {

    if (isset($_SESSION["Language"]) && preg_match("/[\w]{1,}/", $_SESSION["Language"]) && file_exists(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "lang" . DS . $_SESSION["Language"] . ".php")) {
        require_once(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "lang" . DS . $_SESSION["Language"] . ".php");
    } else if (preg_match("/[\w]{1,}/", $CONFIG["Language"]) && file_exists(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "lang" . DS . $CONFIG["Language"] . ".php")) {
        require_once(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "lang" . DS . $CONFIG["Language"] . ".php");
    } else {
        require_once(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "lang" . DS . "english.php");
    }

    $ca = new WHMCS_ClientArea();

    $ca->setPageTitle($easyWiObject->lendTitle);

    $ca->addToBreadCrumb('index.php', $whmcs->get_lang('globalsystemname'));
    $ca->addToBreadCrumb('lendserver.php', $easyWiObject->lendTitle);

    $ca->initPage();

    if (isset($_POST["type"]) && isset($_POST["slots"]) && isset($_POST["time"]) && isset($_POST["password"]) && (($_POST["type"] == "gs" && isset($_POST["game"]) && preg_match('/^[\w\.\-\_]+$/', $_POST["game"])) || $_POST["type"] == "vo")) {

        $password = (preg_match("/^[\w]+$/", $_POST["password"])) ? $_POST["password"] : $easyWiObject->passwordGenerate();

        if ($_POST["type"] == "gs") {

            $rcon = (preg_match("/^[\w]+$/", $_POST["rcon"])) ? $_POST["rcon"] : $easyWiObject->passwordGenerate();

            $return = $easyWiObject->lendServer($type, $_SERVER['REMOTE_ADDR'], array("slots" => (int) $_POST["slots"], "lendtime" => (int) $_POST["time"], "password" => $password, "rcon" => $rcon, "game" => $_POST["game"]));

        } else {
            $return = $easyWiObject->lendServer($type, $_SERVER['REMOTE_ADDR'], array("slots" => (int) $_POST["slots"], "lendtime" => (int) $_POST["time"], "password" => $password));
        }

        if ($return == "tooslow") {
            $return = new stdClass();
            $return->error = $_ADDONLANG["lendError"];
        } else {
            $return = @simplexml_load_string($return);
        }

        if (is_object($return)) {

            $_SESSION["easyWiLendStatus"] = array();

            if ($_POST["type"] == "gs") {
                $_SESSION["easyWiLendStatus"]["G"] = true;
            } else {
                $_SESSION["easyWiLendStatus"]["V"] = true;
            }

            $ca->assign("easy_wi_lang", $_ADDONLANG);
            $ca->assign("return", $return);
            $ca->assign("type", $_POST["type"]);

            $ca->setTemplate('easy_wi_lendserver_lendrequest');
        }
    }

    if (!isset($return) || !is_object($return)) {

        $gameServerFree = 0;
        $gameServerTotal = 0;
        $voiceServerFree = 0;
        $voiceServerTotal = 0;

        // Clean cached entries with TTL reached
        $query = "DELETE FROM `mod_easywi_lendserver_cache` WHERE TIMESTAMPDIFF(SECOND,`checked_at`,CURRENT_TIMESTAMP) > 20";
        full_query($query);

        if (isset($_SESSION["easyWiLendStatus"])) {

            // Check if REST call is still cached
            $query = "SELECT `type`,`response` FROM `mod_easywi_lendserver_cache` WHERE `address`='{$_SERVER['REMOTE_ADDR']}' LIMIT 2";
            $result = full_query($query);
            while ($row = mysql_fetch_assoc($result)) {
                if ($row["type"] == "G") {
                    $lendStatusGameserver = @json_decode($row["response"]);
                } else if ($row["type"] == "V") {
                    $lendStatusVoiceServer = @json_decode($row["response"]);
                }
            }

            if (isset($_SESSION["easyWiLendStatus"]["G"]) && !isset($lendStatusGameserver)) {

                $lendStatusGameserver = $easyWiObject->lendIpUsed('gs', 'ipStatus', $_SERVER["REMOTE_ADDR"]);

                // Insert for caching
                $query = "INSERT INTO `mod_easywi_lendserver_cache` (`address`,`type`,`response`) VALUES ('{$_SERVER['REMOTE_ADDR']}','G','" . json_encode($lendStatusGameserver) . "')";
                full_query($query);

            }

            if (isset($_SESSION["easyWiLendStatus"]["V"]) && !isset($lendStatusVoiceServer)) {

                $lendStatusVoiceServer = $easyWiObject->lendIpUsed('vo', 'ipStatus', $_SERVER["REMOTE_ADDR"]);

                // Insert for caching
                $query = "INSERT INTO `mod_easywi_lendserver_cache` (`address`,`type`,`response`) VALUES ('{$_SERVER['REMOTE_ADDR']}','V','" . json_encode($lendStatusVoiceServer) . "')";
                full_query($query);
            }

            if (!isset($lendStatusGameserver) || !is_object($lendStatusGameserver) || (is_object($lendStatusGameserver) && $lendStatusGameserver->status != "stillrunning" && $lendStatusGameserver->status != "started")) {
                // This removes valid data from the session for whatever reason
                unset($_SESSION["easyWiLendStatus"]["G"]);
            } else {
                $ca->assign("lendGame", $lendStatusGameserver);
            }

            if (!isset($lendStatusVoiceServer) || !is_object($lendStatusVoiceServer) || (is_object($lendStatusVoiceServer) && $lendStatusVoiceServer->status != "stillrunning" && $lendStatusVoiceServer->status != "started")) {
                // This removes valid data from  the session for whatever reason
                unset($_SESSION["easyWiLendStatus"]["V"]);
            } else {
                $ca->assign("lendVoice", $lendStatusVoiceServer);
            }
        }

         // Check if REST call is still cached
        $query = "SELECT `type`,`response` FROM `mod_easywi_lendserver_cache` WHERE `address`='127.0.0.1' LIMIT 3";
        $result = full_query($query);
        while ($row = mysql_fetch_assoc($result)) {
            if ($row["type"] == "G" && !isset($_SESSION["easyWiLendStatus"]["G"])) {
                $lendStatusGameGlobal = @json_decode($row["response"]);
            } else if ($row["type"] == "V" && !isset($_SESSION["easyWiLendStatus"]["V"])) {
                $lendStatusVoiceGlobal = @json_decode($row["response"]);
            } else if ($row["type"] == "A") {
                $lendStatusGlobal = @json_decode($row["response"]);
            }
        }

        if ((!isset($lendStatusGlobal) || !is_object($lendStatusGlobal)) ) {

            $lendStatusGlobal = $easyWiObject->lendOverallStatus(false);

            // Insert for caching
            $query = "INSERT INTO `mod_easywi_lendserver_cache` (`address`,`type`,`response`) VALUES ('127.0.0.1','A','" . json_encode($lendStatusGlobal) . "')";
            full_query($query);
        }

        if (isset($lendStatusGlobal) && is_object($lendStatusGlobal)) {

            $serverList = array();

            foreach ($lendStatusGlobal as $type => $server) {

                $type = (string) $type;

                if ($type == "gameserver") {

                    $runningGame = (string) $server->runningGame;

                    $array = array(
                        'ip' => (string) $server->ip,
                        'port' => (string) $server->port,
                        'slots' => (string) $server->slots,
                        'usedslots' => (string) $server->usedslots,
                        'timeleft' => ($server->timeleft == 0) ? $_ADDONLANG["available"] : (int) $server->timeleft . ' ' . $_ADDONLANG["minutes"],
                        'queryName' =>(string)  $server->queryName,
                        'queryMap' => (string) $server->queryMap,
                        'runningGame' => (string) $server->games->$runningGame,
                        'games' => (array) $server->games
                    );

                } else {

                    $array = array(
                        'ip' => (string) $server->ip,
                        'port' => (string) $server->port,
                        'slots' => (string) $server->slots,
                        'usedslots' => (string) $server->usedslots,
                        'timeleft' => ($server->timeleft == 0) ? $_ADDONLANG["available"] : (int) $server->timeleft . ' ' . $_ADDONLANG["minutes"],
                        'queryName' =>(string)  $server->queryName,
                        'connect' => (string) $server->connect
                    );
                }

                $serverList[$type][] = $array;
            }

            $ca->assign("lendStatusGlobal", $serverList);
        }

        if ((!isset($lendStatusGameGlobal) || !is_object($lendStatusGameGlobal)) && !isset($_SESSION["easyWiLendStatus"]["G"])) {

            $lendStatusGameGlobal = $easyWiObject->lendOverallStatus('gs');

            // Insert for caching
            $query = "INSERT INTO `mod_easywi_lendserver_cache` (`address`,`type`,`response`) VALUES ('127.0.0.1','G','" . json_encode($lendStatusGameGlobal) . "')";
            full_query($query);
        }

        if (isset($lendStatusGameGlobal) && is_object($lendStatusGameGlobal)) {

            $_ADDONLANG["lendAllGameTaken"] = str_replace("%m%", $lendStatusGameGlobal->nextfree, $_ADDONLANG["lendAllGameTaken"]);

            $selectOption = array();

            foreach ((array) $lendStatusGameGlobal->games as $shorten => $game) {

                if ((int) $game->free > 0) {
                    $selectOption[] = $shorten;
                }

                $gameServerFree += (int) $game->free;
                $gameServerTotal += (int) $game->total;
            }

            $ca->assign("gameTypeSelect", $selectOption);
            $ca->assign("gameServerFree", $gameServerFree);
            $ca->assign("gameServerTotal", $gameServerTotal);

            $selectOption = array();

            if ($lendStatusGameGlobal->maxplayer > 0) {

                $current = $lendStatusGameGlobal->minplayer;

                while ($current <= $lendStatusGameGlobal->maxplayer) {
                    $selectOption[] = $current;
                    $current = $current + $lendStatusGameGlobal->playersteps;
                }
            }

            $ca->assign("gameSlotSelect", $selectOption);

            $selectOption = array();

            if ($lendStatusGameGlobal->maxtime > 0) {

                $current = $lendStatusGameGlobal->mintime;

                while ($current <= $lendStatusGameGlobal->maxtime) {
                    $selectOption[] = $current;
                    $current = $current + $lendStatusGameGlobal->timesteps;
                }
            }

            $ca->assign("gameTimeSelect", $selectOption);

            $ca->assign("lendStatusGame", $lendStatusGameGlobal);
        }

        if ((!isset($lendStatusVoiceGlobal) || !is_object($lendStatusVoiceGlobal)) && !isset($_SESSION["easyWiLendStatus"]["V"])) {

            $lendStatusVoiceGlobal = $easyWiObject->lendOverallStatus('vo');

            // Insert for caching
            $query = "INSERT INTO `mod_easywi_lendserver_cache` (`address`,`type`,`response`) VALUES ('127.0.0.1','V','" . json_encode($lendStatusVoiceGlobal) . "')";
            full_query($query);
        }

        if (isset($lendStatusVoiceGlobal) && is_object($lendStatusVoiceGlobal)) {

            $_ADDONLANG["lendAllVoiceTaken"] = str_replace("%m%", $lendStatusVoiceGlobal->nextfree, $_ADDONLANG["lendAllVoiceTaken"]);

            $selectOption = array();

            if ($lendStatusVoiceGlobal->maxplayer > 0) {

                $current = $lendStatusVoiceGlobal->minplayer;

                while ($current <= $lendStatusVoiceGlobal->maxplayer) {
                    $selectOption[] = $current;
                    $current = $current + $lendStatusVoiceGlobal->playersteps;
                }
            }

            $ca->assign("voiceSlotSelect", $selectOption);

            $selectOption = array();

            if ($lendStatusVoiceGlobal->maxtime > 0) {

                $current = $lendStatusVoiceGlobal->mintime;

                while ($current <= $lendStatusVoiceGlobal->maxtime) {
                    $selectOption[] = $current;
                    $current = $current + $lendStatusVoiceGlobal->timesteps;
                }
            }

            $ca->assign("voiceTimeSelect", $selectOption);

            $ca->assign("voiceServerFree", (int) $lendStatusVoiceGlobal->ts3->free);
            $ca->assign("voiceServerTotal", (int) $lendStatusVoiceGlobal->ts3->total);
            $ca->assign("lendStatusVoice", $lendStatusVoiceGlobal);
        }

        if (isset($lendStatusGameGlobal) && is_object($lendStatusGameGlobal)) {
            $nextCheckInNMinutes = $lendStatusGameGlobal->nextcheck;
        } else if (isset($lendStatusVoiceGlobal) && is_object($lendStatusVoiceGlobal)) {
            $nextCheckInNMinutes = $lendStatusVoiceGlobal->nextcheck;
        } else {
            $nextCheckInNMinutes = 1;
        }

        $_ADDONLANG["lendServerStatusCheck"] = str_replace("%m%", $nextCheckInNMinutes, $_ADDONLANG["lendServerStatusCheck"]);

        $ca->assign("easy_wi_lang", $_ADDONLANG);

        $ca->setTemplate('easy_wi_lendserver_start');
    }

    $ca->output();

} else {
    header('HTTP/1.1 302 Found');
    header('Location: '. str_replace("lendserver.php", "", $_SERVER["DOCUMENT_URI"]));
    die;
}