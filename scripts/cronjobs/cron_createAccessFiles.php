#!/usr/bin/php

<?php

/*
 * SVN Access Manager - a subversion access rights management tool
 * Copyright (C) 2008-2018 Thomas Krieger <tom@svn-access-manager.org>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

/*
 *
 * File: cron_createAccessFiles.php
 * $LastChangedDate: 2018-05-23 15:39:55 +0200 (Wed, 23 May 2018) $
 * $LastChangedBy: tom_krieger $
 *
 * $Id: cron_createAccessFiles.php 427 2018-05-23 13:39:55Z tom_krieger $
 *
 *
 * !!!!!=========================================================!!!!!
 * Please change $INCLUDEPATH according to your installation
 * !!!!!=========================================================!!!!!
 *
 */
if (file_exists(realpath("./config/config.inc.php"))) {
    require ("./config/config.inc.php");
}
elseif (file_exists(realpath("../config/config.inc.php"))) {
    require ("../config/config.inc.php");
}
elseif (file_exists("/etc/svn-access-manager/config.inc.php")) {
    require ("/etc/svn-access-manager/config.inc.php");
}
else {
    die("can't load config.inc.php. Please check your installation!\n");
}

$INCLUDEPATH = isset($CONF[INSTALLBASE]) ? $CONF[INSTALLBASE] : "";

include_once ("$INCLUDEPATH/include/constants.inc.php");
require ("$INCLUDEPATH/include/variables.inc.php");
require ("$INCLUDEPATH/include/functions.inc.php");
require ("$INCLUDEPATH/include/db-functions-adodb.inc.php");
require ("$INCLUDEPATH/include/createAuthFiles.php");

ini_set('max_execution_time', '3600');
ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('error_log', '/var/tmp/cron_createAccessFiles.log');
ini_set('error_reporting', 'E_ALL');

initialize_i18n();

if ($CONF['createAccessFile'] == 'YES') {
    
    $dbh = db_connect();
    if ((isset($CONF[SEPARATEFILESPERREPO])) && ($CONF[SEPARATEFILESPERREPO] == "YES")) {
        
        $tRetAccess = createAccessFilePerRepo($dbh);
    }
    else {
        $tRetAccess = createAccessFile($dbh);
    }
    
    if ($tRetAccess[ERROR] != 0) {
        
        print $tRetAccess[ERRORMSG] . " \n";
    }
    db_disconnect($dbh);
}

if ($CONF['createUserFile'] == 'YES') {
    
    $dbh = db_connect();
    if ((isset($CONF[SEPARATEFILESPERREPO])) && ($CONF[SEPARATEFILESPERREPO] == "YES")) {
        
        $tRetAuthUser = createAuthUserFilePerRepo($dbh);
    }
    else {
        $tRetAuthUser = createAuthUserFile($dbh);
    }
    
    if ($tRetAuthUser[ERROR] != 0) {
        
        print $tRetAuthUser[ERRORMSG] . "\n";
    }
    
    db_disconnect($dbh);
}

if ($CONF['createViewvcConf'] == "YES") {
    
    $dbh = db_connect();
    $tRetViewvcConf = createViewvcConfig($dbh);
    
    if ($tRetViewvcConf[ERROR] != 0) {
        
        print $tRetViewvcConf[ERRORMSG] . "\n";
    }
    
    db_disconnect($dbh);
}

exit();
?>
