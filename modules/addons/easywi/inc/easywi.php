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

class EasyWi {

    public $syncUsers, $removeUsers, $idPrefix, $url, $lendModule, $lendTitle, $protectionModule, $protectionTitle;
    private $version, $user, $password, $timeout, $syncPasswords, $mapByEmail, $statePending, $stateFraud, $provisioning, $license, $licenseValid, $licenseLastCheck, $versionExternal, $versionFile, $optionsRaw;
    private $details = array();
    public $logAPIResponses;

    function __construct () {

        $this->versionFile = "1.9";

        $table = "tbladdonmodules";
        $fields = "setting,value";
        $where = array("module" => "easywi");

        $result = select_query($table, $fields, $where);
        while ($data = mysql_fetch_assoc($result)) {

            if ($data["setting"] == "version") {
                $this->version = $data["value"];
            } else if ($data["setting"] == "url") {
                $this->url = $data["value"];
            } else if ($data["setting"] == "user") {
                $this->user = $data["value"];
            } else if ($data["setting"] == "password") {
                $this->password = $data["value"];
            } else if ($data["setting"] == "timeout") {
                $this->timeout = (int) $data["value"];
            } else if ($data["setting"] == "syncPasswords") {
                $this->syncPasswords = $data["value"];
            } else if ($data["setting"] == "syncUsers") {
                $this->syncUsers = $data["value"];
            } else if ($data["setting"] == "removeUsers") {
                $this->removeUsers = $data["value"];
            } else if ($data["setting"] == "logAPIResponses") {
                $this->logAPIResponses = $data["value"];
            } else if ($data["setting"] == "mapByEmail") {
                $this->mapByEmail = $data["value"];
            } else if ($data["setting"] == "idPrefix") {
                $this->idPrefix = $data["value"];
            } else if ($data["setting"] == "statePending") {
                $this->statePending = $data["value"];
            } else if ($data["setting"] == "stateFraud") {
                $this->stateFraud = $data["value"];
            } else if ($data["setting"] == "provisioning") {
                $this->provisioning = $data["value"];
            } else if ($data["setting"] == "license") {
                $this->license = $data["value"];
            } else if ($data["setting"] == "lendModule") {
                $this->lendModule = $data["value"];
            } else if ($data["setting"] == "lendTitle") {
                $this->lendTitle = $data["value"];
            } else if ($data["setting"] == "protectionModule") {
                $this->protectionModule = $data["value"];
            } else if ($data["setting"] == "protectionTitle") {
                $this->protectionTitle = $data["value"];
            }
        }
    }

    function __desctruct () {
        unset($url, $user, $password, $timeout, $syncPasswords, $syncUsers, $removeUsers, $mapByEmail, $idPrefix);
    }

    // abstract general methods
    private function apiCall ($vars) {

        $postfields = array(
            "pwd" => $this->password,
            "user" => $this->user,
            "type" => $vars["type"],
            "xml" => base64_encode($vars["xml"])
        );

        $url = (substr($this->url, 0, -1) == "/") ? $this->url . "api.php" : $this->url . "/api.php";

        return ($this->provisioning == "Yes") ? $this->curlCall($url, $postfields, array()) : false;
    }

    private function yesNo ($value) {
        return ($value == "Yes") ? "Y" : "N";
    }

    public function addLogentry ($vars, $response = false, $rawResponse = false) {
 
        if ($this->logAPIResponses == "Yes") {

            // convert object into string for better readability at log overview

            $responseString = "WHMCS data used: ";

            foreach ($this->optionsRaw as $k => $v) {

                if (is_array($v)) {
                    $v = json_encode($v);
                }

                $responseString .= "\"" . $k . "\"=>\"" . $v . "\",";
            }

            $responseString .= " WHMCS data after overwrite: ";

            foreach ($vars as $k => $v) {

                if (is_array($v)) {
                    $v = json_encode($v);
                }

                if ($v == "Yes" or $v == "No") {
                    $v = $this->yesNo($v);
                }

                $responseString .= "\"" . $k . "\"=>\"" . $v . "\",";
            }

            if ($response !== false) {

                if (is_string($response) and strlen($response) > 0) {
                    $responseString .= " Easy-Wi.com API Response: " . $response;
                } else {

                    $response = (array) $response;
                    $responseString .= " Easy-Wi.com API Response: ";
                    foreach ($response as $k => $v) {

                        if (is_array($v)) {
                            $v = json_encode($v);
                        }

                        $responseString .= "\"" . $k . "\"=>\"" . $v . "\",";
                    }
                }
            } else if ($rawResponse !== false) {
                $responseString .= " Easy-Wi.com API Response: " . $rawResponse;
            } else {
                $responseString .= "No Easy-Wi.com API Response";
            }

            logActivity($responseString);
        }
    }

