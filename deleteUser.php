<?php

/**
 * delete an user
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
 * $Id: deleteUser.php 430 2018-05-30 18:16:19Z tom_krieger $
 *
 */
include ('load_config.php');

$installBase = isset($CONF[INSTALLBASE]) ? $CONF[INSTALLBASE] : "";

require ("$installBase/include/variables.inc.php");
include_once ("$installBase/include/constants.inc.php");
require ("$installBase/include/functions.inc.php");
require ("$installBase/include/output.inc.php");
require ("$installBase/include/db-functions-adodb.inc.php");

initialize_i18n();

$SESSID_USERNAME = check_session();
check_password_expired();
$dbh = db_connect();
$preferences = db_get_preferences($SESSID_USERNAME, $dbh);
$CONF[PAGESIZE] = $preferences[PAGESIZE];
$CONF[TOOLTIP_SHOW] = $preferences[TOOLTIP_SHOW];
$CONF[TOOLTIP_HIDE] = $preferences[TOOLTIP_HIDE];
$rightAllowed = db_check_acl($SESSID_USERNAME, "User admin", $dbh);
$_SESSION[SVNSESSID]['helptopic'] = "deleteuser";
$schema = db_determine_schema();

if ($rightAllowed != "delete") {
    
    db_log($SESSID_USERNAME, "tried to use deleteUser without permission", $dbh);
    db_disconnect($dbh);
    header("Location: nopermission.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    
    $tTask = db_escape_string($_GET['task']);
    if (isset($_GET['id'])) {
        
        $tId = db_escape_string($_GET['id']);
    }
    else {
        
        $tId = "";
    }
    
    $_SESSION[SVNSESSID]['task'] = strtolower($tTask);
    $_SESSION[SVNSESSID][USERID] = $tId;
    
    if ($_SESSION[SVNSESSID]['task'] == "delete") {
        
        $query = "SELECT * " . "  FROM " . $schema . "svnusers " . " WHERE id = $tId";
        $result = db_query($query, $dbh);
        
        if ($result['rows'] == 1) {
            
            $row = db_assoc($result['result']);
            $tUserid = $row[USERID];
            $tName = $row['name'];
            $tGivenname = $row['givenname'];
            $tEmail = $row['emailaddress'];
            $tPasswordExpires = $row['passwordexpires'];
            $tLocked = $row['locked'];
            $tAdministrator = $row['admin'];
            $tExpire = ($tPasswordExpires == 0) ? _("no") : _("yes");
            $tLockedValue = ($tLocked == 0) ? _("no") : _("yes");
            $tAdminValue = ($tAdministrator == "n") ? _("no") : _("yes");
        }
        else {
            
            $tMessage = sprintf(_("Invalid userid %s requested!"), $id);
            $tMessageType = DANGER;
        }
    }
    else {
        
        $tMessage = sprintf(_("Invalid task %s, anyone tampered arround with?"), $_SESSION[SVNSESSID]['task']);
    }
    
    $header = USERS;
    $subheader = USERS;
    $menu = USERS;
    $template = "deleteUser.tpl";
    
    include ("$installBase/templates/framework.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    
    if (isset($_POST['fSubmit'])) {
        $button = db_escape_string($_POST['fSubmit']);
    }
    elseif ((isset($_POST['fSubmit_ok_x'])) || (isset($_POST['fSubmit_ok']))) {
        $button = _("Delete");
    }
    elseif ((isset($_POST['fSubmit_back_x'])) || (isset($_POST['fSubmit_back']))) {
        $button = _("Back");
    }
    else {
        $button = "undef";
    }
    
    if ($button == _("Delete")) {
        
        $userid = db_getUseridById($_SESSION[SVNSESSID][USERID], $dbh);
        $error = 0;
        $dbnow = db_now();
        $query = "UPDATE " . $schema . "svnusers " . "   SET deleted = '$dbnow', " . "       deleted_user = '" . $_SESSION[SVNSESSID][USERNAME] . "' " . " WHERE id = " . $_SESSION[SVNSESSID][USERID] . "   AND (deleted = '00000000000000')";
        
        db_ta('BEGIN', $dbh);
        db_log($_SESSION[SVNSESSID][USERNAME], "deleted user $userid", $dbh);
        
        $result = db_query($query, $dbh);
        
        if ($result['rows'] == 1) {
            
            $dbnow = db_now();
            $query = "UPDATE " . $schema . "svn_access_rights " . "   SET deleted = '$dbnow', " . "       deleted_user = '" . $_SESSION[SVNSESSID][USERNAME] . "' " . " WHERE (user_id = " . $_SESSION[SVNSESSID][USERID] . ") " . "   AND (deleted = '00000000000000')";
            $result = db_query($query, $dbh);
            
            if ($result['rows'] < 0) {
                
                $error = 1;
            }
        }
        
        if ($error == 0) {
            
            $dbnow = db_now();
            $query = "UPDATE " . $schema . "svn_projects_responsible " . "   SET deleted = '$dbnow', " . "       deleted_user = '" . $_SESSION[SVNSESSID][USERNAME] . "' " . " WHERE user_id = " . $_SESSION[SVNSESSID][USERID] . "   AND (deleted = '00000000000000')";
            $result = db_query($query, $dbh);
            
            if ($result['rows'] < 0) {
                
                $error = 1;
            }
        }
        
        if ($error == 0) {
            
            $dbnow = db_now();
            $query = "UPDATE " . $schema . "svn_users_groups " . "   SET deleted = '$dbnow', " . "       deleted_user = '" . $_SESSION[SVNSESSID][USERNAME] . "' " . " WHERE user_id = " . $_SESSION[SVNSESSID][USERID] . "   AND (deleted = '00000000000000')";
            $result = db_query($query, $dbh);
            
            if ($result['rows'] < 0) {
                
                $error = 1;
            }
        }
        
        if ($error == 0) {
            
            db_ta('COMMIT', $dbh);
            $tMessage = _("User successfully deleted");
            $tMessageType = SUCCESS;
            
            db_disconnect($dbh);
            
            header("Location: list_users.php");
            exit();
        }
        else {
            
            db_ta('ROLLBACK', $dbh);
            
            if ($_SESSION[SVNSESSID][USERID] != '') {
                $query = "SELECT * " . "  FROM " . $schema . "svnusers " . " WHERE id = " . $_SESSION[SVNSESSID][USERID];
                $result = db_query($query, $dbh);
                
                if ($result['rows'] == 1) {
                    
                    $row = db_assoc($result['result']);
                    $tUserid = $row[USERID];
                    $tName = $row['name'];
                    $tGivenname = $row['givenname'];
                    $tEmail = $row['emailaddress'];
                    $tPasswordExpires = $row['passwordexpires'];
                    $tLocked = $row['locked'];
                    $tAdministrator = $row['admin'];
                    $tExpire = (tPasswordExpires == 0) ? _("no") : _("yes");
                    $tLockedValue = ($tLocked == 0) ? _("no") : _("yes");
                    $tAdminValue = ($tAdministrator == "n") ? _("no") : _("yes");
                }
                else {
                    
                    $tUserid = '';
                    $tName = '';
                    $tGivenname = '';
                    $tEmail = '';
                    $tPasswordExpires = '';
                    $tLocked = '';
                    $tAdministrator = '';
                    $tExpire = '';
                    $tLockedValue = '';
                    $tAdminValue = '';
                    
                    $tMessage = sprintf(_("Invalid userid %s requested!"), $id);
                    $tMessageType = DANGER;
                }
            }
            
            $tMessage = _("User not deleted due to database errors");
            $tMessageType = DANGER;
        }
    }
    elseif ($button == _("Back")) {
        
        db_disconnect($dbh);
        header("Location: list_users.php");
        exit();
    }
    else {
        
        $tMessage = _("Invalid button $button, anyone tampered arround with?");
        $tMessageType = DANGER;
    }
    
    $header = USERS;
    $subheader = USERS;
    $menu = USERS;
    $template = "deleteUser.tpl";
    
    include ("$installBase/templates/framework.tpl");
}

db_disconnect($dbh);
?>
