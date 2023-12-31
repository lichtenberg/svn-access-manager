<?php

/**
 * login to SVN Access manager page
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
 * $Id: login.php 430 2018-05-30 18:16:19Z tom_krieger $
 *
 */
include ('load_config.php');

$installBase = isset($CONF['install_base']) ? $CONF['install_base'] : "";

require ("$installBase/include/variables.inc.php");
include_once ("$installBase/include/constants.inc.php");
require ("$installBase/include/db-functions-adodb.inc.php");
require ("$installBase/include/functions.inc.php");

initialize_i18n();

$dbh = db_connect();
$_SESSION[SVNSESSID]['helptopic'] = "login";
$schema = db_determine_schema();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    
    $tUsername = "";
    
    include ("$installBase/templates/login.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    
    $error = 0;
    $fUsername = db_escape_string($_POST['fUsername']);
    $fPassword = db_escape_string($_POST['fPassword']);
    $tPasswordExpired = 0;
    $result = db_query("SELECT password " . "  FROM " . $schema . "svnusers " . " WHERE (userid = '$fUsername')" . "   AND (deleted = '00000000000000')", $dbh);
    
    if ($result['rows'] == 1) {
        
        if ((isset($CONF[USE_LDAP])) && (strtoupper($CONF[USE_LDAP]) == "YES")) {
            
            $ldapres = check_ldap_password($fUsername, $fPassword);
            if ($ldapres == 1) {
                $result = db_query("SELECT * " . "  FROM " . $schema . "svnusers " . " WHERE (userid = '$fUsername') " . "   AND (deleted = '00000000000000')", $dbh);
            }
            elseif ($ldapres == - 1) {
                
                $error = 1;
                $tMessage = _("LDAP server not reachable!");
                $tUsername = $fUsername;
            }
            else {
                
                $error = 1;
                $tMessage = _('Username and/or password wrong');
                $tUsername = $fUsername;
            }
        }
        else {
            
            $row = db_assoc($result['result']);
            $password = addslashes(pacrypt($fPassword, $row['password']));
            $result = db_query("SELECT * " . "  FROM " . $schema . "svnusers " . " WHERE (userid = '$fUsername') " . "   AND (password = '$password')", $dbh);
        }
        
        if (($error == 0) && ($result['rows'] != 1)) {
            
            $error = 1;
            $tMessage = _('Username and/or password wrong');
            $tUsername = $fUsername;
        }
        
        if ($error == 0) {
            
            $row = db_assoc($result['result']);
            $id = $row['id'];
            $tName = $row['name'];
            $tGivenname = $row['givenname'];
            $tAdmin = $row['admin'];
            $tPasswordExpires = $row['passwordexpires'];
            if (($tPasswordExpires != 0) && (isset($CONF[USE_LDAP])) && (strtoupper($CONF[USE_LDAP]) != "YES")) {
                
                $tPwModified = mkUnixTimestampFromDateTime($row['password_modified']);
                $today = time();
                $maxDiff = $CONF['password_expires'] * 86400;
                if (($today - $tPwModified) > $maxDiff) {
                    
                    $tPasswordExpired = 1;
                }
                else {
                    
                    $tPasswordExpired = 0;
                }
            }
            else {
                
                $tPasswordExpired = 0;
            }
            
            $query = "SELECT * " . "  FROM " . $schema . "svn_projects_responsible " . " WHERE (user_id = $id) " . "   AND (deleted = '00000000000000')";
            $result = db_query($query, $dbh);
            
            if (($result['rows'] > 0) && ($tAdmin == "n")) {
                
                $tAdmin = 'p';
            }
        }
    }
    else {
        
        $error = 1;
        $tMessage = _('Username and/or password wrong');
        $tMessageType = 'danger';
    }
    
    if ($error != 1) {
        
        $s = new Session();
        session_start();
        
        if (! isset($_SESSION[SVNSESSID])) {
            $_SESSION[SVNSESSID] = array();
        }
        
        $_SESSION[SVNSESSID][USERNAME] = $fUsername;
        $_SESSION[SVNSESSID]['name'] = $tName;
        $_SESSION[SVNSESSID]['givenname'] = $tGivenname;
        $_SESSION[SVNSESSID]['admin'] = $tAdmin;
        $_SESSION[SVNSESSID]['password_expired'] = $tPasswordExpired;
        if (isset($CONF['ldap_bind_use_login_data']) && $CONF['ldap_bind_use_login_data'] == 1) {
            $_SESSION[SVNSESSID]['password'] = $fPassword;
        }
        
        db_log($_SESSION[SVNSESSID][USERNAME], "user $tUsername logged in", $dbh);
        
        if ($tPasswordExpired == 1) {
            
            $_SESSION[SVNSESSID][ERRORMSG] = _('Your password expired.');
            $_SESSION[SVNSESSID][ERRORTYPE] = 'warning';
            
            db_log($_SESSION[SVNSESSID][USERNAME], "password of user $tUsername expired, force password change", $dbh);
            db_disconnect($dbh);
            header("Location: password.php");
            exit();
        }
        
        db_disconnect($dbh);
        header("Location: main.php");
        exit();
    }
    
    include ("$installBase/templates/login.tpl");
}

db_disconnect($dbh);
?>
