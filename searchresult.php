<?php

/**
 * Ajax servicwe for searches
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
 * $Id: searchresult.php 430 2018-05-30 18:16:19Z tom_krieger $
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
$_SESSION[SVNSESSID]['helptopic'] = SEARCHRESULT;
$error = 0;
$tErrorClass = "";
$tMessage = "";
$tArray = isset($_SESSION[SVNSESSID][SEARCHRESULT]) ? $_SESSION[SVNSESSID][SEARCHRESULT] : array();
$tType = isset($_SESSION[SVNSESSID]['searchtype']) ? $_SESSION[SVNSESSID]['searchtype'] : "";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    
    $template = "searchresult_$tType.tpl";
    $header = SEARCH;
    $subheader = SEARCH;
    $menu = SEARCH;
    
    include ("./templates/framework.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    
    if (isset($_POST['fSubmit'])) {
        $button = db_escape_string($_POST['fSubmit']);
    }
    elseif ((isset($_POST['fSubmit_ok_x'])) || (isset($_POST['fSubmit_ok']))) {
        $button = _("Submit");
    }
    elseif ((isset($_POST['fSubmit_back_x'])) || (isset($_POST['fSubmit_back']))) {
        $button = _("Back");
    }
    elseif ((isset($_POST['fSubmit_new'])) || (isset($_POST['fSubmit_new_x']))) {
        $button = "new";
    }
    else {
        $button = "undef";
    }
    if ($button == _("Back")) {
        
        $location = "undef";
        if ($tType == "access_right") {
            $location = "list_access_rights.php";
        }
        elseif ($tType == "groupadmin") {
            $location = "list_group_admins.php";
        }
        elseif ($tType == "groups") {
            $location = "list_groups.php";
        }
        elseif ($tType == "projects") {
            $location = "list_projects.php";
        }
        elseif ($tType == "repos") {
            $lovation = "list_repos.php";
        }
        elseif ($tType == "users") {
            $location = "list_users.php";
        }
        
        db_disconnect($dbh);
        header("Location: $location");
        exit();
    }
    elseif ($button == "new") {
        
        $location = "undef";
        if ($tType == "access_right") {
            $location = "selectProject.php";
        }
        elseif ($tType == "groupadmin") {
            $location = "selectGroup.php";
        }
        elseif ($tType == "groups") {
            $location = "workOnGroup.php?task=new";
        }
        elseif ($tType == "projects") {
            $location = "workOnProject.php?task=new";
        }
        elseif ($tType == "repos") {
            $location = "workOnRepo.php?task=new";
        }
        elseif ($tType == "users") {
            $location = "workOnUser.php?task=new";
        }
        
        db_disconnect($dbh);
        header("Location: $location");
        exit();
    }
    
    $template = "searchresult_$tType.tpl";
    $header = SEARCH;
    $subheader = SEARCH;
    $menu = SEARCH;
    
    include ("./templates/framework.tpl");
}

db_disconnect($dbh);

?>
