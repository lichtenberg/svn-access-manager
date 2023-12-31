<?php

/**
 * list projects
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
 * $Id: list_projects.php 430 2018-05-30 18:16:19Z tom_krieger $
 *
 */
/**
 * list view of all projects
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
$rightAllowed = db_check_acl($SESSID_USERNAME, "Project admin", $dbh);
$_SESSION[SVNSESSID]['helptopic'] = "listprojects";

if ($rightAllowed == "none") {
    
    db_log($SESSID_USERNAME, "tried to use list_projects without permission", $dbh);
    db_disconnect($dbh);
    header("Location: nopermission.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    
    $_SESSION[SVNSESSID]['projectcounter'] = 0;
    $tProjects = db_getProjects(0, - 1, $dbh);
    $tCountRecords = db_getCountProjects($dbh);
    
    $template = "list_projects.tpl";
    $header = PROJECTS;
    $subheader = PROJECTS;
    $menu = PROJECTS;
    
    include ("$installBase/templates/framework.tpl");
    
    db_disconnect($dbh);
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    
    if (isset($_POST['fSubmit'])) {
        $button = db_escape_string($_POST['fSubmit']);
    }
    elseif ((isset($_POST['fSubmit_new_x'])) || (isset($_POST['fSubmit_new']))) {
        $button = _("New project");
    }
    elseif ((isset($_POST['fSubmit_back_x'])) || (isset($_POST['fSubmit_back']))) {
        $button = _("Back");
    }
    elseif ((isset($_POST['fSearchBtn'])) || (isset($_POST['fSearchBtn_x']))) {
        $button = _("search");
    }
    else {
        $button = "undef";
    }
    
    $schema = db_determine_schema();
    $tSearch = isset($_POST['fSearch']) ? db_escape_string($_POST['fSearch']) : "";
    
    if (($button == "search") || ($tSearch != "")) {
        
        $tSearch = html_entity_decode($tSearch);
        $_SESSION[SVNSESSID]['search'] = $tSearch;
        $_SESSION[SVNSESSID]['searchtype'] = PROJECTS;
        $result = db_get_list('projects', $tSearch, $dbh);
        $tErrorClass = $result['errorclass'];
        $tMessage = $result['message'];
        $tProjects = $result['result'];
    }
    elseif ($button == _("New project")) {
        
        db_disconnect($dbh);
        header("Location: workOnProject.php?task=new");
        exit();
    }
    elseif ($button == _("Back")) {
        
        db_disconnect($dbh);
        header("Location: main.php");
        exit();
    }
    else {
        
        $tMessage = sprintf(_("Invalid button %s, anyone tampered arround with?"), $button);
    }
    
    $template = "list_projects.tpl";
    $header = PROJECTS;
    $subheader = PROJECTS;
    $menu = PROJECTS;
    
    include ("$installBase/templates/framework.tpl");
    
    db_disconnect($dbh);
}
?>
