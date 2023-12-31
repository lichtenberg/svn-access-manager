<?php

/*
    SVN Access Manager - a subversion access rights management tool
    Copyright (C) 2008 Thomas Krieger <tom@svn-access-manager.org>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*

File:            config.inc.php.tpl
$LastChangedDate: 2014-01-01 21:10:25 +0100 (Wed, 01 Jan 2014) $
$LastChangedBy: tom_krieger $

$Id: config.inc.php.tpl 352 2014-01-01 20:10:25Z tom_krieger $

*/


if (preg_match ("/config.inc.php/", $_SERVER['PHP_SELF'])) {

   header ("Location: login.php");
   exit;

}


$CONF['install_base']                   = '/usr/share/svn-access-manager';

// Language config
$CONF['default_language']               = 'en';
$CONF['default_locale']                 = 'en_US';
$CONF['supported_languages']    = array ('de', 'de_DE', 'en', 'en_US');

// Database Config
$CONF['database_type']                  = 'mysqli';
$CONF['database_host']                  = '###DBHOST###';
$CONF['database_user']                  = '###DBUSER###';
$CONF['database_password']              = '###DBPASS###';
$CONF['database_name']                  = '###DBNAME###';
$CONF['database_prefix']                = '';
$CONF['database_innodb']                = 'YES';
$CONF['database_charset']               = '###CHARSET###';
$CONF['database_collation']             = '###COLLATION###';
$CONF['database_schema']                = '';
$CONF['database_tablespace']    = '';
$CONF['session_in_db']                  = 'YES';

$CONF['website_charset']                = 'iso-8859-15';
$CONF['website_url']                    = 'http://localhost/svn_access_manager';

// Sort order for user
$CONF['user_sort_fields']               = "name,givenname";
$CONF['user_sort_order']                = "ASC";

// Site Admin
// Define the Site Admins email address below.
$CONF['admin_email']                    = 'svn-admin@example.com';

$CONF['encrypt']                                = 'system';
$CONF['generate_password']              = 'YES';

// Lost password
$CONF['lostPwSender']                   = 'admin@example.com';
$CONF['lostPwMaxError']                 = 3;
$CONF['lostPwLinkValid']                = 2;

$CONF['logging']                                = 'YES';

// Page Size
// Set the number of entries that you would like to see
// in one page.
$CONF[PAGESIZE]                              = '50';

$CONF['passwordSpecialChars']   = '[\!\"\§\$\%\/\(\)=\?\*\+\#\-\_\.\:\,\;\<\>\|\@]';
$CONF['passwordSpecialCharsTxt']= '!"§$%/()=?*+#-_.:,;<>|@';
$CONF['minPasswordlength']              = 14;
$CONF['minPasswordlengthUser']  = 8;
// Set passwword complexity. A password must consist of four different goups och charactsers.
// The four groups are locer-case and upper-case characters, special characters and digits
// The two config varaibles $CONF['minPasswordGroups'] and $CONF['minPasswordGroupsUser']
// allow to set how many groups a password must consist of. The maximum is 4, the minimum is 1!
// If the values are out of range, for user 3 is assumed and for admins 4 is assumed
$CONF['minPasswordGroups']              = 4;
$CONF['minPasswordGroupsUser']  = 3;

// password expiry stuff
$CONF['password_expires']               = 60;
$CONF['password_expires_warn']  = 50;
// define default value for password expire, allowed values 0 and 1
$CONF['expire_password']                = '1';

# valid values for pwcrypt are sha, apr-md5, md5, or crypt
$CONF['pwcrypt']                                = 'md5';

$CONF['copyright']                              = '(C) 2008-2018 Thomas Krieger (tom(at)svn-access-manager(dot)org)';

$CONF['svn_command']                    = '/usr/bin/svn';
$CONF['svnadmin_command']               = '/usr/bin/svnadmin';
$CONF['repo_compatibility']             = '--pre-1.6-compatible';
$CONF['grep_command']                   = '/usr/bin/grep';
$CONF['use_javascript']                 = 'YES';

$CONF['SVNAccessFile']                  = '/etc/svn/svnaccess';
$CONF['AuthUserFile']                   = '/etc/svn/svnpasswd';
$CONF['ViewvcConf']                             = '/etc/svn/viewvc-apache.conf';
$CONF['ViewvcGroups']                   = '/etc/svn/viewvc-groups';
# ViewVC Alias in the Apache Webserver without trailing /
$CONF['ViewvcLocation']                 = '/viewvc';
$CONF['ViewvcApacheReload']             = 'sudo /usr/bin/systemctl reload httpd';
$CONF['ViewvcRealm']                    = 'ViewVC';
$CONF['createAccessFile']               = 'YES';
$CONF['createUserFile']                 = 'YES';
$CONF['createViewvcConf']               = 'YES';
$CONF['separateFilesPerRepo']   = 'NO';
//Limit access control to directories only, change to files if you want to have
//files listed during access rights management too
//Valid values are "dirs" or "files"
$CONF['accessControl']                  = 'dirs';
// Set users default access right
// valid values are; read, write
$CONF['userDefaultAccess']              = 'read';
// annonymous access option, allowed values are 0 or 1
$CONF['write_anonymous_access_rights']   = 0;

// SVN access file repository path sort order, allowed values are ASC or DESC
$CONF['repoPathSortOrder']              = 'ASC';

// Custom Fields
$CONF['column_custom1']                 = NULL;
$CONF['column_custom2']                 = NULL;
$CONF['column_custom3']                 = NULL;

$CONF['mail_password_warn']             = <<<EOM

Dear %s,

your password for SVN Access Manager is about to expire. Please goto %s, log in and change your password.

Please keep in mind that your account will be locked out automatically if your password was not changed.

Users are locked out if the password was not changed for %s days!

Kind regrads

SVN Access Manager
Administrator

EOM;

$CONF['mail_user_locked']               = <<<EOM

Dear %s,

you account at SVN Access Manager was locked. You did not change your password for %s days.

You can not access the subversion repositories any more. To get access please log into your account at %s and change your password.

Please give about %s minutes until your account is unlocked and you can access the subversion repositories again.

Kind regards

SVN Access Manager
Administrator

EOM;

//LDAP stuff
$CONF['use_ldap']                               = 'NO';
$CONF['bind_dn']                                = '';
$CONF['bind_pw']                                = '';
$CONF['user_dn']                                = '';
$CONF['user_filter_attr']                       = '';
$CONF['user_objectclass']                       = '';
$CONF['additional_user_filter']                 = '';
$CONF['ldap_server']                            = '';
$CONF['ldap_port']                              = '389';
$CONF['ldap_protocol']                          = '3';
$CONF['attr_mapping']['uid']                    = 'uid';
$CONF['attr_mapping']['name']                   = 'sn';
$CONF['attr_mapping']['givenName']              = 'givenName';
$CONF['attr_mapping']['mail']                   = 'mail';
$CONF['attr_mapping']['userPassword']           = 'userPassword';
// sort field to sort ldap users
$CONF['ldap_sort_field']                        = 'sn';
// sort order for ldap sort, allowed values are ASC and DESC
$CONF['ldap_sort_order']                        = 'ASC';
// use login data for ldap bind, allowed values are 0 and 1
$CONF['ldap_bind_use_login_data']                               = 0;
// LDAP bind dn suffix
$CONF['ldap_bind_dn_suffix']                                    = '';

//
// END OF CONFIG FILE
//
?>