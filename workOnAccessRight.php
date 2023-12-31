<?php

/**
 * Work on an access right
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
 * $Id: workOnAccessRight.php 430 2018-05-30 18:16:19Z tom_krieger $
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
$rightAllowed = db_check_acl($SESSID_USERNAME, "Access rights admin", $dbh);
$_SESSION[SVNSESSID]['helptopic'] = "workonaccessright";
$accessControl = isset($CONF['accessControl']) ? $CONF['accessControl'] : "dirs";

if ($rightAllowed == "none") {
    
    if ($_SESSION[SVNSESSID]['admin'] == "p") {
        
        $tSeeUserid = $SESSID_USERNAME;
    }
    else {
        
        db_log($SESSID_USERNAME, "tried to use workOnAccessRight without permission", $dbh);
        db_disconnect($dbh);
        header("Location: nopermission.php");
        exit();
    }
}
else {
    
    $tSeeUserid = - 1;
}

$schema = db_determine_schema();

if ($tSeeUserid != - 1) {
    $id = db_getIdByUserid($SESSID_USERNAME, $dbh);
    $tProjectIds = "";
    $query = "SELECT * " . "  FROM " . $schema . "svn_projects_responsible " . " WHERE (user_id = $id) " . "   AND (deleted = '00000000000000')";
}
else {
    
    $tProjectIds = "";
    $query = "SELECT * " . "  FROM " . $schema . "svn_projects_responsible " . " WHERE (deleted = '00000000000000')";
}

$result = db_query($query, $dbh);
while ( $row = db_assoc($result[RESULT]) ) {
    
    if ($tProjectIds == "") {
        
        $tProjectIds = $row[PROJECT_ID];
    }
    else {
        
        $tProjectIds = $tProjectIds . "," . $row[PROJECT_ID];
    }
}

$uId = db_getIdByUserid($SESSID_USERNAME, $dbh);
$tProjects = array();
if ($tProjectIds != "") {
    $query = "SELECT svnprojects.id, svnmodule, modulepath, reponame, " . "       repopath, repouser, repopassword " . "  FROM " . $schema . "svn_projects_responsible, " . $schema . "svnprojects, " . $schema . "svnrepos " . " WHERE (svnprojects.id IN (" . $tProjectIds . ")) " . "   AND (svn_projects_responsible.project_id = svnprojects.id) " . "   AND (svnprojects.repo_id = svnrepos.id) " . "   AND (svn_projects_responsible.deleted = '00000000000000') " . "   AND (svnprojects.deleted = '00000000000000')";
    $result = db_query($query, $dbh);
    while ( $row = db_assoc($result[RESULT]) ) {
        
        $tProjects[$row['id']] = $row[SVNMODULE];
    }
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    
    $tReadonly = "";
    $fileSelect = 0;
    $tTask = db_escape_string($_GET['task']);
    if (isset($_GET['id'])) {
        
        $tId = db_escape_string($_GET['id']);
    }
    else {
        
        $tId = "";
    }
    
    if (($rightAllowed == "add") && ($tTask != "new")) {
        
        db_log($SESSID_USERNAME, "tried to use workOnAccessRight without permission", $dbh);
        db_disconnect($dbh);
        header("Location: nopermission.php");
        exit();
    }
    
    $_SESSION[SVNSESSID]['task'] = strtolower($tTask);
    
    if ($_SESSION[SVNSESSID]['task'] == "new") {
        
        unset($_SESSION[SVNSESSID]['validfrom']);
        unset($_SESSION[SVNSESSID]['validuntil']);
        unset($_SESSION[SVNSESSID]['accessright']);
        unset($_SESSION[SVNSESSID]['userid']);
        unset($_SESSION[SVNSESSID]['groupid']);
        
        $query = "SELECT * " . "  FROM " . $schema . "svnprojects " . " WHERE id = " . $_SESSION[SVNSESSID]['projectid'];
        $result = db_query($query, $dbh);
        if ($result['rows'] == 1) {
            
            $row = db_assoc($result[RESULT]);
            $tProject = $row['id'];
            $tProjectName = $row[SVNMODULE];
            $_SESSION[SVNSESSID][SVNMODULE] = $tProjectName;
            $tModulePath = $row[MODULEPATH];
            $_SESSION[SVNSESSID][MODULEPATH] = $tModulePath;
            $_SESSION[SVNSESSID]['path'] = array();
            $_SESSION[SVNSESSID]['path'][0] = "";
            $_SESSION[SVNSESSID][PATHCNT] = 0;
            $tRepoId = $row['repo_id'];
            $query = "SELECT * " . "  FROM " . $schema . "svnrepos " . " WHERE id = $tRepoId";
            $result = db_query($query, $dbh);
            if ($result['rows'] == 1) {
                
                $row = db_assoc($result[RESULT]);
                $tRepoName = $row[REPONAME];
                $tRepoPath = $row[REPOPATH];
                $tRepoUser = $row[REPOUSER];
                $tRepoPassword = $row[REPOPASSWORD];
                
                $_SESSION[SVNSESSID][REPONAME] = $tRepoName;
                $_SESSION[SVNSESSID][REPOPATH] = $tRepoPath;
                $_SESSION[SVNSESSID][REPOUSER] = $tRepoUser;
                $_SESSION[SVNSESSID][REPOPASSWORD] = $tRepoPassword;
                $os = determineOs();
                
                if ($os == "windows") {
                    $tempdir = "c:/temp";
                }
                else {
                    $tempdir = "/var/tmp/";
                }
                
                if (strtolower(substr($tRepoPath, 0, 4) == "http")) {
                    $options = " --username $tRepoUser --password $tRepoPassword ";
                }
                else {
                    $options = "";
                }
                
                $repopath = preg_replace('/\\\/', '/', $tRepoPath);
                $tRepodirs = array();
                $cmd = $CONF['svn_command'] . ' list --no-auth-cache --non-interactive --config-dir ' . $tempdir . ' ' . $options . ' "' . $repopath . '/' . $tModulePath . '"';
                $errortext = exec($cmd, $tRepodirsArr, $retval);
                
                if ($retval == 0) {
                    
                    if (strtolower($accessControl) != "files") {
                        
                        foreach( $tRepodirsArr as $repo ) {
                            
                            if (preg_match('/\/$/', $repo)) {
                                $tRepodirs[] = $repo;
                            }
                        }
                    }
                    else {
                        $tRepodirs = $tRepodirsArr;
                    }
                    $tPathSelected = "";
                }
                else {
                    
                    $tMessage = sprintf(_("Error while accessing svn repository: %s (%s / retcode = %s)"), $errortext, $cmd, $retval);
                    $tMessageType = 'warning';
                }
            }
            else {
                
                $tMessage = sprintf(_("Invalid repository id %s requested!"), $tRepoId);
                $tMessageType = DANGER;
            }
        }
        else {
            
            $tMessage = sprintf(_("Invalid project id %s requested"), $_SESSION[SVNSESSID]['projectid']);
            $tMessageType = DANGER;
        }
    }
    elseif ($_SESSION[SVNSESSID]['task'] == "change") {
        
        $tReadonly = "readonly";
        $query = "SELECT * " . "  FROM " . $schema . "svn_access_rights " . " WHERE id = $tId";
        $result = db_query($query, $dbh);
        if ($result['rows'] == 1) {
            
            $row = db_assoc($result[RESULT]);
            $rightid = $row['id'];
            $projectid = $row[PROJECT_ID];
            $tPathSelected = $row['path'];
            $validfrom = $row['valid_from'];
            $validuntil = $row['valid_until'];
            $accessright = $row['access_right'];
            $groupid = $row['group_id'];
            $userid = $row['user_id'];
            
            if ($userid != 0) {
                
                $userid = db_getUseridById($userid, $dbh);
            }
            
            $_SESSION[SVNSESSID]['pathselected'] = $tPathSelected;
            $_SESSION[SVNSESSID]['validfrom'] = $validfrom;
            $_SESSION[SVNSESSID]['validuntil'] = $validuntil;
            $_SESSION[SVNSESSID]['accessright'] = $accessright;
            $_SESSION[SVNSESSID]['userid'] = $userid;
            $_SESSION[SVNSESSID]['groupid'] = $groupid;
            $_SESSION[SVNSESSID]['rightid'] = $tId;
            
            $query = "SELECT * " . "  FROM " . $schema . "svnprojects " . " WHERE id = '$projectid'";
            $result = db_query($query, $dbh);
            if ($result['rows'] == 1) {
                
                $row = db_assoc($result[RESULT]);
                $tProject = $row['id'];
                $tProjectName = $row[SVNMODULE];
                $_SESSION[SVNSESSID][SVNMODULE] = $tProjectName;
                $tModulePath = $row[MODULEPATH];
                $_SESSION[SVNSESSID][MODULEPATH] = $tModulePath;
                $tRepoId = $row['repo_id'];
                $query = "SELECT * " . "  FROM " . $schema . "svnrepos " . " WHERE id = $tRepoId";
                $result = db_query($query, $dbh);
                if ($result['rows'] == 1) {
                    
                    $row = db_assoc($result[RESULT]);
                    $tRepoName = $row[REPONAME];
                    $tRepoPath = $row[REPOPATH];
                    $tRepoUser = $row[REPOUSER];
                    $tRepoPassword = $row[REPOPASSWORD];
                    
                    $_SESSION[SVNSESSID][REPONAME] = $tRepoName;
                    $_SESSION[SVNSESSID][REPOPATH] = $tRepoPath;
                    $_SESSION[SVNSESSID][REPOUSER] = $tRepoUser;
                    $_SESSION[SVNSESSID][REPOPASSWORD] = $tRepoPassword;
                }
            }
            else {
                
                $tMessage = sprintf(_("Invalid project id %s requested"), $projectid);
                $tMessageType = DANGER;
            }
            
            db_disconnect($dbh);
            header("location: setAccessRight.php?task=change");
            exit();
        }
        else {
            
            $tMessage = _("Invalid access right id $tId requested!");
            $tMessageType = DANGER;
        }
    }
    else {
        
        $tMessage = sprintf(_("Invalid task %s, anyone tampered arround with?"), $_SESSION[SVNSESSID]['task']);
        $tMessageType = DANGER;
    }
    
    $header = ACCESS;
    $subheader = ACCESS;
    $menu = ACCESS;
    $template = "workOnAccessRight.tpl";
    
    include ("$installBase/templates/framework.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    
    $tProjectName = $_SESSION[SVNSESSID][SVNMODULE];
    $tRepoName = $_SESSION[SVNSESSID]['reponame'];
    $tRepoPath = $_SESSION[SVNSESSID][REPOPATH];
    $tRepoUser = $_SESSION[SVNSESSID][REPOUSER];
    $tRepoPassword = $_SESSION[SVNSESSID][REPOPASSWORD];
    $tModulePath = $_SESSION[SVNSESSID][MODULEPATH];
    
    if (isset($_POST['fSubmit'])) {
        $button = db_escape_string($_POST['fSubmit']);
    }
    elseif ((isset($_POST['fSubmit_chdir_x'])) || (isset($_POST['fSubmit_chdir']))) {
        $button = _("Change to directory");
    }
    elseif ((isset($_POST['fSubmit_back_x'])) || (isset($_POST['fSubmit_back']))) {
        $button = _("Back");
    }
    elseif ((isset($_POST['fSubmit_set_x'])) || (isset($_POST['fSubmit_set']))) {
        $button = _("Set access rights");
    }
    else {
        $button = "";
    }
    
    if ($button == _("Back")) {
        
        db_disconnect($dbh);
        header("location: list_access_rights.php");
        exit();
    }
    elseif (($button == _("Change to directory")) || ($button == "")) {
        
        $fileSelect = 0;
        
        if (isset($_POST['fPath'])) {
            
            $tPath = db_escape_string($_POST['fPath']);
        }
        else {
            
            $tPath = "";
        }
        
        if ($tPath == '[back]') {
            
            $count = count($_SESSION[SVNSESSID]['path']) - 1;
            
            if ($count > 0) {
                
                array_pop($_SESSION[SVNSESSID]['path']);
                $_SESSION[SVNSESSID][PATHCNT] --;
            }
        }
        elseif ($tPath == "") {
            
            // do nothing
        }
        else {
            
            $_SESSION[SVNSESSID][PATHCNT] ++;
            if (preg_match('/\/$/', $tPath)) {
                
                $tPath = substr($tPath, 0, (strlen($tPath) - 1));
            }
            else {
                $fileSelect = 1;
            }
            $_SESSION[SVNSESSID]['path'][$_SESSION[SVNSESSID][PATHCNT]] = $tPath;
        }
        
        $tRepodirs = array();
        $tPathSelected = implode("/", $_SESSION[SVNSESSID]['path']);
        $os = determineOs();
        
        if ($os == "windows") {
            $tempdir = "c:/temp";
        }
        else {
            $tempdir = "/var/tmp/";
        }
        
        if (strtolower(substr($tRepoPath, 0, 4) == "http")) {
            $options = " --username $tRepoUser --password $tRepoPassword ";
        }
        else {
            $options = "";
        }
        
        $tRepodirs = array();
        $repopath = preg_replace('/\\\/', '/', $tRepoPath);
        $cmd = $CONF['svn_command'] . ' list --no-auth-cache --non-interactive --config-dir ' . $tempdir . ' ' . $options . ' "' . $repopath . '/' . $tModulePath . '/' . $tPathSelected . '"';
        $errortext = exec($cmd, $tRepodirsArr, $retval);
        
        if (strtolower($accessControl) != "files") {
            
            foreach( $tRepodirsArr as $repo ) {
                
                if (preg_match('/\/$/', $repo)) {
                    $tRepodirs[] = $repo;
                }
            }
        }
        else {
            $tRepodirs = $tRepodirsArr;
        }
    }
    elseif ($button == _("Set access rights")) {
        
        if (isset($_POST['fPathSelected'])) {
            
            $tPath = db_escape_string($_POST['fPathSelected']);
        }
        else {
            
            $tPath = "";
        }
        
        if (substr($tPath, 0, 1) != "/") {
            $tPath = "/" . $tPath;
        }
        
        $tPath = preg_replace('/\/$/', '', $tPath);
        
        $_SESSION[SVNSESSID]['pathselected'] = $tPath;
        
        db_disconnect($dbh);
        header("location: setAccessRight.php?task=" . $_SESSION[SVNSESSID]['task']);
        exit();
    }
    else {
        
        $tMessage = sprintf(_("Invalid button %s, anyone tampered arround with?"), $button);
        $tMessageType = DANGER;
    }
    
    $header = ACCESS;
    $subheader = ACCESS;
    $menu = ACCESS;
    $template = "workOnAccessRight.tpl";
    
    include ("$installBase/templates/framework.tpl");
}
?>
