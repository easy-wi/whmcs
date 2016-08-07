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

$_ADDONLANG = array(
    "action" => "Action",
    "active" => "Active",
    "add" => "Add",
    "address" => "Address",
    "alias" => "Option Aliases",
    "aliasAdded" => "Alias mapping added",
    "aliasEdited" => "Alias mapping edited",
    "aliasRemoved" => "Alias mapping removed",
    "available" => "Available",
    "availableIn" => "Available in",
    "check" => "Check",
    "checkServer" => "Check for servers",
    "createNewUser" => "Create new user",
    "createNewOrderFor" => "Create new order for",
    "dbsInstalled" => "Databases installed",
    "description" => "Description",
    "dns" => "DNS",
    "doNothing" => "Do nothing",
    "email" => "Email",
    "errorName" => "Error: No alias specified!",
    "errorTechnicalName" => "Error: Invalid technical name!",
    "easyWiDetails" => "Easy-Wi Details",
    "externalID" => "Easy-Wi externalID",
    "firstName" => "First name",
    "gameInstalled" => "Installed on masterservers and ready for use are following games: ",
    "games" => "Gameserver",
	"games" => "Lended Gameserver",
    "gamesAvailable" => "Games available",
    "gameName" => "Game name",
    "gamesNotInstalled" => "Currently there are no games installed on any masterserver.",
    "gamesNotInstalledMaster" => "There are no game masterservers installed.",
    "hddUsage" => "HDD usage",
    "helpAliases" => "At configurable options either a technical name like CPU, Ram or a self explaining one can be set. If a name other than a technical is used, it needs to be mapped here.",
    "helpValueAliases" => "If a configurable option is a dropdown and contains speaking values, they need to be mapped here.",
    "helpMasterlist" => "This overview is showing the current status of all active masterservers at Easy-Wi.",
    "helpUserEasyWi" => "Easy-Wi is data master in this method. Based on Easy-Wi data, users at WHMCS can be created or mapped.",
    "helpUserLocal" => "WHMCS is the data master in this method. Based on WHMCS data you can create, map or resend users to Easy-Wi.",
    "hostname" => "Hostname",
    "id" => "Easy-Wi ID",
    "installedDNS" => "Installed DNS",
    "installedserver" => "Installed server",
    "installedslots" => "Installed slots",
    "installedVhosts" => "Installed vhosts",
    "intro" => "This overview will show you the overall sync status. Also you are able to import and retrigger data from and into Easy-Wi",
    "ip" => "IP address",
    "lastName" => "Last name",
    "lend" => "Lend",
    "lendAllGameTaken" => "All server are taken. Next game server is scheduled to be free in %m% minutes.",
    "lendAllVoiceTaken" => "All server are taken. Next voice server is scheduled to be free in %m% minutes.",
    "lendError" => "Unfortunately somebody else was faster than you and there is no server left now.",
    "lendServerList" => "Lendserver Overview",
    "lendServerStatusCheck" => "Next statuscheck will be executed in %m% minutes.",
    "lendSuccess" => "Server successfully lendet. The data is as follows:",
    "lendTime" => "Lend time",
    "list" => "List",
    "localID" => "WHMCS ID",
    "loginName" => "Login name",
    "map" => "Map",
    "mapToOrder" => "Map order",
    "masterlist" => "Masterlist",
    "maxDBs" => "Maximum databases",
    "maxDNS" => "Maximum DNS",
    "maxHDD" => "Maximum HDD",
    "maxserver" => "Maximum server",
    "maxslots" => "Maximum slots",
    "maxVhost" => "Maximum vhosts",
    "minutes" => "Minutes",
    "N" => "No",
    "notSynced" => "Not synced",
    "notSyncedEasyWi" => "Missing in Easy-Wi",
    "notSyncedEasyWiMissing" => "WHMCS link OK, Easy-Wi link broken",
    "notSyncedLocal" => "Broken link in WHMCS",
    "notSyncedLocalMissing" => "Link exists, user missing",
    "password" => "Password",
    "port" => "Port",
    "productNotExisting" => "No Product configured",
    "productNotExistingShorten" => "No product with required shorten. Missing at producs are:",
    "protectionError" => "Bad news, the server is not protected!",
    "protectionInfo" => "Please enter the server address in the format IP:PORT!",
    "protectionOk" => "Good news, the server is protected!",
    "protectionUnknownState" => "Our protection system does not know the given address!",
    "recordsJump" => "Jump to Page",
    "recordsPage" => "Records per page",
    "recordsShowing" => "Showing %s to %e of %t entries",
    "resend" => "Resend",
    "slots" => "Slots",
    "syncStatus" => "Sync status",
    "syncFailed" => "Sync failed",
    "syncSelected" => "Sync selected users",
    "syncSelectedOrders" => "Sync selected orders",
    "syncUser" => "Sync user",
    "syncUserBestMatch" => "Best match",
    "syncNoPointExternal" => "Syncing can be dangerous as as option \"Remove users\" is set to \"Yes\". Sync operation might cause data loss on Easy-Wi side!",
    "syncNoPointUserSync" => "Cannot search and sync servers as user account is not in sync with WHMCS and Easy-Wi!",
    "syncNoPointInternal" => "Not synced",
    "syncSuccess" => "Sync succeeded",
    "shorten" => "Shorten",
    "show" => "Show",
    "synced" => "Synced",
    "technicalName" => "Technical name",
    "type" => "Type",
    "userlocal" => "Local User",
    "usereasywi" => "Easy-Wi User",
    "valueAlias" => "Value Aliases",
    "voice" => "Voice server",
    "voiceServer" => "Voice master",
    "webMaster" => "Webspace master",
    "Y" => "Yes",
);