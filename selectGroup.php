<?php

/**
 * Select a group
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
 * $Id: selectGroup.php 430 2018-05-30 18:16:19Z tom_krieger $
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
$uId = db_getIdByUserid($SESSID_USERNAME, $dbh);
$_SESSION[SVNSESSID]['helptopic'] = "selectgroup";
$rightAllowed = db_check_acl($SESSID_USERNAME, "Group admin", $dbh);

if ($rightAllowed == "none") {
    
    db_log($SESSID_USERNAME, "tried to use selectGroup without permission", $dbh);
    db_disconnect($dbh);
    header("Location: nopermission.php");
    exit();
}

$schema = db_determine_schema();

$tGroups = array();
$query = "SELECT * " . "  FROM " . $schema . "svngroups " . " WHERE (svngroups.deleted = '00000000000000') " . "ORDER BY svngroups.groupname ASC";
$result = db_query($query, $dbh);
while ( $row = db_assoc($result['result']) ) {
    
    $tGroups[$row['id']] = $row['groupname'];
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    
    $header = ACCESS;
    $subheader = ACCESS;
    $menu = ACCESS;
    $template = "selectGroup.tpl";
    
    include ("$installBase/templates/framework.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    
    if (isset($_POST['fSubmit'])) {
        $button = db_escape_string($_POST['fSubmit']);
    }
    elseif ((isset($_POST['fSubmit_ok_x'])) || (isset($_POST['fSubmit_ok']))) {
        $button = _("Select group");
    }
    elseif ((isset($_POST['fSubmit_back_x'])) || (isset($_POST['fSubmit_back']))) {
        $button = _("Back");
    }
    else {
        $button = "undef";
    }
    
    if ($button == _("Back")) {
        
        db_disconnect($dbh);
        header("Location: list_group_admins.php");
        exit();
    }
    elseif ($button == _("Select group")) {
        
        $tGroup = db_escape_string($_POST['fGroup']);
        $_SESSION[SVNSESSID]['groupid'] = $tGroup;
        
        db_disconnect($dbh);
        header("Location: workOnGroupAccessRight.php?task=new");
        exit();
    }
    
    $header = ACCESS;
    $subheader = ACCESS;
    $menu = ACCESS;
    $template = "selectProject.tpl";
    
    include ("$installBase/templates/framework.tpl");
}
?>
