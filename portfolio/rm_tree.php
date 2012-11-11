<?php
/*
+----------------------------------------------------------------------+
|
| rm_tree.php - Server-Written File Remover / Permissions Changer
|
| This script removes, or adjusts the permissions of, files and
| that were originally written to the filesystem by a web script.
|
| About the script:
|
| In the most common web server configuration, files and directories
| that are created by a PHP script will be owned by the default user
| ID (UID) of the web server daemon.  These server-written files
| typically cannot be subsequently written to or deleted by your
| normal user.  In other words, the files and directories were created
| by a PHP script and now they need to be deleted or written to by
| a PHP script (unless the original script left the permissions 
| world-writable).
|
| The script originally only removed the server-writeen files and
| dirctories (hence the name), but now you can optionally convert
| them TO and FROM world-writable permissions.  Many people will
| want to convert existing files and directories to world-writable
| and then set the script that writes new files so it leaves permissions
| world-writable from now on.
|
| By default, this script works on files and subdirectories *beneath*
| a specified top directory.  That is, it works on the "branches" and
| "leaves" of a tree but leaves the "stump" alone unless
| $leave_topdir_alone is FALSE.
|
| When changing permissions TO world-writable, they will be 0777 and
| 0666 for directories and files respectively.  Non-world-writable
| permissions will be 0775 and 0664 for directories and files
| respectively.
|
| By default the script will change permissions to non-wold-writable.
|
| Instructions:
|
| * First, now is the time to consider setting the file creation mask
|   of your script that creates new files (e.g. Qdig) so you won't need
|   to use this script in the future.  Here is the world-writable file
|   creation mask setting:
|
|     umask(0000);
|
| * For security reasons, by default this script is packed in the Qdig
|   archive with non world readable permissions.  Make sure the script
|   is readable by the web server daemon, typically with
|
|     chmod 644 rm_tree.php
|
| * Also for security reasons, the script is disabled by default.
|   Enable the script with
|
|     $enabled =TRUE;
|
| * Set an appropriate top directory.  In order of liklihood, typical
|   settings might be
|
|     $top_dir = 'qdig-files/converted-images';
|     $top_dir = 'qdig-files/captions';
|     $top_dir = 'qdig-files';
|
| * Configure the script's $change_perms[] settings.
|
|   For deleting:
|
|     $change_perms['ww']     = FALSE;
|     $change_perms['non_ww'] = FALSE;
|
|   For permissions-changing TO world-writable:
|
|     $change_perms['ww']     = TRUE;
|     $change_perms['non_ww'] = FALSE;
|
|   For permissions-changing FROM world-writable:
|
|     $change_perms['ww']     = FALSE;
|     $change_perms['non_ww'] = TRUE;
|
| * When you are satisfied with your settings, visit the script's URL
|   (that starts with "http//:") with your browser.
|
| * Once you're done, set $enabled to FALSE and optionally also
|   set non-world-readable permissions (e.g. `chmod 600 rm_tree.php').
+----------------------------------------------------------------------+
| Copyright 2004, 2005, 2006 Hagan Fox
| This program is distributed under the terms of the
| GNU General Public License, Version 2
|
| This program is free software; you can redistribute it and/or modify
| it under the terms of the GNU General Public License, Version 2 as
| published by the Free Software Foundation.
|
| This program is distributed in the hope that it will be useful,
| but WITHOUT ANY WARRANTY; without even the implied warranty of
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
| GNU General Public License for more details.
|
| You should have received a copy of the GNU General Public License,
| Version 2 along with this program; if not, visit GNU's Home Page
| http://www.gnu.org/
+----------------------------------------------------------------------+
$Id: rm_tree.php,v 1.3 2006/04/15 17:13:29 haganfox Exp $
*/

/**
* Settings
*/
// Set this to TRUE.  It's FALSE by default avoid "accidents". :)
$enabled = FALSE;

// This is the Name of the top directory.  Everything below it will
// be affected (deleted or have its permissions checked / changed).
$top_dir = 'qdig-files/converted-images';
//$top_dir = 'qdig-files/captions';

// Leave the the top directory when deleting a directory tree.
$leave_topdir_alone = TRUE;

// Don't delete anything, just check / change permissions so they're
// either world writable on nonworld-wirtable.
$change_perms['ww']     = TRUE;
$change_perms['non_ww'] = FALSE;

