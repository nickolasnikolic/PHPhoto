<?php
/*
+----------------------------------------------------------------------+
| path_helper.php - A URL versus filesystem location de-mystification
| script
|
| This script is both a micro-tutorial and a utility for discovering
| the difference between a web script's URL and its filesystem location.
| I hope it will be useful for unraveling the mystery of what the two
| are.  The script will tell you its URL and corresponding filesystem
| location, along with similar information for the directory where
| the script is installed.
|
| There's only one setting.  Set $Enabled to TRUE and browse the
| script's URL (the script's what? :-) ) with your browser.
+----------------------------------------------------------------------+
| Copyright 2006 Hagan Fox
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
CVS: $Id: path_helper.php,v 1.1 2006/04/15 17:19:35 haganfox Exp $
*/

$Enabled = FALSE;

if (!$Enabled == TRUE) {
header('HTTP/1.0 403 Forbidden');
exit('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<HTML><HEAD><TITLE>403 Forbidden</TITLE></HEAD>
<BODY><H1>403 Forbidden</H1>
<P>This script is disabled.
</BODY></HTML>');
}
$FilePath = (isset($_SERVER['SCRIPT_FILENAME']))
  ? realpath($_SERVER['SCRIPT_FILENAME'])
  : __FILE__;
$FileDir  = dirname($FilePath);
$FileName = basename($FilePath);
$QueryString = (@$_SERVER['QUERY_STRING'])
  ? '?'.$_SERVER['QUERY_STRING']
  : '';
$SelfURL = (isset($_SERVER['PHP_SELF']))
  ? $_SERVER['PHP_SELF']
  : $_SERVER['REQUEST_URI'];
$SelfURI = (isset($_SERVER['SCRIPT_URI']))
  ? $_SERVER['SCRIPT_URI'].$QueryString
  : 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].$QueryString;
$URLParts = explode('?', $SelfURL);
$URLPath = $URLParts['0'];
$URLDir =  dirname($SelfURL);
$URIDir =  dirname($SelfURI.'null_text');
$urlbp = '$url_base_path';
$fsbp  = '$fs_base_path';
if (@$_GET['action'] == 'php_variables') {
  if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
    exit('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<HTML><HEAD><TITLE>phpinfo() disabled</TITLE></HEAD>
<BODY><P>Sorry, the phpinfo() function is disabled. <A HREF="'.$SelfURL.'"
title="Back to the URL and Filesystem Path De-mystifier">Back</a></BODY></HTML>');
  } else {
    phpinfo(32); exit;
  }
}
header("Content-type: text/html; charset=ISO-8859-1;");
echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>URL and Filesystem Path De-mystifier</title>
 <meta http-equiv='Content-Style-Type' content='text/css' />
 <style type='text/css'><!--
  body { margin:8px; color:black; background-color:white;
   font-family:Verdana,Helvetica,sans-serif; font-size:10.5pt; }
  h1, h2 { margin:0.85em 0px 0.25em 0px; border-top:1px solid #cccccc;
   border-left:1px solid #cccccc; padding:2px 2px 3px 3px;
   background-color:#f7f7f7; font-weight:normal; }
  h1 { font-size:1.62em; }
  h2 { font-size:1.35em; }
  p, ul, dl { margin:0.5em; }
  .pathdisplay { margin-left:25px; font-weight:bold; white-space:nowrap; }
  pre { font-family:'Lucida Console','Andale Mono','Courier New',Courier; 
  margin-left:25px; padding:4px; background-color:#f7f7f7; }
  --></style>
</head><body><div style='width:550px;'>

<h1 style='margin-top:0.25em;'>"URL" versus "Filesystem Location"</h1>
<p>A URL points to the location of <em>web content</em> on a network and
a filesystem path points to the location of a <em>file or directory</em>
on a computer's storage system.  There is a portion of a URL that represents
a path below the site's document root.</p>
<p>This helper script is for sorting out which is which on a particular
server so you can configure a web script with a URL path and a filesystem
path that correspond to one another.</p>

<h2>URL and filesystem location for <em>$FileName</em></h2>
<p>The URL that was used to browse this page is:<br />
 <span class='pathdisplay'>$SelfURI</span></p>
<p>The <em>path portion of the URL</em> is:<br />
 <span class='pathdisplay'>$URLPath</span></p>
<p><em>$FileName</em>'s location on the filesystem is:<br />
 <span class='pathdisplay'>$FilePath</span></p>
<p><em>$FileName</em> is in this directory:<br />
 <span class='pathdisplay'>$FileDir/</span></p>
<p>The corresponding URL for the directory is:<br />
 <span class='pathdisplay'>$URIDir/</span></p>
<p>The <em>path portion of the URL</em> is:<br />
 <span class='pathdisplay'>$URLDir/</span></p>

<h2>Qdig settings</h2>
<p>Qdig settings for a gallery in the same directory would be:
</p><pre>
$urlbp = '$URLDir/';
$fsbp  = '$FileDir/';
</pre>

<h2>Glossary</h2>
<ul><li>A <em>URL</em> is a web address that you can reach with
 your web browser.
</li><li>A <em>filesystem path</em> is a location on the server's
 filesystem.
</li><li>The <em>path portion of a URL</em> is the portion
 that <em>looks like</em> a filesystem path.  It starts with
 <strong>/</strong> immediately following server's name
 ({$_SERVER['HTTP_HOST']} for this server).
 If there's a <a href="$SelfURL?param=value"
 title="This page with a query string"> query string</a>, the path
 portion doesn't include the <strong>?</strong> or anything after.
 If there's a segment identifier, the path portion doesn't include
 the <strong>#</strong> or anything after.
</li></ul>

<h2>About this script</h2>
<p>This script uses <a href="$SelfURL?action=php_variables"
title="phpinfo() Variables page">PHP variables</a> that can be
inconsistent from server to server.  Care has been taken for the
script to work as universally as possible, but the results might
not be 100% reliable.  If you don't see the results you expect,
<a href='http://sourceforge.net/forum/forum.php?forum_id=237740'
 title='Qdig Open Discussion forum'>let me know</a>.</p>
<p>The script has it's own 
<a href='http://qdig.sourceforge.net/Support/URLAndFilesystemPaths'>page</a>
on the <a href='http://qdig.sourceforge.net/'>Qdig site</a>
where further information may be posted.</p>
<p style='margin-left:30px;'>-Hagan Fox</p>
</div></body></html>
EOT;

