<?php

/**
 * delete a repository
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
 * $Id: deleteRepo.php 430 2018-05-30 18:16:19Z tom_krieger $
 *
 */
include ('load_config.php');

$installBase = isset($CONF[INSTALLBASE]) ? $CONF[INSTALLBASE] : "";

require ("$installBase/include/variables.inc.php");
include_once ("$installBase/include/constants.inc.php");
require ("$installBase/include/functions.inc.php");
require ("$installBase/include/output.inc.php");
require ("$installBase/include/db-functions-adodb.inc.php");

$SESSID_USERNAME = check_session();
check_password_expired();
$dbh = db_connect();
$preferences = db_get_preferences($SESSID_USERNAME, $dbh);
$CONF[PAGESIZE] = $preferences[PAGESIZE];
$CONF[TOOLTIP_SHOW] = $preferences[TOOLTIP_SHOW];
$CONF[TOOLTIP_HIDE] = $preferences[TOOLTIP_HIDE];
$rightAllowed = db_check_acl($SESSID_USERNAME, "Repository admin", $dbh);
$_SESSION[SVNSESSID]['helptopic'] = "deleterepo";

if ($rightAllowed != "delete") {
    
    db_log($SESSID_USERNAME, "tried to use deleteProject without permission", $dbh);
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
    $_SESSION[SVNSESSID][REPOID] = $tId;
    
    $schema = db_determine_schema();
    
    if ($_SESSION[SVNSESSID]['task'] == "delete") {
        
        $query = "SELECT * " . "  FROM " . $schema . "svnrepos " . " WHERE id = $tId";
        $result = db_query($query, $dbh);
        
        if ($result['rows'] == 1) {
            
            $row = db_assoc($result[RESULT]);
            $tReponame = $row['reponame'];
            $tRepopath = $row['repopath'];
            $tRepouser = $row['repouser'];
            $tRepopassword = $row['repopassword'];
            $tDisabled = "";
            $tClass = "button";
            
            $query = "SELECT * " . "  FROM " . $schema . "svnprojects " . " WHERE (deleted = '00000000000000') " . "   AND (repo_id = '" . $_SESSION[SVNSESSID][REPOID] . "')";
            $result = db_query($query, $dbh);
            
            if ($result['rows'] > 0) {
                
                $repos = "";
                
                while ( $row = db_assoc($result[RESULT]) ) {
                    
                    if ($repos == "") {
                        
                        $repos .= $row[SVNMODULE];
                    }
                    else {
                        
                        $repos .= ", " . $row[SVNMODULE];
                    }
                }
                
                $tMessage = sprintf(_("Repository can not be deleted because it's referenced from other projects (%s)"), $repos);
                $tDisabled = "disabled";
                $tClass = "buttondisabled";
            }
        }
        else {
            
            $tMessage = _("Invalid repository id $id requested!");
            $tMessageType = DANGER;
        }
    }
    else {
        
        $tMessage = sprintf(_("Invalid task %s, anyone tampered arround with?"), $_SESSION[SVNSESSID]['task']);
        $tMessageType = DANGER;
    }
    
    $header = REPOS;
    $subheader = REPOS;
    $menu = REPOS;
    $template = "deleteRepo.tpl";
    
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
    
    $schema = db_determine_schema();
    
    if ($button == _("Delete")) {
        
        $query = "SELECT * " . "  FROM " . $schema . "svnprojects " . " WHERE (deleted = '00000000000000') " . "   AND (repo_id = '" . $_SESSION[SVNSESSID][REPOID] . "')";
        $result = db_query($query, $dbh);
        
        if ($result['rows'] == 0) {
            
            $reponame = db_getRepoById($_SESSION[SVNSESSID][REPOID], $dbh);
            
            db_ta('BEGIN', $dbh);
            db_log($_SESSION[SVNSESSID]['username'], "deleted repository $reponame", $dbh);
            
            $dbnow = db_now();
            $query = "UPDATE " . $schema . "svnrepos " . "   SET deleted = '$dbnow', " . "       deleted_user = '" . $_SESSION[SVNSESSID]['username'] . "'" . " WHERE id = " . $_SESSION[SVNSESSID][REPOID];
            $result = db_query($query, $dbh);
            
            if ($result['rows'] == 1) {
                
                db_ta('COMMIT', $dbh);
                $tMessage = _("Repository successfully deleted");
                $tMessageType = SUCCESS;
                
                db_disconnect($dbh);
                
                header("Location: list_repos.php");
                exit();
            }
            else {
                
                db_ta('ROLLBACK', $dbh);
                $tMessage = _("Repository not deleted due to database error");
                $tMessageType = DANGER;
            }
        }
        else {
            
            $repos = "";
            
            while ( $row = db_assoc($result[RESULT]) ) {
                
                if ($repos == "") {
                    
                    $repos .= $row[SVNMODULE];
                }
                else {
                    
                    $repos .= ", " . $row[SVNMODULE];
                }
            }
            
            $tMessage = sprintf(_("Repository not deleted due to usage in other projects (%s)!"), $repos);
            $tMessageType = DANGER;
        }
    }
    elseif ($button == _("Back")) {
        
        db_disconnect($dbh);
        header("Location: list_repos.php");
        exit();
    }
    else {
        
        $tMessage = _("Invalid button $button, anyone tampered arround with?");
        $tMessageType = DANGER;
    }
    
    $header = REPOS;
    $subheader = REPOS;
    $menu = REPOS;
    $template = "deleteRepo.tpl";
    
    include ("$installBase/templates/framework.tpl");
}

db_disconnect($dbh);
?>
