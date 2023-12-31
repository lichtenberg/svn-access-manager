<?php

/**
 * bnulk add ldap users
 *
 * @author Thomas Krieger
 * @copyright 2008-2018 Thomas Krieger. All rights reserved.
 *           
 *            SVN Access Manager - a subversion access rights management tool
 *            Copyright (C) 2008-2018 Thomas Krieger <tom@svn-access-manager.org>
 *           
 *            This program is free software; you can redistribute it and/or modify
 *            it under the terms of the GNU General Public License as published by
 *            the Free Software Foundation; either version 2 of the License, or
 *            (at your option) any later version.
 *           
 *            This program is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU General Public License for more details.
 *           
 *            You should have received a copy of the GNU General Public License
 *            along with this program; if not, write to the Free Software
 *            Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *           
 *
 */

/*
 *
 * $LastChangedDate: 2018-05-30 20:16:19 +0200 (Wed, 30 May 2018) $
 * $LastChangedBy: tom_krieger $
 *
 * $Id: bulk_add_ldap_users.php 430 2018-05-30 18:16:19Z tom_krieger $
 *
 */
include ('load_config.php');

$installBase = isset($CONF[INSTALLBASE]) ? $CONF[INSTALLBASE] : "";

require ("$installBase/include/variables.inc.php");
include_once ("$installBase/include/constants.inc.php");
require_once ("$installBase/include/functions.inc.php");
require_once ("$installBase/include/db-functions-adodb.inc.php");
include_once ("$installBase/include/output.inc.php");

initialize_i18n();

$SESSID_USERNAME = check_session();
check_password_expired();
$dbh = db_connect();
$preferences = db_get_preferences($SESSID_USERNAME, $dbh);
$CONF[PAGESIZE] = $preferences[PAGESIZE];
$CONF[TOOLTIP_SHOW] = $preferences[TOOLTIP_SHOW];
$CONF[TOOLTIP_HIDE] = $preferences[TOOLTIP_HIDE];
$rightAllowed = db_check_acl($SESSID_USERNAME, 'User admin', $dbh);
$_SESSION[SVNSESSID]['helptopic'] = "bulkaddldapusers";
$tDisabled = "";

if ($rightAllowed == "none") {
    db_log($SESSID_USERNAME, "tried to use bulk_add_ldap_users without permission", $dbh);
    db_disconnect($dbh);
    header("Location: nopermission.php");
    exit();
}

if (isset($CONF['userDefaultAccess'])) {
    $tUserRight = $CONF['userDefaultAccess'];
}
else {
    $tUserRight = "read";
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    
    $tUsersLdap = get_ldap_users();
    $tUsers = array();
    foreach( $tUsersLdap as $user ) {
        
        if (db_getIdByUserid($user['uid'], $dbh)) {
            // user already exists, nothing to import
        }
        else {
            
            $entry = array();
            $entry[USERID] = $user['uid'];
            $entry['name'] = $user['name'];
            $entry[GIVENNAME] = $user[GIVENNAME];
            $entry[EMAILADDRESS] = $user[EMAILADDRESS];
            $entry['selected'] = 1;
            $entry['added'] = 0;
            $tUsers[$entry[USERID]] = $entry;
        }
    }
    
    $_SESSION[SVNSESSID][BULKADDLIST] = $tUsers;
    
    $template = "bulk_add_ldap_users.tpl";
    $header = USERS;
    $subheader = USERS;
    $menu = USERS;
    
    include ("$installBase/templates/framework.tpl");
    
    db_disconnect($dbh);
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    
    $tToAdd = isset($_POST['fToAdd']) ? db_escape_string($_POST['fToAdd'], $dbh) : array();
    $tUserRight = isset($_POST['fUserRight']) ? db_escape_string($_POST['fUserRight'], $dbh) : "read";
    
    if (isset($_POST['fSubmit'])) {
        $button = db_escape_string($_POST['fSubmit']);
    }
    elseif ((isset($_POST['fSubmit_new_x'])) || (isset($_POST['fSubmit_new']))) {
        $button = _("Submit");
    }
    elseif ((isset($_POST['fSubmit_back_x'])) || (isset($_POST['fSubmit_back']))) {
        $button = _("Back");
    }
    else {
        $button = "undef";
    }
    
    if ($button == _("Back")) {
        
        db_disconnect($dbh);
        header("Location: list_users.php");
        exit();
    }
    elseif ($button == _("Submit")) {
        
        $schema = db_determine_schema();
        $tPassword = db_escape_string(pacrypt('changeme'), $dbh);
        db_ta("BEGIN", $dbh);
        foreach( $tToAdd as $i => $userid ) {
            
            $datenow = db_now();
            $entry = $_SESSION[SVNSESSID][BULKADDLIST][$userid];
            $query = "INSERT INTO " . $schema . "svnusers (userid, name, givenname, password, passwordexpires, locked, emailaddress, admin, user_mode, created, created_user, password_modified, superadmin) " . "     VALUES ('" . $entry[USERID] . "', '" . $entry['name'] . "', '" . $entry[GIVENNAME] . "', '$tPassword', 1, 0 ,'" . $entry[EMAILADDRESS] . "', 'n', '$tUserRight', $datenow, '" . $_SESSION[SVNSESSID]['username'] . "', '20000101000000', 0)";
            $result = db_query($query, $dbh);
            db_log($_SESSION[SVNSESSID]['username'], "added user " . $entry[USERID] . ", " . $entry['name'] . ", " . $entry[GIVENNAME], $dbh);
            
            $_SESSION[SVNSESSID][BULKADDLIST][$userid]['added'] = 1;
        }
        
        db_ta("COMMIT", $dbh);
        
        $tDisabled = "disabled=disabled";
    }
    else {
        
        $tMessage = sprintf(_("Invalid button %s, anyone tampered arround with?"), $button);
        $tMessageType = DANGER;
    }
    
    $tUsers = $_SESSION[SVNSESSID][BULKADDLIST];
    
    $template = "bulk_add_ldap_users.tpl";
    $header = USERS;
    $subheader = USERS;
    $menu = USERS;
    
    include ("$installBase/templates/framework.tpl");
    
    db_disconnect($dbh);
}

?>