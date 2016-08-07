<?php

/**
 * Easy-Wi Addon Hooks
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
    define("WHMCS_MAIN_DIR", substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), "modules" . DS . "addons")));
}

if (!class_exists("EasyWi")) {
    require_once(WHMCS_MAIN_DIR . DS . "modules" . DS . "addons" . DS . "easywi" . DS . "inc" . DS . "easywi.php");
}

// Inside hook functions the class will check if the user is synced and should be synced. Only if required we will make API calls.
// Logig is always within the class. That allows to register the same function multiple times.

function easywi_hook_user_add ($vars) {

    $easyWiObject = new EasyWi();
    $easyWiObject->addUser($vars);
}
add_hook("ClientAdd", 1, "easywi_hook_user_add");
add_hook("ClientAreaRegister", 1, "easywi_hook_user_add");


function easywi_hook_user_edit ($vars) {

    $easyWiObject = new EasyWi();
    $easyWiObject->modUser($vars);
}
add_hook("ClientEdit", 1, "easywi_hook_user_edit");
add_hook("ClientClose", 1, "easywi_hook_user_edit");


function easywi_hook_user_password ($vars) {

    $easyWiObject = new EasyWi();
    $easyWiObject->changePassword($vars);
}
add_hook("ClientChangePassword", 1, "easywi_hook_user_password");


function easywi_hook_user_remove ($vars) {

    $easyWiObject = new EasyWi();
    $easyWiObject->removeUser($vars);
}
add_hook("ClientDelete", 1, "easywi_hook_user_remove");


function easywi_hook_product_remove ($vars) {

    $easyWiObject = new EasyWi();
    $easyWiObject->orderProvision($vars, "terminate");
}
add_hook("CancelOrder", 1, "easywi_hook_product_remove");
add_hook("DeleteOrder", 1, "easywi_hook_product_remove");
add_hook("ServiceDelete", 1, "easywi_hook_product_remove");

function easywi_hook_product_pending ($vars) {

    $easyWiObject = new EasyWi();
    $easyWiObject->orderProvision($vars, "pending");
}
add_hook("PendingOrder", 1, "easywi_hook_product_pending");


function easywi_hook_product_fraud ($vars) {

    $easyWiObject = new EasyWi();
    $easyWiObject->orderProvision($vars, "fraud");
}
add_hook("FraudOrder", 1, "easywi_hook_product_fraud");


function easywi_hook_product_add ($vars) {

    $easyWiObject = new EasyWi();
    $easyWiObject->orderProvision($vars, "add");
}
add_hook("AcceptOrder", 1, "easywi_hook_product_add");


function easywi_hook_product_change ($vars) {

    $easyWiObject = new EasyWi();
    $easyWiObject->orderProvision($vars, "mod");
}
add_hook("AfterProductUpgrade", 1, "easywi_hook_product_change");
add_hook("AfterConfigOptionsUpgrade", 1, "easywi_hook_product_change");
add_hook("AfterModuleChangePackage", 1, "easywi_hook_product_change");
add_hook("AdminServiceEdit", 1, "easywi_hook_product_change");

function easywi_show_update_available () {

    $easyWiObject = new EasyWi();
    $versionCheck = $easyWiObject->checkForUpdate();

    if ($versionCheck) {
        $returnHtml = "<div id=\"sysoverviewbanner\">";
        $returnHtml .= "<div style=\"margin:0;padding: 10px;background-color: yellow;border: 1px dashed #cc0000;font-weight: bold;color: #cc0000;font-size:14px;text-align: center;\">";
        $returnHtml .= "Easy-Wi addon update available: " . $versionCheck;
        $returnHtml .= "</div>";
        $returnHtml .= "</div>";

        return $returnHtml;
    }

    return "";
}
add_hook("AdminHomepage", 1, "easywi_show_update_available");