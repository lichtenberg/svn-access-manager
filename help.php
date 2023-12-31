<?php

/**
 * help page
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
 * $Id: help.php 430 2018-05-30 18:16:19Z tom_krieger $
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
$tText = array();

if (isset($_SESSION[SVNSESSID][HELPTOPIC])) {
    
    $schema = db_determine_schema();
    
    $lang = check_language();
    $query = "SELECT topic, headline_$lang AS headline, helptext_$lang AS helptext " . "  FROM " . $schema . "help " . " WHERE topic = '" . $_SESSION[SVNSESSID][HELPTOPIC] . "'";
    $result = db_query($query, $dbh);
    
    if ($result['rows'] > 0) {
        
        $tText = db_assoc($result['result']);
    }
    else {
        
        $tText['headline'] = _("No help found");
        $tText['helptext'] = sprintf(_("There is no help topic '%s' in the database"), $_SESSION[SVNSESSID][HELPTOPIC]);
    }
}
else {
    
    $tText['headline'] = _("No help found");
    $tText['helptext'] = _("There is no help topic set");
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    
    include ("$installBase/templates/help.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    
    include ("$installBase/templates/help.tpl");
}

db_disconnect($dbh);

?>
