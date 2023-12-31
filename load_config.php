<?php

/**
 * Load global configuration file
 *
 * @author Thomas Krieger
 * @copyright 2008-2018 Thomas Krieger. All rights reserved.
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
 */

/*
 *
 * $LastChangedDate: 2018-05-30 20:16:19 +0200 (Wed, 30 May 2018) $
 * $LastChangedBy: tom_krieger $
 *
 * $Id: load_config.php 430 2018-05-30 18:16:19Z tom_krieger $
 *
 */

global $CONF;

/**
 * search config.inc.php file and load it.
 */
if (! is_array($CONF)) {
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
}

/**
 * define a constant for the install base directory if not yet done
 */
if (! defined('INSTALLBASE')) {
    define('INSTALLBASE', 'install_base');
}
?>