// Let the script do the error reporting.
error_reporting(E_ALL ^E_NOTICE ^E_WARNING);

/**
* Logic
*/
echo "<html>\n<head>\n<title>Server-Written File Remover / Permissions Changer</title>\n</head>\n<body>\n";
if ($enabled == TRUE) {
    if (! is_dir($top_dir)) {
        echo "The directory '$top_dir/' was not found.<br />\n";
    } else {
        rmRecursiveDir($top_dir);
        if (@$did_something == TRUE) {
            echo "Done.\n"; 
        } else {
            echo "Nothing to do!\n";
        }
    }
} else {
    echo 'This script is disabled.<br /><br />
    Edit the script and set $enabled to TRUE.'."\n";
}
echo "</body>\n</html>";

/**
* Remove specified directory recursively.
*
* This function is based on a function by Aidan Lister <aidan@php.net> at
* http://aidan.dotgeek.org/lib/?file=function.rmdirr.php
*/
function rmRecursiveDir($dirname)
{
    global $top_dir, $leave_topdir_alone, $change_perms, $did_something;

    // Loop through a directory.
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers.
        if ($entry == '.' || $entry == '..') { continue; }
        // Recurse if a directory, delete if a file.      
        if (is_dir("$dirname/$entry")) {
            rmRecursiveDir("$dirname/$entry");
        } else {
            $file_perms  = decoct(fileperms("$dirname/$entry")) % 1000;
            if ($change_perms['non_ww']) {
                if ($file_perms == 664) {
                    echo '';
                } else if (chmod("$dirname/$entry", 0664)) {
                    echo "Changed permissions of $dirname/$entry from $file_perms to 0664.<br />\n";
                    $did_something = TRUE;
                } else {
                    echo "<b>Unable to change permissions of $dirname/$entry.</b><br />\n";
                }
            } else if ($change_perms['ww']) {
                if ($file_perms == 666) {
                    echo '';
                } else if (chmod("$dirname/$entry", 0666)) {
                    echo "Changed permissions of $dirname/$entry from $file_perms to 0666.<br />\n";
                    $did_something = TRUE;
                } else {
                    echo "<b>Unable to change permissions of $dirname/$entry.</b><br />\n";
                }
            } else if (unlink("$dirname/$entry")) {
                echo "Deleted $dirname/$entry.<br />\n";
                $did_something = TRUE;            
            } else {
                echo "<b>Unable to delete $dirname/$entry.</b><br />\n";
                $did_something = TRUE;
            }
        }
    }
    // Clean up.
    $dir->close();

    // Leave top directory as-is if told to do so.
    if ($leave_topdir_alone == TRUE
        && $dirname == $top_dir)
    {
        echo "Leaving '$top_dir/' as-is.<br />\n";
        return;
    }
    // Remove (now empty) directory.
    $dir_perms  = decoct(fileperms("$dirname/$entry")) % 1000;
    if ($change_perms['non_ww'] == TRUE) {
        if ($dir_perms == 775) {
            echo '';
            return TRUE;
        } else if (chmod("$dirname/$entry", 0775)) {
            echo "Changed permissions of $dirname/$entry from $file_perms to 0775.<br />\n";
            $did_something = TRUE;
            return TRUE;
        } else {
          echo "<b>Unable to change permissions of $dirname/$entry.</b><br />\n";
          return FALSE;
        }
    }
    if ($change_perms['ww'] == TRUE) {
        if ($dir_perms == 777) {
            echo '';
            return TRUE;
        } else if (chmod("$dirname/$entry", 0777)) {
            echo "Changed permissions of $dirname/$entry from $file_perms to 0777.<br />\n";
            $did_something = TRUE;
            return TRUE;
        } else {
          echo "<b>Unable to change permissions of $dirname/$entry.</b><br />\n";
          return FALSE;
        }
    }
    if (rmdir($dirname)) {
        echo "Removed '$dirname/'.<br />\n";
        $did_something = TRUE;            
        return TRUE;
    } else {
        echo "<b>Unable to remove '$dirname/' directory.</b><br />\n";
        $did_something = TRUE;
        return FALSE;
    }
}
/* vim: set expandtab tabstop=4 shiftwidth=4: */ ?>
