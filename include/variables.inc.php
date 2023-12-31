<?php

/**
 * define a bunch of variables and set them to suitable default values.
 *
 * @author Thomas Krieger
 * @copyright 2008-2018 Thomas Krieger. All rights ereserved.
 * @license GPL v2
 *         
 *          SVN Access Manager - a subversion access rights management tool
 *          Copyright (C) 2008-2018 Thomas Krieger <tom@svn-access-manager.org>
 *         
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License as published by
 *          the Free Software Foundation; either version 2 of the License, or
 *          (at your option) any later version.
 *         
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *          GNU General Public License for more details.
 *         
 *          You should have received a copy of the GNU General Public License
 *          along with this program; if not, write to the Free Software
 *          Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *         
 *         
 *
 *
 */

/*
 *
 * $LastChangedDate: 2018-05-30 20:16:19 +0200 (Wed, 30 May 2018) $
 * $LastChangedBy: tom_krieger $
 *
 * $Id: variables.inc.php 430 2018-05-30 18:16:19Z tom_krieger $
 *
 */

/**
 * check if called directly and redirect to login page
 */
if (preg_match("/variables\.inc\.php/", $_SERVER['PHP_SELF'])) {
    
    header("Location: login.php");
    exit();
}

$tMessage = "";
$tMessageType = "";
$tUsername = "";
$fUsername = "";
$fUser = "";
$fPassword = "";
$fPassword2 = "";
$tPassword = "";
$tPassword2 = "";
$fPassword_current = "";
$template = "";
$menu = "";
$header = "";
$subHeader = "";
$pPassword_admin_text = "";
$pPassword_password_current_text = "";
$pPassword_password_text = "";
$tUserid = "";
$tGivenname = "";
$tName = "";
$tEmail = "";
$tLocked = "";
$tPwModified = "";
$tTask = "";
$iId = "";
$tReadonly = "";
$tGroup = "";
$tDescription = "";
$tReponame = "";
$tRepopath = "";
$tRepouser = "";
$tRepopassword = "";
$tProject = "";
$tModulepath = "";
$tRepo = "";
$tDisabled = "";
$tClass = "";
$tPath = "";
$tPathSelected = "";
$tChangeFunction = "";
$tRead = "";
$tWrite = "";
$tNone = "";
$tValidFrom = "";
$tValidUntil = "";
$tDatabaseHost = "";
$tDatabaseUser = "";
$tDatabasePassword = "";
$tDatabaseName = "";
$tSvnAccessFile = "";
$tAuthUserFile = "";
$tUseSvnAccessFileYes = "";
$tUseSvnAccessFileNo = "";
$tUseAuthUserFileYes = "";
$tUseAuthUserFileNo = "";
$tSvnCommand = "";
$tGrepCommand = "";
$tLoggingYes = "";
$tLoggingNo = "";
$tPageSize = "";
$tJavaScriptYes = "";
$tJavaScriptNo = "";
$tAdminEmail = "";
$tUserRight = "";
$tNextDisabled = "";
$tPrevDisabled = "";
$tDate = "";
$tDatabaseError = "";
$tSessionInDatabaseYes = "";
$tSessionInDatabaseNo = "";
$tSessionInDatabase = "";
$tReload = "";
$tViewvcConfigsYes = "";
$tViewvcConfigsNo = "";
$tViewvcConfigDir = "";
$tViewvcAlias = "";
$tViewvcApacheReload = "";
$tViewvcRealm = "";
$tBuildInfo = "Build: 2018-06-07 18:44:22 - 1155";

?>
