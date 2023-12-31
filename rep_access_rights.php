<?php

/**
 * Report access rights
 * *
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
 * $Id: rep_access_rights.php 430 2018-05-30 18:16:19Z tom_krieger $
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
$rightAllowed = db_check_acl($SESSID_USERNAME, "Reports", $dbh);
$_SESSION[SVNSESSID]['helptopic'] = "repaccessrights";

if ($rightAllowed == "none") {
    
    db_log($SESSID_USERNAME, "tried to use rep_access_rights without permission", $dbh);
    db_disconnect($dbh);
    header("Location: nopermission.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    
    $tDateError = '';
    $lang = check_language();
    $tDate = date("Y-m-d");
    
    $template = "getDateForAccessRightsModal.tpl";
    $header = REPORTS;
    $subheader = REPORTS;
    $menu = REPORTS;
    
    include ("$installBase/templates/framework.tpl");
    
    db_disconnect($dbh);
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    
    $error = 0;
    
    if (isset($_POST['fSubmit'])) {
        $button = db_escape_string($_POST['fSubmit']);
    }
    elseif ((isset($_POST['fSubmit_date_x'])) || (isset($_POST['fSubmit_date']))) {
        $button = _("Create report");
    }
    elseif ((isset($_POST['fSubmit_cancel_x'])) || (isset($_POST['fSubmit_cancel']))) {
        $button = _("Cancel");
    }
    else {
        $button = "undef";
    }
    
    if ($button == _("Create report")) {
        
        // 2018-05-09
        $tDate = isset($_POST['fDate']) ? db_escape_string($_POST['fDate']) : "";
        $_SESSION[SVNSESSID]['date'] = $tDate;
        $lang = check_language();
        
        $day = substr($tDate, 8, 2);
        $month = substr($tDate, 5, 2);
        $year = substr($tDate, 0, 4);
        
        if (! check_date($day, $month, $year)) {
            
            $tMessage = sprintf(_("Not a valid date: %s (%s-%s-%s)"), $tDate, $day, $month, $year);
            $tMessageType = DANGER;
            $tDateError = 'error';
            $error = 1;
            
            $template = "getDateForAccessRightsModal.tpl";
            $header = REPORTS;
            $subheader = REPORTS;
            $menu = REPORTS;
            
            include ("$installBase/templates/framework.tpl");
            
            db_disconnect($dbh);
            exit();
        }
        else {
            
            $tDateError = 'ok';
            $valid = $year . $month . $day;
            $_SESSION[SVNSESSID]['valid'] = $valid;
            $_SESSION[SVNSESSID]['rightcounter'] = 0;
            $tAccessRights = db_getAccessRightsList($_SESSION[SVNSESSID]['valid'], 0, - 1, $dbh);
            $tCountRecords = db_getCountAccessRightsList($_SESSION[SVNSESSID]['valid'], $dbh);
            $tPrevDisabled = "disabled";
        }
    }
    elseif (($button == _("Cancel")) || ($button == 'undef')) {
        
        db_disconnect($dbh);
        header("Location: main.php");
        exit();
    }
    else {
        
        $tMessage = sprintf(_("Invalid button %s, anyone tampered arround with?"), $button);
        $tMessageType = DANGER;
    }
    
    $template = "rep_access_rights.tpl";
    $header = REPORTS;
    $subheader = REPORTS;
    $menu = REPORTS;
    
    include ("$installBase/templates/framework.tpl");
    
    db_disconnect($dbh);
}

?>