    // WHMCS method curlCall supports to less options, so we need to build it on our own
    private function curlCall ($url, $postfields, $options, $secureSSL = false) {

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, ($this->timeout > 0) ? $this->timeout : 10);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $secureSSL);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $secureSSL);
        curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, "https://github.com/easy-wi/whmcs");

        foreach ($options as $key => $value) {

            if ($key == "CURLOPT_HTTPAUTH") {
                curl_setopt($curl, CURLOPT_HTTPAUTH, $value);
            } else if ($key == "CURLOPT_HTTPAUTH") {
                curl_setopt($curl, CURLOPT_HTTPAUTH, $value);
            } else if ($key == "CURLOPT_USERPWD") {
                curl_setopt($curl, CURLOPT_USERPWD, $value);
            } else if ($key == "CURLOPT_VERBOSE") {
                curl_setopt($curl, CURLOPT_VERBOSE, $value);
            } else if ($key == "CURLOPT_HEADER") {
                curl_setopt($curl, CURLOPT_HEADER, $value);
            } else if ($key == "CURLOPT_SSL_VERIFYPEER") {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $value);
            } else if ($key == "CURLOPT_SSL_VERIFYHOST") {
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $value);
            } else if ($key == "CURLOPT_RETURNTRANSFER") {
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, $value);
            } else if ($key == "CURLINFO_HEADER_OUT") {
                curl_setopt($curl, CURLINFO_HEADER_OUT, $value);
            } else if ($key == "CURLOPT_TIMEOUT") {
                curl_setopt($curl, CURLOPT_TIMEOUT, $value);
            }
        }

        if (count($postfields) > 0) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
        }

        return curl_exec($curl);
    }

    private function apiListAll ($types) {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("server");

        $key = $postxml->createElement("action", "ls");
        $element->appendChild($key);

        foreach ($types as $k => $v) {
            if ($v) {
                $key = $postxml->createElement("type", $k);
                $element->appendChild($key);
            }
        }

        $postxml->appendChild($element);

        return array("type" => "list", "xml" => $postxml->saveXML());
    }

    // User specific methods begin here.

    public function isUserSynced ($userID) {

        $userID = (int) $userID;

        $table = "mod_easywi_user_synced";
        $fields = "synced";
        $where = array("user_id" => $userID);

        $result = select_query($table, $fields, $where);
        $data = mysql_fetch_assoc($result);

        return ($data["synced"] == "Y") ? "Y" : "N";
    }

    public function addUserSyncEntry ($userID, $status) {

        $userID = (int) $userID;

        $query = "INSERT INTO `mod_easywi_user_synced` (`user_id`,`synced`) VALUES ({$userID},'{$status}') ON DUPLICATE KEY UPDATE `synced`=VALUES(`synced`)";
        full_query($query);

    }

    private function deleteUserSyncEntry ($userID) {

        $userID = (int) $userID;

        $query = "DELETE FROM `mod_easywi_user_synced` WHERE `user_id`={$userID} LIMIT 1";
        full_query($query);
    }

    public function localClientDetails ($userID) {

        $table = "tblclients";
        $fields = "id,firstname,lastname,companyname,email,address1,address2,city,state,postcode,country,phonenumber,status,language";
        $where = array("id" => (int) $userID);

        $result = select_query($table, $fields, $where);
        while ($data = mysql_fetch_assoc($result)) {

            $data["userid"] = $data["id"];

            return $data;
        }

        return false;
    }

    public function searchLocalClientByMail ($userMail) {

        $usersFound = array();

        if (!filter_var($userMail, FILTER_VALIDATE_EMAIL))  {
            return $usersFound;
        }

        $table = "tblclients";
        $fields = "id,firstname,lastname,companyname,email,address1,address2,city,state,postcode,country,phonenumber,status,language";
        $where = array("email" => $userMail);

        $result = select_query($table, $fields, $where);
        while ($data = mysql_fetch_assoc($result)) {
            $usersFound[] = $data;
        }

        return $usersFound;
    }

    public function addLocalUser ($data) {

        $table = "tblclients";

        $values = array(
            "status" => ($data["active"] == "Y") ? "Active" : "Inactive",
            "country" => strtoupper($data[""]),
            "firstname" => $data["firstName"],
            "lastname" => $data["lastName"],
            "email" => $data["mail"],
            "address1" => $data["address1"],
            "city" => $data["city"],
            "postcode" => $data["postcode"],
            "phonenumber" => $data["phone"]
        );

        return insert_query($table, $values);
    }

    private function apiUserCrud ($action, $vars, $identifyByEmail = false, $showOnlyUserDetails = false) {

        /*
         $vars:
            userid
            firstname
            lastname
            companyname
            email
            address1
            address2
            city
            state
            postcode
            country
            phonenumber
            password
         */

        $passwordMod = ($action == "password") ? true : false;
        $action = ($action == "password") ? "mod" : $action;


        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("users");

        $key = $postxml->createElement("action", $action);
        $element->appendChild($key);

        if (isset($vars["EasyWiID"])) {

            $key = $postxml->createElement("identify_by", "localid");
            $element->appendChild($key);

            $key = $postxml->createElement("localid", $vars["EasyWiID"]);
            $element->appendChild($key);

        } else if ($identifyByEmail == true) {

            $key = $postxml->createElement("identify_by", "email");
            $element->appendChild($key);

        } else {

            $key = $postxml->createElement("identify_by", "external_id");
            $element->appendChild($key);
        }

        if (($action == "add" && isset($vars["password"])) || ($passwordMod && isset($vars["password"]))) {
            $key = $postxml->createElement("password", $vars["password"]);
            $element->appendChild($key);
        }

        $key = $postxml->createElement("external_id", $this->idPrefix . ":" . $vars["userid"]);
        $element->appendChild($key);

        $key = $postxml->createElement("email", (isset($vars["email"])) ? $vars["email"] : "");
        $element->appendChild($key);

        $key = $postxml->createElement("show_user_data_only",  ($showOnlyUserDetails == true) ? 1 : 0);
        $element->appendChild($key);

        if ($action != "del" && $action != "ls" && $action != "clean") {

            $active = ($vars["status"] == "Inactive" || $vars["status"] == "Closed") ? "N" : "Y";

            $key = $postxml->createElement("active", $active);
            $element->appendChild($key);

            $key = $postxml->createElement("vname", $vars["firstname"]);
            $element->appendChild($key);

            $key = $postxml->createElement("name", $vars["lastname"]);
            $element->appendChild($key);

            // WHMCS stores street + number so we need to split
            $exploded = preg_split('/\s/', $vars["address1"], -1, PREG_SPLIT_NO_EMPTY);
            $lastKey = count($exploded) - 1;

            if ($lastKey > -1 && isset($exploded[$lastKey])) {

                $key = $postxml->createElement("streetn", $exploded[$lastKey]);
                $element->appendChild($key);

                unset($exploded[$lastKey]);
            }

            $key = $postxml->createElement("street", implode(" ", $exploded));
            $element->appendChild($key);

            $key = $postxml->createElement("city", $vars["city"]);
            $element->appendChild($key);

            $key = $postxml->createElement("cityn", $vars["postcode"]);
            $element->appendChild($key);

            $key = $postxml->createElement("country", strtolower($vars["country"]));
            $element->appendChild($key);
        }

        $postxml->appendChild($element);

        return array("type" => "user", "xml" => $postxml->saveXML());
    }

    private function apiListUsers ($requesOptions) {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("server");

        $key = $postxml->createElement("action", "ls");
        $element->appendChild($key);

        $key = $postxml->createElement("start", $requesOptions["start"]);
        $element->appendChild($key);

        $key = $postxml->createElement("amount", $requesOptions["amount"]);
        $element->appendChild($key);

        if (!$requesOptions["showSynced"]) {
            $key = $postxml->createElement("notLike", $this->idPrefix);
            $element->appendChild($key);
        }

        if (!$requesOptions["showNotSynced"]) {
            $key = $postxml->createElement("like", $this->idPrefix);
            $element->appendChild($key);
        }

        $postxml->appendChild($element);

        return array("type" => "user", "xml" => $postxml->saveXML());
    }

    private function apiUserSyncByMail ($vars) {

        $responseRaw = $this->apiCall($this->apiUserCrud("ls", $vars, true));
        $response = @simplexml_load_string($responseRaw);

        $this->addLogentry($vars, $response, $responseRaw);

        // If everything worked as expected there should be no error returned at this point.
        // But as bugs might happen we need to check and catch it anyway.
        if ($response->errors != "") {

            $this->addUserSyncEntry($vars["userid"], "N");

            return false;

        // Sync succeded at some point but was not stored in WHMCS custom table. We need to add sync flag.
        } else if ($response->errors == "" && $response->externalID == $this->idPrefix . ":" . $vars["userid"]) {

            $this->addUserSyncEntry($vars["userid"], "Y");

            return true;

        // Last case maps user by email and sets the externalID at Easy-WI.
        } else if ($response->errors == "" && $response->externalID == "") {

            $responseRaw = $this->apiCall($this->apiUserCrud("mod", $vars, true));
            $response = @simplexml_load_string($responseRaw);

            $this->addLogentry($vars, $response, $responseRaw);

            if (($response->errors != "")) {

                $this->addUserSyncEntry($vars["userid"], "N");

                return false;

            } else {

                $this->addUserSyncEntry($vars["userid"], "Y");

                return true;
            }

        }

        return false;
    }

    public function changePassword ($vars, $forceSend = false) {

        /*
         $vars:
            userid
            password
         */

        if ($this->syncPasswords == "Yes" || $forceSend == true) {

            $userSync = $this->isUserSynced($vars["userid"]);

            $userDetails = $this->localClientDetails($vars["userid"]);

            if (isset($userDetails["userid"])) {

                $userDetails["password"] = $vars["password"];

                if ($userSync == "Y") {

                    return $this->modUser($userDetails, $forceSend, false, true);

                } else if ($userSync == "N" && $this->syncUsers == "Yes") {

                    return $this->addUser($userDetails, $forceSend);
                }
            }
        } else {

            $this->addLogentry(array("syncPasswords" => $this->syncPasswords));

            return true;
        }

        return false;
    }

    public function addUser ($vars, $provisioning = false, $forceProvisioning = false) {

        $userSync = $this->isUserSynced($vars["userid"]);

        if (!isset($vars["firstname"])) {
            $vars = $this->localClientDetails($vars["userid"]);
        }

        if ($forceProvisioning || ($userSync == "N" && ($this->syncUsers == "Yes" || $provisioning !== false))) {

            $responseRaw = $this->apiCall($this->apiUserCrud("add", $vars));
            $response = @simplexml_load_string($responseRaw);

            $this->addLogentry($vars, $response, $responseRaw);

            if ($forceProvisioning && is_object($response) && strlen($response->errors) > 1) {
                return true;
            }

            if (is_object($response) && strpos($response->errors, "user with this e-mail already exists") !== false && $this->mapByEmail == "Yes") {

                return $this->apiUserSyncByMail($vars);

            } else if (is_object($response) && strlen($response->errors) > 1) {

                $this->addUserSyncEntry($vars["userid"], "N");

                return false;

            } else {

                $this->addUserSyncEntry($vars["userid"], "Y");

                return true;
            }

        } else if ($userSync == "N" && $this->syncUsers != "Yes" && $provisioning !== true && $forceProvisioning !== false) {
            return false;
        }

        return true;
    }

    public function cleanExternUserID ($externalID) {

        $responseRaw = $this->apiCall($this->apiUserCrud("clean", array("userid" => $externalID)));
        $response = @simplexml_load_string($responseRaw);

        $this->addLogentry(array("action" => "externID cleanup", "externalID" => $externalID), $response, $responseRaw);
    }

    public function modUser ($vars, $provisioning = false, $EasyWiID = false, $password = false) {

        $userSync = $this->isUserSynced($vars["userid"]);

        if (!isset($vars["firstname"])) {
            $vars = $this->localClientDetails($vars["userid"]);
        }

        if ($EasyWiID != false) {
            $vars["EasyWiID"] = $EasyWiID;
        }

        if (($userSync == "Y" && $this->syncUsers == "Yes") || $provisioning == true) {

            $action = ($password) ? "password" : "mod";

            $responseRaw = $this->apiCall($this->apiUserCrud($action, $vars));
            $response = @simplexml_load_string($responseRaw);

            $this->addLogentry($vars, $response, $responseRaw);

            if (strpos($response->errors, "No user can be found to edit") !== false) {
                return $this->addUser($vars);
            }

            return $response;

        } else if ($this->syncUsers == "Yes") {

            return $this->addUser($vars);

        } else {
            $this->addLogentry(array("action" => "modify", "userSync" => $userSync, "syncUsers" => $this->syncUsers));
        }

        return array("syncStatus" => "Not syncing", "userSync" => $userSync, "syncUsers" => $this->syncUsers);
    }

    public function removeUser ($vars) {

        /*
         $vars:
            userid
         */
        $userSync = $this->isUserSynced($vars["userid"]);

        if ($this->syncUsers == "Yes" && $userSync != "Y") {
            $userSync = ($this->addUser($vars)) ? "Y" : "N";
        }

        if ($userSync == "Y") {

            if ($this->removeUsers == "Yes") {

                $responseRaw = $this->apiCall($this->apiUserCrud("del", $vars));

            } else {
                $vars = $this->localClientDetails($vars["userid"]);
                $vars["status"] = "Inactive";

                $responseRaw = $this->apiCall($this->apiUserCrud("mod", $vars));
            }

            $response = @simplexml_load_string($responseRaw);

            $this->addLogentry($vars, $response, $responseRaw);
        } else {
            logActivity("User sync is maintained as {$userSync}. User removal/inactive state will not be send to Easy-Wi");
        }

        if ($this->removeUsers != "Yes" || $userSync != "Y") {
            $this->addLogentry(array("action" => "delete", "userSync" => $userSync, "syncUsers" => $this->syncUsers));
        }

        $this->deleteUserSyncEntry($vars["userid"]);

        return (isset($response) && $response->errors != "") ? $response->errors: true;
    }

    public function getUserList ($requesOptions) {

        $users = array("userList" => array());

        $responseRaw = $this->apiCall($this->apiListUsers($requesOptions));
        $response = @simplexml_load_string($responseRaw);


        if (is_object($response)) {

            $this->addLogentry(array("type" => "listUsers"), $response);

            $users["totalAmount"] = (int) $response->totalAmount;
            $users["start"] = (int) $response->start;
            $users["amount"] = (int) $response->amount;
            $users["like"] = (string) $response->like;
            $users["notLike"] = (string) $response->notLike;

            foreach ($response->user as $user) {
                $users["userList"][] = array(
                    "id" => (int) $user->id,
                    "active" => (string) $user->active,
                    "cname" => (string) $user->cname,
                    "mail" => (string) $user->mail,
                    "firstName" => (string) $user->vname,
                    "lastName" => (string) $user->name,
                    "city" => (string) $user->city,
                    "postcode" => (string) $user->cityn,
                    "address1" => (string) $user->street . ' ' . $user->cityn,
                    "language" => (string) $user->language,
                    "phone" => (string) $user->phone,
                    "externalID" => (string) $user->externalID
                );
            }

            return $users;

        }

        $this->addLogentry(array("type" => "listUsers"), $responseRaw);

        return 'Raw response: ' . $responseRaw;
    }

    public function getUserDetails ($userID, $email = false) {

        $userList = array();

        $responseRaw = $this->apiCall($this->apiUserCrud("ls", array("userid" => $userID), false, true));
        $response = @simplexml_load_string($responseRaw);

        $this->addLogentry(array("type" => "getUserDetails"), $response);

        if (is_object($response) && $response->errors == "") {

            $EasyWiID = (int) $response->userdetails->id;

            $userList[$EasyWiID] = array(
                "id" => (int) $response->userdetails->id,
                "active" => (string) $response->userdetails->active,
                "loginname" => (string) $response->userdetails->cname,
                "lastname" => (string) $response->userdetails->name,
                "firstname" => (string) $response->userdetails->vname,
                "email" => (string) $response->userdetails->mail,
                "whmcsID" => (int) $userID,
            );
        }

        if ($email != false &&(!isset($userList["id"]["mail"]) || $userList["id"]["mail"] != $email)) {

            $responseRaw = $this->apiCall($this->apiUserCrud("ls", array("userid" => $userID, "email" => $email), true, true));
            $response = @simplexml_load_string($responseRaw);

            $this->addLogentry(array("type" => "getUserDetails"), $response);

            if (is_object($response) && $response->errors == "") {

                $EasyWiID = (int) $response->userdetails->id;

                @list($prefix, $whmcsID) = explode(":", $response->userdetails->externalID);

                $whmcsID = ($this->idPrefix == $prefix && is_numeric($whmcsID)) ? (int) $whmcsID : "";

                $userList[$EasyWiID] = array(
                    "id" => $EasyWiID,
                    "active" => (string) $response->userdetails->active,
                    "loginname" => (string) $response->userdetails->cname,
                    "lastname" => (string) $response->userdetails->name,
                    "firstname" => (string) $response->userdetails->vname,
                    "email" => (string) $response->userdetails->mail,
                    "whmcsID" => $whmcsID,
                );
            }
        }

        return $userList;
    }

    public function getUserServers ($userID) {


        $responseRaw = $this->apiCall($this->apiUserCrud("ls", array("userid" => $userID)));
        $response = @simplexml_load_string($responseRaw);

        $this->addLogentry(array("type" => "readUser"), $response);

        if (is_object($response)) {

            $servers = array(
                "gserver" => array(),
                "voice" => array(),
                "tsdns" => array(),
                "mysql" => array(),
                "webspace" => array()
            );

            foreach ($response->gserver as $serverList) {
                foreach ($serverList as $server) {

                    $cpu = count(preg_split('/,/', $server->cores, -1, PREG_SPLIT_NO_EMPTY));
                    $cpu = ($server->taskset == "Y" && $cpu > 0) ? $cpu : '';

                    $servers["gserver"][] = array(
                        "id" => (int) $server->id,
                        "active" => (string) $server->active,
                        "protected" => (string) $server->pallowed,
                        "brandname" => (string) $server->brandname,
                        "tvenable" => (string) $server->tvenable,
                        "private" => (string) $server->war,
                        "ip" => (string) $server->serverip,
                        "port" => (string) $server->port,
                        "slots" => (string) $server->slots,
                        "ram" => (string) $server->maxram,
                        "cpu" => $cpu,
                        "taskset" => (string) $server->taskset,
                        "shorten" => (string) $server->shorten,
                        "externalID" => (string) $server->externalID
                    );
                }
            }

            foreach ($response->voice as $serverList) {
                foreach ($serverList as $server) {
                    $servers["voice"][] = array(
                        "id" => (int) $server->id,
                        "active" => (string) $server->active,
                        "dns" => (string) $server->dns,
                        "ip" => (string) $server->ip,
                        "port" => (string) $server->port,
                        "slots" => (string) $server->slots,
                        "private" => (string) $server->password,
                        "forcebanner" => (string) $server->forcebanner,
                        "forcebutton" => (string) $server->forcebutton,
                        "brandname" => (string) $server->forceservertag,
                        "forcewelcome" => (string) $server->forcewelcome,
                        "traffic" => (string) $server->maxtraffic,
                        "externalID" => (string) $server->externalID
                    );
                }
            }

            foreach ($response->tsdns as $serverList) {
                foreach ($serverList as $server) {
                    $servers["tsdns"][] = array(
                        "id" => (int) $server->dnsID,
                        "active" => (string) $server->active,
                        "dns" => (string) $server->dns,
                        "ip" => (string) $server->ip,
                        "port" => (string) $server->port,
                        "externalID" => (string) $server->externalID
                    );
                }
            }

            foreach ($response->mysql as $serverList) {
                foreach ($serverList as $server) {
                    $servers["mysql"][] = array(
                        "id" => (int) $server->id,
                        "active" => (string) $server->active,
                        "dbname" => (string) $server->dbname,
                        "externalID" => (string) $server->externalID
                    );
                }
            }

            foreach ($response->webspace as $serverList) {
                foreach ($serverList as $server) {
                    $servers["webspace"][] = array(
                        "id" => (int) $server->webVhostID,
                        "active" => (string) $server->active,
                        "hdd" => (string) $server->hdd,
                        "dns" => (string) $server->dns,
                        "externalID" => (string) $server->externalID
                    );
                }
            }

            return $servers;
        }

        return 'Raw response: ' . $responseRaw;

    }

    // User specific methods end here.

    // Voice server specific methods begin here.
    private function apiVoiceServerCrud ($userID, $productID, $action, $options, $easyWiID = false) {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("voice");

        $key = $postxml->createElement("action", $action);
        $element->appendChild($key);

        $key = $postxml->createElement("identify_user_by", "user_externalid");
        $element->appendChild($key);

        $key = $postxml->createElement("user_externalid", $this->idPrefix . ":" . $userID);
        $element->appendChild($key);

        // Required attributes keys which we won´t use. They can be empty

        $key = $postxml->createElement("user_localid", "");
        $element->appendChild($key);

        $key = $postxml->createElement("username", "");
        $element->appendChild($key);
        // Empty required ends here

        if ($easyWiID == false) {

            $key = $postxml->createElement("server_local_id", "");
            $element->appendChild($key);

            $key = $postxml->createElement("identify_server_by", "server_external_id");
            $element->appendChild($key);

        } else {

            $key = $postxml->createElement("server_local_id", $easyWiID);
            $element->appendChild($key);

            $key = $postxml->createElement("identify_server_by", "server_local_id");
            $element->appendChild($key);
        }

        $key = $postxml->createElement("server_external_id", $this->idPrefix . ":" . $productID);
        $element->appendChild($key);

        $key = $postxml->createElement("active", $this->whmcsStatusToEasyWiStatus($options["action"]));
        $element->appendChild($key);

        foreach ($options["rootIDs"] as $rootID) {
            $key = $postxml->createElement("master_server_id", $rootID);
            $element->appendChild($key);
        }

        # will be definde by the masterserver if set to Y
        $key = $postxml->createElement("tsdns", "Y");
        $element->appendChild($key);

        # Required but currently without function
        $key = $postxml->createElement("shorten", "ts3");
        $element->appendChild($key);

        $key = $postxml->createElement("private", $this->yesNo($options["private"]));
        $element->appendChild($key);

        $key = $postxml->createElement("maxtraffic", $options["traffic"]);
        $element->appendChild($key);

        $key = $postxml->createElement("slots", $options["slots"]);
        $element->appendChild($key);

        $key = $postxml->createElement("forceservertag", $this->yesNo($options["brandname"]));
        $element->appendChild($key);

        $key = $postxml->createElement("forcebanner",$this->yesNo($options["forcebanner"]));
        $element->appendChild($key);

        $key = $postxml->createElement("forcebutton",$this->yesNo($options["forcebutton"]));
        $element->appendChild($key);

        $key = $postxml->createElement("forcewelcome",$this->yesNo($options["forcewelcome"]));
        $element->appendChild($key);

        $postxml->appendChild($element);

        return array("type" => "voice", "xml" => $postxml->saveXML());
    }
    // Voice server specific methods begin here.

    // TSDNS specific methods begin here.

    private function apiListTSDNS () {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("server");

        $key = $postxml->createElement("action", "ls");
        $element->appendChild($key);

        $postxml->appendChild($element);

        return array("type" => "tsdns", "xml" => $postxml->saveXML());
    }

    public function getTSDNSMasterList () {
        $responseRaw = $this->apiCall($this->apiListTSDNS());
        return @simplexml_load_string($responseRaw);
    }

    private function apiTSDNSCrud ($userID, $productID, $action, $options, $easyWiID = false) {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("tsdns");

        $key = $postxml->createElement("action", $action);
        $element->appendChild($key);

        $key = $postxml->createElement("identify_user_by", "user_externalid");
        $element->appendChild($key);

        $key = $postxml->createElement("user_externalid", $this->idPrefix . ":" . $userID);
        $element->appendChild($key);

        // Required attributes keys which we won´t use. They can be empty
        $key = $postxml->createElement("user_localid", "");
        $element->appendChild($key);

        $key = $postxml->createElement("username", "");
        $element->appendChild($key);
        // Empty required ends here

        if ($easyWiID == false) {

            $key = $postxml->createElement("server_local_id", "");
            $element->appendChild($key);

            $key = $postxml->createElement("identify_server_by", "server_external_id");
            $element->appendChild($key);

        } else {

            $key = $postxml->createElement("server_local_id", $easyWiID);
            $element->appendChild($key);

            $key = $postxml->createElement("identify_server_by", "server_local_id");
            $element->appendChild($key);
        }

        $key = $postxml->createElement("server_external_id", $this->idPrefix . ":" . $productID);
        $element->appendChild($key);

        $key = $postxml->createElement("active", $this->whmcsStatusToEasyWiStatus($options["action"]));
        $element->appendChild($key);

        $key = $postxml->createElement("dns", (isset($options["dns"])) ? $options["dns"] : '');
        $element->appendChild($key);

        $key = $postxml->createElement("ip", (isset($options["ip"])) ? $options["ip"] : '');
        $element->appendChild($key);

        $key = $postxml->createElement("port", (isset($options["port"])) ? $options["port"] : '');
        $element->appendChild($key);

        foreach ($options["rootIDs"] as $rootID) {
            $key = $postxml->createElement("master_server_id", $rootID);
            $element->appendChild($key);
        }

        $postxml->appendChild($element);

        return array("type" => "tsdns", "xml" => $postxml->saveXML());
    }

    // TSDNS specific methods methods end here.

    // Webspace specific methods begin here.

    private function apiListWeb () {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("server");

        $key = $postxml->createElement("action", "ls");
        $element->appendChild($key);

        $postxml->appendChild($element);

        return array("type" => "web", "xml" => $postxml->saveXML());
    }

    public function getWebspaceMasterList () {
        $responseRaw = $this->apiCall($this->apiListWeb());
        return @simplexml_load_string($responseRaw);
    }

    private function apiWebspaceCrud ($userID, $productID, $action, $options, $easyWiID = false) {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("web");

        $key = $postxml->createElement("action", $action);
        $element->appendChild($key);

        $key = $postxml->createElement("identify_user_by", "user_externalid");
        $element->appendChild($key);

        $key = $postxml->createElement("user_externalid", $this->idPrefix . ":" . $userID);
        $element->appendChild($key);

        // Required attributes keys which we won´t use. They can be empty

        $key = $postxml->createElement("user_localid", "");
        $element->appendChild($key);

        $key = $postxml->createElement("username", "");
        $element->appendChild($key);
        // Empty required ends here

        if ($easyWiID == false) {

            $key = $postxml->createElement("server_local_id", "");
            $element->appendChild($key);

            $key = $postxml->createElement("identify_server_by", "server_external_id");
            $element->appendChild($key);

        } else {

            $key = $postxml->createElement("server_local_id", $easyWiID);
            $element->appendChild($key);

            $key = $postxml->createElement("identify_server_by", "server_local_id");
            $element->appendChild($key);
        }

        $key = $postxml->createElement("server_external_id", $this->idPrefix . ":" . $productID);
        $element->appendChild($key);

        $key = $postxml->createElement("active", $this->whmcsStatusToEasyWiStatus($options["action"]));
        $element->appendChild($key);

        foreach ($options["rootIDs"] as $rootID) {
            $key = $postxml->createElement("master_server_id", $rootID);
            $element->appendChild($key);
        }

        $key = $postxml->createElement("ownVhost", "N");
        $element->appendChild($key);

        $key = $postxml->createElement("dns", (isset($options["dns"])) ? $options["dns"] : '');
        $element->appendChild($key);

        $key = $postxml->createElement("hdd", (isset($options["hdd"]) and ((int) $options["hdd"]) > 0) ? $options["hdd"] : '');
        $element->appendChild($key);

        if (isset($options["password"])) {
            $key = $postxml->createElement("password", $options["password"]);
            $element->appendChild($key);
        }

        $postxml->appendChild($element);

        return array("type" => "web", "xml" => $postxml->saveXML());
    }
    // Webspace specific methods end begin here.

    // MySQL specific methods begin here.

    private function apiListMySQL () {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("server");

        $key = $postxml->createElement("action", "ls");
        $element->appendChild($key);

        $postxml->appendChild($element);

        return array("type" => "mysql", "xml" => $postxml->saveXML());
    }

    public function getMySQLMasterList () {
        $responseRaw = $this->apiCall($this->apiListMySQL());
        return @simplexml_load_string($responseRaw);
    }

    private function apiMySQLCrud ($userID, $productID, $action, $options, $easyWiID = false) {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("web");

        $key = $postxml->createElement("action", $action);
        $element->appendChild($key);

        $key = $postxml->createElement("identify_user_by", "user_externalid");
        $element->appendChild($key);

        $key = $postxml->createElement("user_externalid", $this->idPrefix . ":" . $userID);
        $element->appendChild($key);

        // Required attributes keys which we won´t use. They can be empty

        $key = $postxml->createElement("user_localid", "");
        $element->appendChild($key);

        $key = $postxml->createElement("username", "");
        $element->appendChild($key);
        // Empty required ends here

        if ($easyWiID == false) {

            $key = $postxml->createElement("server_local_id", "");
            $element->appendChild($key);

            $key = $postxml->createElement("identify_server_by", "server_external_id");
            $element->appendChild($key);

        } else {

            $key = $postxml->createElement("server_local_id", $easyWiID);
            $element->appendChild($key);

            $key = $postxml->createElement("identify_server_by", "server_local_id");
            $element->appendChild($key);
        }

        $key = $postxml->createElement("server_external_id", $this->idPrefix . ":" . $productID);
        $element->appendChild($key);

        $key = $postxml->createElement("active", $this->whmcsStatusToEasyWiStatus($options["action"]));
        $element->appendChild($key);

        foreach ($options["rootIDs"] as $rootID) {
            $key = $postxml->createElement("master_server_id", $rootID);
            $element->appendChild($key);
        }

        $key = $postxml->createElement("manage_host_table", $this->yesNo($options["private"]));
        $element->appendChild($key);

        $postxml->appendChild($element);

        return array("type" => "mysql", "xml" => $postxml->saveXML());
    }
    // MySQL specific methods end begin here.


    // Game server specific methods begin here.

    private function apiGameServerCrud ($userID, $productID, $action, $options, $easyWiID = false) {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("gserver");

        $key = $postxml->createElement("action", $action);
        $element->appendChild($key);

        $key = $postxml->createElement("identify_user_by", "user_externalid");
        $element->appendChild($key);

        $key = $postxml->createElement("user_externalid", $this->idPrefix . ":" . $userID);
        $element->appendChild($key);

        // Required attributes keys which we won´t use. They can be empty

        $key = $postxml->createElement("user_localid", "");
        $element->appendChild($key);

        $key = $postxml->createElement("username", "");
        $element->appendChild($key);

        // Empty required ends here

        if ($easyWiID == false) {

            $key = $postxml->createElement("server_local_id", "");
            $element->appendChild($key);

            $key = $postxml->createElement("identify_server_by", "server_external_id");
            $element->appendChild($key);

        } else {

            $key = $postxml->createElement("server_local_id", $easyWiID);
            $element->appendChild($key);

            $key = $postxml->createElement("identify_server_by", "server_local_id");
            $element->appendChild($key);
        }

        $key = $postxml->createElement("server_external_id", $this->idPrefix . ":" . $productID);
        $element->appendChild($key);

        if (isset($options["password"])) {
            $key = $postxml->createElement("initialpassword", $options["password"]);
            $element->appendChild($key);
        }

        foreach ($options["gameShortens"] as $shorten) {
            $key = $postxml->createElement("shorten", $shorten);
            $element->appendChild($key);
        }

        if (strlen($options["preInstalled"]) > 0) {

            $key = $postxml->createElement("primary", $options["preInstalled"]);
            $element->appendChild($key);

            $key = $postxml->createElement("installGames", "P");
            $element->appendChild($key);

        } else {
            $key = $postxml->createElement("installGames", "N");
            $element->appendChild($key);
        }

        $key = $postxml->createElement("slots", $options["slots"]);
        $element->appendChild($key);

        $key = $postxml->createElement("home_label", $options["homelabel"]);
        $element->appendChild($key);

        $key = $postxml->createElement("hdd", (isset($options["hdd"]) and ((int) $options["hdd"]) > 0) ? $options["hdd"] : 0);
        $element->appendChild($key);

        $key = $postxml->createElement("private", $this->yesNo($options["private"]));
        $element->appendChild($key);

        $key = $postxml->createElement("active", $this->whmcsStatusToEasyWiStatus($options["action"]));
        $element->appendChild($key);

        foreach ($options["rootIDs"] as $rootID) {
            $key = $postxml->createElement("master_server_id", $rootID);
            $element->appendChild($key);
        }

        if ($options["cpu"] > 0) {

            $key = $postxml->createElement("taskset", "Y");
            $element->appendChild($key);

            $key = $postxml->createElement("coreCount", $options["cpu"]);
            $element->appendChild($key);

        } else {
            $key = $postxml->createElement("taskset", "N");
            $element->appendChild($key);
        }

        $key = $postxml->createElement("eacallowed", $this->yesNo($options["eac"]));
        $element->appendChild($key);

        $key = $postxml->createElement("brandname", $this->yesNo($options["brandname"]));
        $element->appendChild($key);

        $key = $postxml->createElement("tvenable", $this->yesNo($options["tvEnable"]));
        $element->appendChild($key);

        if ($options["ram"] > 0) {
            $key = $postxml->createElement("minram", round($options["ram"] / 4));
            $element->appendChild($key);

            $key = $postxml->createElement("maxram", $options["ram"]);
            $element->appendChild($key);
        }

        $postxml->appendChild($element);

        return array("type" => "gserver", "xml" => $postxml->saveXML());
    }

     // Game server specific methods end here.

    public function configOptionsOverwrite ($vars) {

        $this->optionsRaw = $vars;
        /*
            "configoption1":"type",
            "configoption2":"slots(int)",
            "configoption3":"cpu(int)",
            "configoption4":"ram(int)",
            "configoption5":"hdd(int)",
            "configoption6":"traffic(int)",
            "configoption7":"bindRoot(on)",
            "configoption8":"RootIDs(csv)",
            "configoption9":"private(onoff)",
            "configoption10":"brandname(onoff)",
            "configoption11":"shortens(csv)",
            "configoption12":"preInstalled(string)",
            "configoption13":"tvEnable(onoff)",
            "configoption14":"protected(onoff)",
            "configoption15":"eac(onoff)",
            "configoption16":"empty",
            "configoption17":"forcebanner(onoff)",
            "configoption18":"forcebutton(onoff)",
            "configoption19":"forcewelcome(onoff)",
            "configoption20":"homelabel(string)",
            "configoptions":{"Slots":"14","CPU":"2","Ram":"1024"} (Only an example as individual and because of it checks needed)
        */

        $configOptions = array_change_key_case($vars["configoptions"], CASE_LOWER);

        $table = "mod_easywi_options_name_alias";
        $fields = "id,technical_name,alias";

        $result = select_query($table, $fields, array());
        while ($row = mysql_fetch_assoc($result)) {

            $keyNameWHMCSConfigured = strtolower($row["alias"]);
            $keyNameTechnical = strtolower($row["technical_name"]);

            if (isset($configOptions[$keyNameWHMCSConfigured])) {

                if (isset($configOptions[$keyNameWHMCSConfigured]) and strlen($configOptions[$keyNameWHMCSConfigured]) > 0) {
                    $configOptions[$keyNameTechnical] = $configOptions[$keyNameWHMCSConfigured];
                }

                $result2 = select_query("mod_easywi_value_name_alias", "technical_name,alias", array("option_alias_id" => $row["id"]));

                while ($row2 = mysql_fetch_assoc($result2)) {
                    if (strtolower($row2["alias"]) == strtolower($configOptions[$keyNameWHMCSConfigured])) {
                        $configOptions[$keyNameTechnical] = strtolower($row2["technical_name"]);
                    }
                }
            }
        }

        $rootIDs = ($vars["configoption7"] == "on") ? preg_split('/,/', $vars["configoption8"], -1, PREG_SPLIT_NO_EMPTY) : array();

        $options = array(
            "action" => $vars["action"],
            "type" => $vars["configoption1"],
            "slots" => (isset($configOptions["slots"]) and $configOptions["slots"] != "") ? $configOptions["slots"] : $vars["configoption2"],
            "cpu" => (isset($configOptions["cpu"]) and $configOptions["cpu"] != "") ? $configOptions["cpu"]: $vars["configoption3"],
            "ram" => (isset($configOptions["ram"]) and $configOptions["ram"] != "") ? $configOptions["ram"] : $vars["configoption4"],
            "hdd" => (isset($configOptions["hdd"]) and $configOptions["hdd"] != "") ? $configOptions["hdd"] : $vars["configoption5"],
            "traffic" => (isset($configOptions["traffic"]) and $configOptions["traffic"] != "") ? $configOptions["traffic"] : $vars["configoption6"],
            "bindRoot" => $vars["configoption7"],
            "rootIDs" => $rootIDs,
            "private" => (isset($configOptions["private"]) and $configOptions["private"] != "") ? $configOptions["private"] : $vars["configoption9"],
            "brandname" => (isset($configOptions["brandname"]) and $configOptions["brandname"] != "") ? $configOptions["brandname"] : $vars["configoption10"],
            "gameShortens" => preg_split('/,/', $vars["configoption11"], -1, PREG_SPLIT_NO_EMPTY),
            "preInstalled" => (isset($configOptions["preinstalled"]) and $configOptions["preinstalled"] != "") ? $configOptions["preinstalled"] : $vars["configoption12"],
            "tvEnable" => (isset($configOptions["tvEnable"]) and $configOptions["tvEnable"] != "") ? $configOptions["tvEnable"] : $vars["configoption13"],
            "protected" => (isset($configOptions["protected"]) and $configOptions["protected"] != "") ? $configOptions["protected"] : $vars["configoption14"],
            "eac" => (isset($configOptions["eac"]) and $configOptions["eac"] != "") ? $configOptions["eac"] : $vars["configoption15"],
            "forcebanner" => (isset($configOptions["forcebanner"]) and $configOptions["forcebanner"] != "") ? $configOptions["forcebanner"] : $vars["configoption17"],
            "forcebutton" => (isset($configOptions["forcebutton"]) and $configOptions["forcebutton"] != "") ? $configOptions["forcebutton"] : $vars["configoption18"],
            "forcewelcome" => (isset($configOptions["forcewelcome"]) and $configOptions["forcewelcome"] != "") ? $configOptions["forcewelcome"] : $vars["configoption19"],
            "homelabel" => (isset($configOptions["homelabel"]) and $configOptions["homelabel"] != "") ? $configOptions["homelabel"] : $vars["configoption20"]
        );

        if ($this->whmcsActionToEasyWiAction($vars["action"]) == "add" && isset($vars["password"]) && strlen($vars["password"]) > 0) {

            $options["password"] = $vars["password"];

        } else if ($this->syncPasswords == "Yes" && isset($vars["password"]) && strlen($vars["password"]) > 0) {

            $values["password2"] = $vars["password"];

            $result = localAPI("decryptpassword", $values, "admin");

            if ($result["result"] == "success") {
                $options["password"] = $result["password"];
            }
        }

        return $options;
    }

    public function getMasterList ($types) {

        $serverList = array(
            "types" => '',
            "games" => array(),
            "gameServer" => array(),
            "mysqlServer" => array(),
            "tsdnsServer" => array(),
            "voiceServer" => array(),
            "webspaceServer" => array()
        );

        $responseRaw = $this->apiCall($this->apiListAll($types));
        $response = @simplexml_load_string($responseRaw);


        if (is_object($response)) {

            $this->addLogentry(array("type" => "listAll"), $response);

            $serverList['types'] = (string) $response->types;

            foreach ($response->server->gameServer as $v) {

                $gamesArray = array();

                foreach ((array) $v->gamesavailable as $k => $v2) {
                    $serverList["games"][$k] = (string) $v2;
                    $gamesArray[] = (string) $k;
                }

                natsort($gamesArray);

                $serverList["gameServer"][] = array(
                    "id" => (int) $v->id,
                    "ip" => (string) $v->ip,
                    "description" => (string) $v->description,
                    "maxserver" => (int) $v->maxserver,
                    "maxslots" => (int) $v->maxslots,
                    "installedserver" => (int) $v->installedserver,
                    "installedslots" => (int) $v->installedslots,
                    "games" => (string) implode(", ", $gamesArray),
                );
            }

            foreach ($response->server->mysqlServer as $v) {
                $serverList["mysqlServer"][] = array(
                    "id" => (int) $v->id,
                    "ip" => (string) $v->ssh2ip,
                    "description" => (string) $v->description,
                    "maxDBs" => (int) $v->maxDBs,
                    "dbsInstalled" => (int) $v->dbsInstalled
                );
            }

            foreach ($response->server->tsdnsServer as $v) {
                $serverList["tsdnsServer"][] = array(
                    "id" => (int) $v->id,
                    "ip" => (string) $v->ssh2ip,
                    "description" => (string) $v->description,
                    "maxDNS" => (int) $v->maxDNS,
                    "installedDNS" => (int) $v->installedDNS
                );
            }

            foreach ($response->server->voiceServer as $v) {
                $serverList["voiceServer"][] = array(
                    "id" => (int) $v->id,
                    "ip" => (string) $v->ssh2ip,
                    "description" => (string) $v->description,
                    "maxserver" => (int) $v->maxserver,
                    "maxslots" => (int) $v->maxslots,
                    "installedserver" => (int) $v->installedserver,
                    "installedslots" => (int) $v->installedslots
                );
            }

            foreach ($response->server->webspaceServer as $v) {
                $serverList["webspaceServer"][] = array(
                    "id" => (int) $v->id,
                    "ip" => (string) $v->ssh2ip,
                    "description" => (string) $v->description,
                    "maxVhost" => (int) $v->maxVhost,
                    "maxHDD" => (int) $v->maxHDD,
                    "installedVhosts" => (int) $v->installedVhosts,
                    "hddUsage" => (int) $v->hddUsage
                );
            }
        } else {

            $this->addLogentry(array("type" => "listAll"), $responseRaw);

            return 'Raw response: ' . $responseRaw;
        }

        return $serverList;
    }

    // Product specific methods begin here.
    public function getProductsByType ($type) {

        $productList = array();

        $typeTableMapping = array("gserver" => "Game server", "voice" => "Voice server", "tsdns" => "TSDNS", "webspace" => "Webspace", "mysql" => "MySQL");

        if (isset($typeTableMapping[$type])) {

            $table = "tblproducts";
            $fields = "id,name,configoption11";
            $where = array(
                "configoption1" => $typeTableMapping[$type],
                "retired" => 0
            );

            $result = select_query($table, $fields, $where);

            while ($data = mysql_fetch_assoc($result)) {
                $productList[$data["id"]] = array("name" => $data["name"], "shorten" => preg_split("/,/", $data["configoption11"], -1, PREG_SPLIT_NO_EMPTY));
            }
        }

        return (count($productList) > 0) ? $productList : false;
    }

    // Product specific methods end here.

    // Order specific methods begin here.
    public function orderNumExists ($orderNum) {

        $table = "tblorders";
        $fields = "id";
        $where = array("ordernum" => (int) $orderNum);
        $result = select_query($table, $fields, $where);

        $data = mysql_fetch_assoc($result);

        return (isset($data["id"]) && $data["id"] > 0) ? true : false;
    }

    public function getUserOrdersById ($userID, $productID) {

        $userID = (int) $userID;
        $productID = (int) $productID;

        $orders = array();

        $query = "SELECT h.`id`,p.`name`,h.`orderid` FROM `tblhosting` AS h INNER JOIN `tblproducts` AS p ON p.`id`=h.`packageid` LEFT JOIN `mod_easywi_service_synced` AS s ON s.`user_id`=h.`userid` AND s.`service_id`=h.`orderid` WHERE h.`userid`={$userID} AND h.`packageid`={$productID} AND (s.`synced`='N' OR s.`synced` IS NULL)";
        $result = full_query($query);
        while ($row = mysql_fetch_assoc($result)) {

            $orderExtraNameDetails = array();

            // Get custom fields if available
            // Allowed are ip, port, dns, name
            $query2 = "SELECT v.`value`,LOWER(f.`fieldname`) FROM `tblcustomfieldsvalues` AS v INNER JOIN `tblcustomfields` AS f ON f.`id`=v.`fieldid` WHERE v.`relid`={$row['id']}";
            $result2 = full_query($query2);
            while ($row2 = mysql_fetch_assoc($result2)) {
                $orderExtraNameDetails[$row2["fieldname"]] = $row2["value"];
            }

            $extraName = "";

            if (count($orderExtraNameDetails) == 0) {

                // speaking name existing, port and ip are likely additional information
                if (isset($orderExtraNameDetails["dns"]) || isset($orderExtraNameDetails["name"]))  {

                    $extraName = (isset($orderExtraNameDetails["dns"])) ? " {$orderExtraNameDetails['dns']}" : " {$orderExtraNameDetails['name']}";

                    // IP and port are found
                    if (isset($orderExtraNameDetails["ip"]) && isset($orderExtraNameDetails["port"])) {
                        $extraName .= " ({$orderExtraNameDetails['ip']}:{$orderExtraNameDetails['port']})";

                    // Only an ip can be found
                    } else if (isset($orderExtraNameDetails["ip"])) {
                        $extraName .= " ({$orderExtraNameDetails['ip']})";
                    }

                // Fall back to IP and maybe port
                } else if (isset($orderExtraNameDetails["ip"])) {
                    $extraName = " {$orderExtraNameDetails['ip']}";
                }
            }

            $orders[$row["id"]] = "{$row["name"]} (service ID {$row["id"]} order ID {$row["orderid"]}){$extraName}";
        }

        return $orders;
    }

    public function isServiceSynced ($userID, $serviceID) {

        $userID = (int) $userID;
        $serviceID = (int) $serviceID;

        $table = "mod_easywi_service_synced";
        $fields = "synced";
        $where = array(
            "user_id" => $userID,
            "service_id" => $serviceID
        );
        $join = "tblhosting ON tblhosting.id=mod_easywi_service_synced.service_id";

        $result = select_query($table, $fields, $where, "", "", "", $join);
        $data = mysql_fetch_assoc($result);

        return ($data["synced"] == "Y") ? "Y" : "N";
    }

    public function mapWHMCSServiceToEasyWi($serviceID, $easyWiID) {

        $vars = $this->getServiceDetailsById($serviceID);

        $vars["action"] = "upgrade";

        $options = $this->configOptionsOverwrite($vars);

        return $this->productCrud($vars["userid"], $serviceID, $options["type"], $vars["action"], $options, "Y", $easyWiID);
    }

    public function addServiceSyncEntry ($userID, $serviceID, $status) {
        $query = "INSERT INTO `mod_easywi_service_synced` (`user_id`,`service_id`,`synced`) VALUES ({$userID},{$serviceID},'{$status}') ON DUPLICATE KEY UPDATE `synced`=VALUES(`synced`)";
        full_query($query);
    }

    private function deleteServiceSyncEntry ($userID, $serviceID) {
        $query = "DELETE FROM `mod_easywi_service_synced` WHERE `user_id`={$userID} AND `service_id`={$serviceID} LIMIT 1";
        full_query($query);
    }

    private function whmcsActionToEasyWiAction ($action) {

        /*
            "action":"create"
	        "action":"upgrade"
	        "action":"suspend"
            "action":"unsuspend"
            "action":"terminate"
            "action":"fraud"
            "action":"pending"
         */

        if ($action == "create") {
            return "add";
        } else if ($action == "terminate") {
            return "del";
        } else if ($action == "fraud") {
            return ($this->stateFraud == "Delete") ? "del" : "mod";
        } else if ($action == "pending") {
            return ($this->statePending == "Delete") ? "del" : "mod";
        } else {
            return "mod";
        }
    }

    public function easyWiActiveToWHMCS ($status) {
        return ($status == "Y") ? "Yes" : "No";
    }

    private function whmcsStatusToEasyWiStatus ($action) {

        /*
            "action":"create"
	        "action":"upgrade"
	        "action":"suspend"
            "action":"unsuspend"
            "action":"terminate"
         */

        return ($action == "suspend" || $action == "fraud" || $action == "pending") ? "N" : "Y";

    }

    private function globalToSpecificCrud ($userID, $serviceID, $type, $action, $options, $easyWiID = false) {

        if ($type == "Game server") {
            $xml = $this->apiGameServerCrud($userID, $serviceID, $action, $options, $easyWiID);
        } else if ($type == "Voice server") {
            $xml = $this->apiVoiceServerCrud($userID, $serviceID, $action, $options, $easyWiID);
        } else if ($type == "TSDNS") {
            $xml = $this->apiTSDNSCrud($userID, $serviceID, $action, $options, $easyWiID);
        } else if ($type == "Webspace") {
            $xml = $this->apiWebspaceCrud($userID, $serviceID, $action, $options, $easyWiID);
        } else if ($type == "MySQL") {
            $xml = $this->apiMySQLCrud($userID, $serviceID, $action, $options, $easyWiID);
        }

        if (isset($xml)) {
            return array('xml' => $xml, 'return' => $this->apiCall($xml));
        }

        return "No Type specified";
    }

    private function productCrud ($userID, $serviceID, $type, $action, $options, $synced, $easyWiID = false, $retry = false) {

        $actionConverted = $this->whmcsActionToEasyWiAction($action);

        if ($synced == "Y" || $actionConverted == "add" || $actionConverted == "del") {

            $responseRaw = $this->globalToSpecificCrud($userID, $serviceID, $type, $actionConverted, $options, $easyWiID);
            $response = @simplexml_load_string($responseRaw['return']);

            $this->addLogentry(array("userid" => $userID, "serviceid" => $serviceID, "action" => $action, "options" => $options, "synced" => $synced, 'xml' => $responseRaw['xml']), ($response && is_object($response)) ? $response : $responseRaw['return']);

            if (is_object($response) && $actionConverted == "add" && strpos($response->errors, "server with external ID already exists") !== false && $easyWiID == false) {

                $responseRaw = $this->globalToSpecificCrud($userID, $serviceID, $type, "mod", $options);
                $response = @simplexml_load_string($responseRaw['return']);

                $this->addLogentry(array("userid" => $userID, "serviceid" => $serviceID, "action" => "mod", "options" => $options, "synced" => $synced, 'xml' => $responseRaw['xml']), ($response && is_object($response)) ? $response : $responseRaw['return']);

            } else if (is_object($response) && $actionConverted == "mod" && strpos($response->errors, "No server can be found to edit") !== false && $easyWiID == false) {

                $responseRaw = $this->globalToSpecificCrud($userID, $serviceID, $type, "add", $options);
                $response = @simplexml_load_string($responseRaw['return']);

                $this->addLogentry(array("userid" => $userID, "serviceid" => $serviceID, "action" => "add", "options" => $options, "synced" => $synced, 'xml' => $responseRaw['xml']), ($response && is_object($response)) ? $response : $responseRaw['return']);
            }

            if (!is_object($response) || (is_object($response) && $response->errors != "") || $actionConverted == "del") {

                $this->deleteServiceSyncEntry($userID, $serviceID);

            } else if (is_object($response) && $response->errors == "" && $actionConverted != "del") {

                $this->addServiceSyncEntry ($userID, $serviceID, "Y");

                // Update WHMCS details with Easy-Wi details

                $customAttributes = array();

                if (strlen($response->serverName) > 1 || strlen($response->address) > 1) {

                    @list($ip, $port) = explode(":", (strlen($response->serverName) > 1) ? $response->serverName : $response->address);

                    $customAttributes["ip"] = $ip;
                    $customAttributes["port"] = $port;
                }

                if (strlen($response->ip) > 1) {
                    $customAttributes["ip"] = (string) $response->ip;
                }

                if (strlen($response->port) > 1) {
                    $customAttributes["port"] = (string) $response->port;
                }

                if (strlen($response->dns) > 1) {
                    $customAttributes["dns"] = (string) $response->dns;
                }

                if (strlen($response->dbname) > 1) {
                    $customAttributes["name"] = (string) $response->dbname;
                }

                $this->fillCustomAttributes($serviceID, $customAttributes);
            }

            if (is_object($response->errors) && $response->errors != "")  {
                $errors = (string) $response->errors;
            } else if (!is_object($response->errors)) {
                $errors = "No Easy-Wi API response";
            } else {
                $errors = false;
            }

            if ($errors !== false and strpos($errors, "user does not exist") !== false) {

                $this->deleteUserSyncEntry($userID);

                if ($retry !== true && $actionConverted != "del") {

                    $sync = ($this->syncUsers == "Yes") ? true : false;

                    $this->addUser($this->localClientDetails($userID), $sync, true);

                    return $this->productCrud($userID, $serviceID, $type, $action, $options, $synced, $easyWiID, true);
                }
            }

            return ($errors) ? $errors : true;
        }

        $this->addLogentry(array("userid" => $userID, "serviceid" => $serviceID, "action" => $action, "options" => $options, "synced" => $synced), "API not triggered. IF case was: (${synced} == 'Y' || {$actionConverted} == 'add' || {$actionConverted} == 'del'");

        if ($actionConverted != "del") {

            $this->addServiceSyncEntry($userID, $serviceID, "N");

            return "Unknown provision error. Action was ${$action}/${actionConverted}";
        }

        $this->deleteServiceSyncEntry($userID, $serviceID);

        return true;
    }

    public function fillCustomAttributes ($serviceID, $attributes) {

        $serviceID = (int) $serviceID;

        // Get product ID
        $query = "SELECT `packageid` FROM `tblhosting` WHERE `id`={$serviceID} LIMIT 1";
        $result = full_query($query);
        $data = mysql_fetch_assoc($result);
        $productID = $data["packageid"];

        // Check if custom columns are defined > tblcustomfields
        $query = "SELECT `id`,LOWER(`fieldname`) AS `name` FROM `tblcustomfields` WHERE `relid`={$productID} AND `type`='product'";
        $result = full_query($query);
        while ($row = mysql_fetch_assoc($result)) {

            // If given for upsert, check if custom columns exist > tblcustomfieldsvalues
            if (isset($attributes[$row["name"]])) {

                $query2 = "SELECT COUNT(1) AS `amount` FROM `tblcustomfieldsvalues` WHERE `relid`='{$serviceID}' AND `fieldid`='{$row['id']}' LIMIT 1";
                $result2 = full_query($query2);
                $data = mysql_fetch_assoc($result2);

                $table = "tblcustomfieldsvalues";

                if ($data["amount"] > 0) {

                    $update = array(
                        "value" => $attributes[$row['name']]
                    );

                    $where = array(
                        "fieldid" => $row['id'],
                        "relid" => $serviceID
                    );

                    update_query($table, $update, $where);

                } else {

                    $values = array(
                        "fieldid" => $row['id'],
                        "relid" => $serviceID,
                        "value" => $attributes[$row['name']]
                    );

                    insert_query($table, $values);
                }
            }
        }
    }

    public function getCustomServiceFields ($serviceID) {

        $customFields = array();

        $serviceID = (int) $serviceID;

        $query = "SELECT f.`fieldname`,f.`description`,v.`value` FROM `tblcustomfieldsvalues` AS v INNER JOIN `tblcustomfields` AS f ON f.`id`=v.`fieldid` WHERE v.`relid`='{$serviceID}' ORDER BY f.`sortorder` ASC";
        $result = full_query($query);
        while ($row = mysql_fetch_assoc($result)) {
            $customFields[$row["fieldname"]] = array(
                "description" => $row["description"],
                "value" => $row["value"],
            );
        }

        return $customFields;
    }

    public function getServiceDetailsById ($serviceID) {

        $configOptions = array();

        $query = "SELECT h.`userid`,h.`orderid`,h.`domainstatus`,p.* FROM `tblhosting` AS h INNER JOIN `tblproducts` AS p ON p.`id` = h.`packageid` WHERE h.`id`={$serviceID} LIMIT 1";
        $result = full_query($query);
        $vars = mysql_fetch_assoc($result);

        $query = "SELECT o.`optionname` AS `attrName`,u.`qty` AS `value` FROM `tblhostingconfigoptions` AS u LEFT JOIN `tblproductconfigoptions` AS o ON o.`id`=u.`optionid` WHERE u.`relid`={$serviceID} AND `qty`>0 UNION SELECT o.`optionname` AS `attrName`,s.`optionname` AS `value` FROM `tblhostingconfigoptions` AS u LEFT JOIN `tblproductconfigoptionssub` AS s ON s.`id`=u.`optionid` LEFT JOIN `tblproductconfigoptions` AS o ON o.`id`=s.`configid` WHERE u.`relid`={$serviceID} AND `qty`=0";
        $result = full_query($query);
        while ($row = mysql_fetch_assoc($result)) {
            $configOptions[$row["attrName"]] = $row["value"];
        }

        $vars["configoptions"] = $configOptions;

        return $vars;
    }

    private function getRelevantOrderCount ($userID, $serviceID) {

        $query = "SELECT COUNT(h.`id`) AS `amount` FROM `tblhosting` AS h INNER JOIN `tblorders` AS o ON o.`id` = h.`orderid` INNER JOIN `tblproducts` AS p ON p.`id` = h.`packageid` WHERE h.`userid`={$userID} AND h.`id`!={$serviceID} AND o.`status` IN ('Active','Suspended') AND p.`servertype`='easywi'";
        $result = full_query($query);
        $data = mysql_fetch_assoc($result);

        return $data["amount"];
    }

    public function orderProvision ($vars, $hookAction = false) {

        // when triggered from hooks we might have either order or service id but not both. In that case we need to retrieve the other
        if (!isset($vars["serviceid"]) && isset($vars["orderid"])) {

            $returns = array();

            $table = "tblhosting";
            $fields = "id";
            $where = array("orderid" => $vars["orderid"]);

            $result = select_query($table, $fields, $where);

            while ($row = mysql_fetch_assoc($result)) {

                $return = $this->orderInternalProcess(array("serviceid" => $row["id"]), $hookAction);

                if (!$return) {
                    $returns[] = $return;
                }
            }

            return (count($returns) == 0) ? true : implode(", ", $returns);
        }

        return $this->orderInternalProcess($vars, $hookAction);
    }

    private function orderInternalProcess ($vars, $hookAction = false) {

        $serviceID = (int) $vars["serviceid"];

        # Due to multiple hooks etc., we need to check if this is in fact Easy-Wi provisioning. If not, abort.
        $query = "SELECT p.`servertype` FROM `tblhosting` AS h INNER JOIN `tblproducts` AS p ON p.`id`=h.`packageid` WHERE h.`id`='{$serviceID}'";
        $result = full_query($query);
        $data = mysql_fetch_assoc($result);

        if (isset($data["servertype"]) && $data["servertype"] == "easywi") {

            if ($hookAction == false and isset($vars["userid"])) {

                $action = $this->whmcsActionToEasyWiAction($vars["action"]);

                $userSynced = $this->addUser($vars["clientsdetails"], ($action != "del" || $this->syncUsers == "Yes") ? true : false);

            } else {

                $vars = $this->getServiceDetailsById($serviceID);

                if (in_array($hookAction, array("fraud", "pending", "suspend", "terminate"))) {
                    $vars["action"] = $hookAction;
                } else if ($hookAction == "mod" && isset($vars["domainstatus"]) && $vars["domainstatus"] == "Pending") {
                    $vars["action"] = ($this->statePending == "Inactive") ? "upgrade" : "terminate";
                } else if ($hookAction == "mod" && isset($vars["domainstatus"]) && $vars["domainstatus"] == "Fraud") {
                    $vars["action"] = ($this->stateFraud == "Inactive") ? "upgrade" : "terminate";
                } else {
                    $vars["action"] = (isset($vars["domainstatus"]) && $vars["domainstatus"] != "Active") ? "terminate" : "upgrade";
                }


                $action = $this->whmcsActionToEasyWiAction($vars["action"]);
                #logActivity("hockAction:$hookAction; Action:$action; vars:".json_encode($vars));

                $userID = (int) $vars["userid"];

                if ($hookAction != "terminate") {

                    $userSynced = $this->addUser($this->localClientDetails($userID), ($action != "del" || $this->syncUsers == "Yes") ? true : false);
                } else {
                    $userSynced =  $this->isUserSynced($userID);
                    $userSynced = ($userSynced == "Y") ? true : false;
                }
            }

            $userID = (int) $vars["userid"];

            $productSynced = $this->isServiceSynced($userID, $serviceID);

            if ($productSynced == "N" and $action == "mod") {
                $action = "add";
            }
            #logActivity(json_encode($vars).'; Action:'.$action.'; userSynced:'.$userSynced.'; producsynced:'.$productSynced);

            if ($action == "del") {

                $this->deleteServiceSyncEntry($userID, $serviceID);

                $leftOrders = $this->getRelevantOrderCount($userID, $serviceID);

                if ($leftOrders == 0) {
                    return $this->removeUser($this->localClientDetails($userID));
                } else {
                    logActivity("{$leftOrders} order(s) left. User with ID {$userID} will not be removed from Easy-Wi after service ID {$serviceID} is gone.");
                }
            }


            if ($action == "del" || ($userSynced && ($action == "add" || $action == "mod"))) {

                $options = $this->configOptionsOverwrite($vars);
                #logActivity("Options:".json_encode($options));

                # allowed list is "Game server,Voice server,TSDNS,Webspace,MySQL"
                if (isset($options["type"]) and in_array($options["type"], array("Game server", "Voice server", "TSDNS", "Webspace", "MySQL")))  {

                    return $this->productCrud($userID, $serviceID, $options["type"], $vars["action"], $options, $productSynced);

                } else {

                    $this->addServiceSyncEntry ($userID, $serviceID, 'N');

                    if (isset($options["type"])) {
                        return "Incorrect Type: {$options['type']}";
                    } else {
                        return "Incorrect options: " . json_encode($options['type']);
                    }
                }
            }

            if (!$userSynced && $productSynced == "Y" && $vars["action"] == "terminate") {

                $this->deleteServiceSyncEntry($userID, $serviceID);

                return true;
            }

            return "User is not synced!";
        }

        return false;
    }

    // Lending API related Methods will start here

    // abstract general methods
    private function lendApiCall ($type, $action = false, $ip = false, $vars = false) {

        $postfields = array(
            "user" => $this->user,
            "pwd" => $this->password,
            "xml" => 1,
            "w" => $type
        );

        if ($action == "ipStatus" && $ip != false) {
            $postfields["ipblocked"] = base64_encode($this->lendIpKnownXML($ip));
        } else if ($action == "lend" && $ip != false && $vars != false) {
            $postfields["game"] = base64_encode($this->lendlendXML($ip, $type, $vars));
        }

        $url = (substr($this->url, 0, -1) == "/") ? $this->url . "lend.php" : $this->url . "/lend.php";

        $curlResponse = $this->curlCall($url, $postfields, array());

        $this->addLogentry($vars, false, $curlResponse);

        return $curlResponse;
    }

    private function lendIpKnownXML ($ip) {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("startserver");

        $key = $postxml->createElement("userip", $ip);
        $element->appendChild($key);

        $postxml->appendChild($element);

        return $postxml->saveXML();
    }

    public function lendIpUsed ($type, $action, $ip) {

        $returned = $this->lendApiCall($type, $action, $ip);

        $this->addLogentry(array(), false, $returned);

        if ($returned == "notblocked") {
            return false;
        }

        return @simplexml_load_string($returned);
    }

    public function lendOverallStatus ($type) {
        return @simplexml_load_string($this->lendApiCall($type));
    }

    private function lendlendXML ($ip, $type, $vars) {

        $postxml = new DOMDocument("1.0", "utf-8");
        $element = $postxml->createElement("startserver");

        $key = $postxml->createElement("userip", $ip);
        $element->appendChild($key);

        $key = $postxml->createElement("slots", $vars["slots"]);
        $element->appendChild($key);

        $key = $postxml->createElement("lendtime", $vars["lendtime"]);
        $element->appendChild($key);

        $key = $postxml->createElement("password", $vars["password"]);
        $element->appendChild($key);

        if ($type == "gs") {

            $key = $postxml->createElement("rcon", $vars["rcon"]);
            $element->appendChild($key);

            $key = $postxml->createElement("game", $vars["game"]);
            $element->appendChild($key);

            $key = $postxml->createElement("ftpuploadpath", "");
            $element->appendChild($key);
        }

        $postxml->appendChild($element);

        return $postxml->saveXML();
    }

    public function lendServer($type, $ip, $vars) {
        return $this->lendApiCall($type, "lend", $ip, $vars);
    }

    public function protectionApiCall($ip, $port) {

        $requestString = (substr($this->url, 0, -1) == "/") ? $this->url : $this->url . "/";
        $requestString .= "protectioncheck.php?ip={$ip}&po={$port}&gamestring=xml";
        $apiResponse = $this->curlCall($requestString, array(), array());
        $this->addLogentry(array(), false, $apiResponse);

        return @simplexml_load_string($apiResponse);
    }

    public function passwordGenerate ($length = 10) {

        $zeichen = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 1, 2, 3, 4, 5, 6, 7, 8, 9);
        $anzahl = count($zeichen) - 1;
        $password = '';

        for($i = 1; $i <= $length; $i++){
            $wuerfeln = mt_rand(0, $anzahl);
            $password .= $zeichen[$wuerfeln];
        }

        return $password;
    }

    public function checkForUpdate() {

        $apiResponse = $this->curlCall("https://api.github.com/repos/easy-wi/whmcs/releases/latest", array(), array());
        $decoded = @json_decode($apiResponse);

        if (!$decoded || !$decoded->tag_name || version_compare($decoded->tag_name, $this->versionFile) != 1) {
            return null;
        }

        return $decoded->tag_name;
    }
}