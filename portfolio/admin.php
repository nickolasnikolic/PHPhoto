<?php
/*
=========================================================================
Qdig Gallery Management Script

This scirpt was was originally useful for managing collections of images
in Qdig galleries.  It's no longer necessary for most Qdig installations,
but it can provide a conveient way to edit captions on-the-fly directly
from Qdig image-display pages.  Starting with Qdig 1.2.0 the best way to
manage the images in your Qdig galleries is to use your favorite FTP or
SCP file management program.

Copyright (c) 2002, 2003, 2004, 2005 Hagan Fox
 * Original AnyPortal (c) 1999 John Martin (http://www.anyportal.com)
 * AnyPortal(php) by Stefan@Wiesendanger.org (http://nger.org/anyportal/)
 * phpFileFarm (c) 2001 - Jason Hines <jason@greenhell.com>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, visit http://www.gnu.org/licenses/gpl.txt
===========================================================================
$Id: admin.php,v 1.27 2006/01/07 08:42:15 haganfox Exp $
*/

class FileFarm {

	var $title, $allow_edit, $show_hidden, $allowed_types, $size_limit,
		$new_days, $image_types, $date_format, $file_perms, $umask, $dir_perms,
		$base_dir, $web_dir, $rel_dir, $rel_path, $self;

	function FileFarm() {

		$this->version  = '20050430-1';

		/*== REQUIRED SETTINGS ==========================================*/
		// You need to either enter a username and password or else
		// disable authentication for the script to work.

		// Enable built-in authentication
		$this->use_authentication = TRUE;
		
		// User name and password for authentication
		$this->user_name    = '';
		$this->password     = '';

		/*== QDIG-SPECIFIC SETTINGS =====================================*/

		// Customize this script for a use on a Qdig gallery.
		$this->qdig_gallery_mode = TRUE;

		// The fllowing two settings are for Qdig =< 1.0
		// Careate a .txt caption file automatically on image upload?
		$qdig_autocreate_caption_file = FALSE;

		// delete the .txt caption file automatically on image delete?
		$qdig_autodelete_caption_file = FALSE;

		// relative web path to Qdig script (no ending slash)
		//$this->qdig_file  = '/account-name/photos/qdig.php';
		//$this->qdig_file  = '';
	
		/*== OPTIONAL SETTINGS ==========================================*/
		// Some custom settings...
	
		// Supress (harmless) php 'Notice: Undefined ...' error output.
		//error_reporting(E_ALL ^E_NOTICE);

		// Report all errors (for debugging)
		//error_reporting(E_ALL);
	
		// full path to base directory (no ending slash)
		$this->base_dir = '/home/www/portfolio';
		//$this->base_dir = '';

		// relative web path to base directory (no ending slash)
		//$this->web_dir  = '/account-name/photos';
		//$this->web_dir  = '';
	
		// title of your file farm
		$this->title = 'Qdig Gallery Management';

		// files you want to be able to edit in text mode
		// and view with (primitive) syntax highlighting
		// $this->allow_edit = array('.txt','.htm','.html','.cfm','.php3','.php','.phtml','.shtml','.css','.xml','.pl');
		$this->allow_edit = array('.txt');

		// whether or not to show hidden files (dot filenames)
		$this->show_hidden = TRUE;

		// display directory summaries
		$this->show_summary = TRUE;

		// convert path names to breadcrumbs?
		$this->breadcrumbs = TRUE;

		// mimetypes that you want to allow for uploading
		// Set to FALSE (or comment out) to disable file type validation.
		// $this->allowed_types = array('text/plain','text/html','application/x-zip-compressed');
		$this->allowed_types = array('image/jpeg','image/pjpeg','image/gif','image/x-png','image/png','image/x-png','image/bmp');

		// list of files/directories to specifically exclude from listing
		$this->excluded = array('.htaccess','robots.txt','favicon.ico');

		// maximum file size allowed for uploading (in bytes)
		$this->size_limit = '5000000';

		// number of days a file is considered NEW
		$this->new_days = 7;

		// files that will display as images on the detail page
		$this->image_types = array('.jpg','.jpeg','.gif','.png','.ico','.bmp','.xbm');

		// how we format dates
		$this->date_format = 'm/d/y h:i:s A';

		// umask setting for default newly-created file and directory permissions.
		// TODO uploaded files are executable, but touch() works as expected
		// Examples: umask(002)  // `drwxrwxr-x' and `-rw-rw-r--' (world readable)
		//           umask(007)  // `drwxrwx---' and `-rw-rw----' (not world readable)
		$this->umask  = 0002;
		// default permissions for uploaded files (int)
		$this->file_perms = 0664;
		// default permissions for created directories (int)
		$this->dir_perms  = 0775;


		/*== SKIN / THEME DEFINITION ========================================*/
	
		$this->siteWidth      = '100%';
		$this->bodyBgColor    = 'ffffff';
		$this->bodyTextColor  = '000000';
		$this->bodyLinkColor  = 'cc3333';
		$this->bodyVlinkColor = '990000';
		$this->bodyAlinkColor = '000000';
		$this->bodyMarginSize = '3';
		$this->headDiv        = '';
		$this->menubarColor   = '000000';
		$this->siteNameColor  = 'ffffff';
		$this->menubarDiv     = '';
		$this->bodyFgColor    = 'ffffff';
		$this->formStyle      = '';
		$this->bodyFgDiv      = '000000,1';
		$this->bodyFgToRowDiv = '000000,1';
		$this->rowColor1      = 'eeeeee';
		$this->rowColor2      = 'f0f0f0';
		$this->rowLinkColor   = 'cc3333';
		$this->rowTextColor   = '000000';
		$this->newFileColor   = 'ff0000';
		$this->rowDiv         = '000000,1';
		$this->rowToBodyFgDiv = '000000,1';
		$this->footDiv        = '000000,1';

		/*== NO NEED TO EDIT PAST THIS POINT ================================*/

		// TODO: Add debug_mode.
		// TODO: Add language settings.

		umask($this->umask);

		// Set variables according to version of PHP attempt to
		// deal with `register_globals Off'
		if (! isset($_POST)) {
			global $HTTP_POST_VARS;
			$this->post_vars=$HTTP_POST_VARS;
		} else {
			$this->post_vars=$_POST;
		}
		if (! isset($_GET)) {
			global $HTTP_GET_VARS;
			$this->get_vars=$HTTP_GET_VARS;
		} else {
			$this->get_vars=$_GET;
		}
		if (! isset($_FILES)) {
			global $HTTP_POST_FILES;
			$this->post_files=$HTTP_POST_FILES;
		} else {
			$this->post_files=$_FILES;
		}
		if (! isset($_SERVER)) {
			global $HTTP_SERVER_VARS;
			$this->self=$HTTP_SERVER_VARS['PHP_SELF'];
			$this->auth_user=@$HTTP_SERVER_VARS['PHP_AUTH_USER'];
			$this->auth_pw=@$HTTP_SERVER_VARS['PHP_AUTH_PW'];
			$this->user_agent=$HTTP_SERVER_VARS['HTTP_USER_AGENT'];
			$this->script_filename=$HTTP_SERVER_VARS['SCRIPT_FILENAME'];
			$this->script_name=$HTTP_SERVER_VARS['SCRIPT_NAME'];
			$this->http_referer=@$HTTP_SERVER_VARS['HTTP_REFERER'];
		} else {
			$this->self=$_SERVER['PHP_SELF'];
			$this->auth_user=@$_SERVER['PHP_AUTH_USER'];
			$this->auth_pw=@$_SERVER['PHP_AUTH_PW'];
			$this->user_agent=$_SERVER['HTTP_USER_AGENT'];
			$this->script_filename=$_SERVER['SCRIPT_FILENAME'];
			$this->script_name=$_SERVER['SCRIPT_NAME'];
			$this->http_referer=@$_SERVER['HTTP_REFERER'];
		}

		$this->file     = stripslashes(@$this->get_vars['F']);
		
		if (@$this->get_vars['R'] == 'Qdig') {
			$this->qdig_refer = TRUE;
		}

		// Set base_dir and web_dir to current dir if none was provided
		if (!isset($this->base_dir)) {
			$this->base_dir = dirname($this->script_filename);
		}
		if (!isset($this->web_dir)) {
			$this->web_dir = dirname($this->script_name);
		}

		if (empty($this->qdig_file)) {
			$this->qdig_file = "{$this->web_dir}/index.php";
		}

		// check for authentication first thing if enabled
		if ($this->use_authentication == TRUE) {
			$this->login_auth = array($this->user_name => $this->password);
		}
		if (@$this->login_auth) {
			if (!empty($this->auth_user)
				&& !empty($this->auth_pw))
			{
				if ($this->login_auth[$this->auth_user] != $this->auth_pw) {
					$this->authenticate();
				}
			} else {
				$this->authenticate();
			}
		}

		// check for skin.txt and load if it exists
		$this->load_skin();

		// determine working directory
		if (@$this->post_vars['DIR']) {
			$this->rel_dir = $this->post_vars['DIR'];
		} elseif (@$this->get_vars['D']) {
			$this->rel_dir = rawurldecode($this->get_vars['D']);
		} else {
			$this->rel_dir = '';
		}

		if ($this->rel_dir=='/') {
			$this->rel_dir = '';
		}

		$this->base_dir = stripslashes($this->base_dir);
		$this->rel_dir  = stripslashes($this->rel_dir);
		$this->rel_path = $this->base_dir.$this->rel_dir;

		if (@strstr($this->rel_dir,'..')) {
			$this->Error('No up-folders allowed'); // Important
		} elseif (!is_dir($this->rel_path)) {
			$this->Error('Folder not found',$this->rel_dir);
		}

		switch (@$this->post_vars['POSTACTION']) {
			case 'UPLOAD' :
				if (!is_writable($this->rel_path)) {
					$this->Error('Could not write to folder',$this->rel_path);
				}
				$file = stripslashes($this->post_files['FN']['name']);
				$type = $this->post_files['FN']['type'];
				$size = $this->post_files['FN']['size'];
				$temp = $this->post_files['FN']['tmp_name'];

				if (is_uploaded_file($temp)) {
					if ($size <= $this->size_limit) {
						if ($this->allowed_types==FALSE
							|| in_array($type,$this->allowed_types))
						{
							$target = $this->rel_path.'/'.$file;
							if (move_uploaded_file($temp,$target)) {
								chmod($target,$this->file_perms);
								// success
								// Qdig feature -- Add a text file for caption TODO: Improve this.
								//if (! file_exists($target.'.txt')
								//	&& $this->qdig_gallery_mode == TRUE
								//	&& $qdig_autocreate_caption_file == TRUE)
								//{
								//	touch($target.'.txt');
								//}
							} else {
								$this->Error('Could not move uploaded file',$target);
							}
						} else {
							$this->Error('File type not allowed',$type);
						}
					} else {
						$this->Error('Max file size exceeded','$size exceeds $this->size_limit');
					}
				}
				clearstatcache();
			break;

			case 'SAVE' :
				if (@strstr($this->post_vars['RELPATH'],'..')) {
					$this->Error('No up-folders allowed'); // Important
				}
				$path = stripslashes($this->base_dir.$this->post_vars['RELPATH']);
				$writable = is_writable($path);
				$legaldir = is_writable(dirname($path));
				$exists   = (file_exists($path)) ? 1 : 0;
				// possibly check for legal extension here as well
				if (!($writable
					|| (!$exists
						&& $legaldir)))
				{
					$this->Error('Could not write to file',$path);
				}
				$fh = fopen($path,'w');
				fwrite($fh,stripslashes($this->post_vars['FILEDATA']));
				fclose($fh);
				clearstatcache();
			break;

			case 'CREATE' :
				if (!is_writable($this->rel_path)) {
					$this->Error('Could not write to folder',$this->rel_path);
				}
				$file = stripslashes($this->post_vars['FN']);
				$path = $this->rel_path.'/'.$file;
				// check for invalid (excluded) file/dir names
				if ($file
					&& strstr(join(' ',$this->excluded),$file))
				{
					$this->Error('Could not write file',$file.' is a reserved name');
				}
				switch ($this->post_vars['T']) {
					case 'D' :  // create a directory
						if (!@mkdir($path,$this->dir_perms) || empty($file)) {
							$this->Error('Could not create folder or folder already exists',$path);
						}
					break;
					case 'F' :  // create a new file
						if (file_exists($path) || !is_writable(dirname($path))) {
							$this->Error('Could not write to file or file already exists', $path);
						}
						$tstr = $this->self.'?op=details&D='.$this->rel_dir.'&F='.$file;
						header('Location: '.$tstr);
						exit;
					break;
				}
			break;

			case 'DELETE' : 
				if ($this->post_vars['CONFIRM'] != 'on') break;

				$tstr  = 'Attempt to delete non-existing object or ';
				$tstr .= 'insufficient privileges: ';

				$file = stripslashes($this->post_vars['FN']);
				$caption_file = $this->rel_path.'/'.$file.'.txt';
				if (!empty($file)) {  // delete file
					$path =  $this->rel_path.'/'.$file;
					$caption_path =  $this->rel_path.'/'.$caption_file;
					if (!@unlink($path)) {
						$this->Error('Could not remove file', $tstr.$path);
						exit;
					}
					if (file_exists($caption_file)
						&& $this->qdig_gallery_mode == TRUE
						&& $qdig_autodelete_caption_file == TRUE
						&& !@unlink($caption_file))
					{
						$this->Error('Could not remove file', $tstr.$caption_file);
						exit;
					}
				} else {  // delete directory
					if (!@rmdir($this->rel_path)) {
						$this->Error('Could not remove folder', $tstr.$this->rel_path);
					} else {
						$this->rel_path = dirname($this->rel_path);  // move up
						$this->rel_dir  = dirname($this->rel_dir);
					}
				}
			break;

			default:
				// user hit "CANCEL" or undefined action
			break;
			}

		// redirect to directory view if posted
		if (!empty($this->post_vars['POSTACTION'])) {
			if (@$this->post_vars['QREFER'] == TRUE) {
				$loc = $this->post_vars['REFER'];
			} else {
				$loc = $this->self. '?&D='.urlencode($this->rel_dir);
			}
			header('location:'.$loc);
			exit;
		}

		// determine operation if passed
		switch (@$this->get_vars['op']) {
			case 'details':
				$this->DetailPage();
				exit;
			case 'view':
				$this->DisplayCode();
				exit;
			case 'download':
				$this->Download();
				exit;
		}

		// default: display directory $rel_path
		$this->Navigate();
	} // end function FileFarm()

	/**
	* Output the document header
	*/
	function head($title,$text='') {
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
 <meta http-equiv="Content-Language" content="en"/>
 <meta http-equiv="Content-Style-Type" content="text/css"/>
 <meta http-equiv="expires" content="0" />
 <meta http-equiv="Pragma" content="no-cache" />
 <meta name="description" content="Remote Administration Page"/>
 <meta name="robots" content="noindex" />
 <meta name="MSSmartTagsPreventParsing" content="true" />
<?php
		// Avoid log spam in case the icon isn't present
		if (is_file('qdig-admin.ico') && is_readable('qdig-admin.ico')) {
?>
 <link rel="icon" href="qdig-admin.ico" type="image/x-icon">
 <link rel="shortcut icon" href="qdig-admin.ico" type="image/x-icon">
<?php
		}
?>
 <title><?php echo $title?></title>
 <style type="text/css"> <!--
  body,td,p,h1,h2,h3,h4,form { font-family:Helvetica,Arial,sans-serif; color: #<?php echo $this->bodyTextColor?>; font-size 12pt }
  textarea,pre,tt { color:<?php echo $this->rowTextColor?>; font-family:monospace,Lucida Console,Courier New,Courier,fixed; font-size: 10pt }
  body { background-color: #<?php echo $this->bodyBgColor?>; margin: <?php echo $this->bodyMarginSize?>}
  em { color:<?php echo $this->newFileColor?>; font-style:normal; }
  a:link { color: #<?php echo $this->rowLinkColor?>; }
  a:visited { color: #<?php echo $this->bodyVlinkColor?>; }
  a:active a:hover { color: #<?php echo $this->bodyAlinkColor?>; }
  .black { color:black; }
  .NEW { color:<?php echo $this->newFileColor?>; }
  .top { color:<?php echo $this->bodyTextColor?>; font-size:10pt; font-weight: bold;}
  .INV { color:<?php echo $this->siteNameColor?>; background-color:<?php echo $this->menubarColor?>; font-size:14pt; font-weight: bold;}
  .ROW1 { background-color:#<?php echo $this->rowColor1?>; color:<?php echo $this->rowTextColor?>;}
  .ROW2 { background-color:#<?php echo $this->rowColor2?>; color:<?php echo $this->rowTextColor?>;}
  .BAR { background-color:#<?php echo $this->bodyFgColor?>; }
  .bottom { color:<?php echo $this->bodyTextColor?>; font-size:11px; text-decoration: none;}
  .REM { color:silver; }
  .XML { color:navy; background-color:yellow; } -->
 </style>
</head>

<body>
 <?php echo $this->colorbars($this->headDiv)?>
 <table summary="Qdig Gallery Management Title" border=0 cellspacing=0 cellpadding=2 width="<?php echo $this->siteWidth?>">
  <tr>
   <td class="INV"><?php echo $this->icon("opendir",$title)?> <?php echo $title?>   </td>
  </tr>
 </table>
 <?php echo $this->colorbars($this->menubarDiv)?>
 <table summary="Qdig Gallery Management Body" border=0 cellspacing=0 cellpadding=2 width="<?php echo $this->siteWidth?>">
  <tr>
   <td bgcolor="<?php echo $this->bodyFgColor?>">
   <div><?php echo $text?></div>
	<?php
	} // end function head()

	/**
	* Output the document footer
	*/
	function foot() {
		print "</td></tr></table>\n";
		$this->colorbars($this->footDiv);
		print " <!-- Footer -->\n <div class=\"bottom\" align=\"right\">\n";
		print " <a href=\"http://qdig.sourceforge.net/index.php/Qdig/AdministrationScript\"\n";
		print "  title=\"Qdig Admin Home\">Qdig Admin</a>\n";
		print " </div>\n";
		print "</body>\n</html>\n";
	} // end function foot()

	/**
	* Produce a View/Modify page.
	*/
	function DetailPage() {

		$path         = $this->rel_path.'/'.$this->file;
		$relpath      = $this->rel_dir.'/'.$this->file;
		$gallery_mode = $this->qdig_gallery_mode;

		if ($this->qdig_refer == TRUE && !is_file($path)) {
			touch($path);
			chmod($path, $this->file_perms);
		}
		$exists   = file_exists($path);
		$ext      = strtolower(strrchr($path,'.'));
		$editable = ($ext=='' || strstr(join(' ',$this->allow_edit),$ext));
		$writable = is_writable($path);

		if (!$editable
			&& !$exists)
		{
			$this->Error('Creation unsupported for file type',$path);
		}
		if (!$exists
			&& !$writeable)
		{
			$this->Error('Creation denied',$path);
		}
		if ($gallery_mode == TRUE) {
			if (strstr($this->http_referer, 'Qwd=')) {
				$back_link = $this->http_referer;
			} else {
				$back_link = $this->web_dir;
			}
			$text  = 'Use this page to view/delete an image or modify/delete ';
			$text .= "a comment in <a href=\"$back_link\">your Qdig gallery</a><br /><br />";
		} else {
			$text  = 'Use this page to view/delete a file or modify ';
			$text .= 'a document.<br /><br />';
		}
			$this->head('View/Edit', $text);
		?>
 <table summary="View/Modify Page Header" cellpadding=2 cellspacing=1 border=0 width="100%">
  <tr>
   <td><?php echo $this->colorbars($this->bodyFgToRowDiv);?>   </td>
  </tr>
  <tr>
   <td class="ROW2">
    <table summary="Cancel Button and Filename">
     <tr>
      <td>
       <!-- Cancel View / Edit -->
       <form action="<?php echo $this->self;?>" method="POST">
        <input type="SUBMIT" name="POSTACTION" value="Cancel">
       </form>
      </td>
      <td valign="top">
       <?php echo '<big><strong>&nbsp;File: '.$this->rel_dir.'/'.$this->file.'</strong></big>';?>
      </td>
     </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td><?php echo $this->colorbars($this->rowToBodyFgDiv);?>   </td>
  </tr>
 </table>
<?php
		/**
		* Edit the document or, if file is an image, display it.
		*/
		// begin Edit Document
		if ($editable
			&& ($writable
				|| !$exists))
		{
			if ($exists
				&& filesize($path) == 0)
			{
				$fstr = '';
			} else {
				$fh = fopen($path,'a+');
				rewind($fh);
				$fstr = fread($fh,filesize($path));
				fclose($fh);
			}
			$fstr = htmlspecialchars($fstr);
?>
 <table summary="Document Contents" cellpadding=2 cellspacing=1 border=0 width="100%">
  <tr>
   <td>
    <form action="<?php echo $this->self;?>" method="POST">
     <strong>EDIT DOCUMENT</strong>
     <br />
     <textarea name="FILEDATA" rows=15 cols=78 wrap=OFF><?php echo $fstr;?></textarea>
     <input type="HIDDEN" name="DIR" value="<?php echo $this->rel_dir;?>">
     <input type="HIDDEN" name="FN" value="<?php echo $this->file;?>">
     <input type="HIDDEN" name="REFER" value="<?php echo $this->http_referer;?>">
     <input type="HIDDEN" name="QREFER" value="<?php echo $this->qdig_refer;?>">
     <input type="HIDDEN" name="POSTACTION" value="SAVE">
     <br />
     Save As:
     <input type="TEXT" size=48 maxlength=255 name="RELPATH" value="<?php echo $relpath;?>">
     <input type="RESET" value="Reset">
     <input type="SUBMIT" value="Save">
    </form>
   </td>
  </tr>
 </table>
<?php
		// End Edit Document
		// begin Display Image
		} elseif (strstr(join(' ',$this->image_types),$ext)) { 
			$info  = getimagesize($this->base_dir.$relpath);
			$dim   = $info[0] .' x '. $info[1];
			$tstr  = "<img src=\"". $this->web_dir.$relpath."\" border=0 ";
			$tstr .= $info[3]." alt=\"".$this->file." (".$dim.")\">";
			echo $tstr;
		} // end Display Image
?>

<?php
		/**
		* Show a `Delete File' table if applicable.
		*/
		if ($exists
			&& $writable)
		{
?>
 <table summary="Delete File" cellpadding=2 cellspacing=1 border=0 width="100%">
  <tr>
   <td><?php echo $this->colorbars($this->bodyFgToRowDiv);?>   </td>
  </tr>
  <tr>
   <td class="ROW2">
    <form action="<?php echo $this->self;?>" method="POST">
     <input type="HIDDEN" name="DIR" value="<?php echo $this->rel_dir;?>">
     <input type="HIDDEN" name="FN" value="<?php echo $this->file;?>">
     <strong>Delete "<?php echo $this->file;?>"?</strong>
     <input type="CHECKBOX" name="CONFIRM">
     <input type="SUBMIT" name="POSTACTION" value="DELETE">
    </form>
   </td>
  </tr>
  <tr>
   <td><?php echo $this->colorbars($this->rowToBodyFgDiv);?>   </td>
  </tr>
 </table>
<?php
		} // end Delete File table
	
		/**
		*Show the File Details.
		*/
		if ($exists) {  // get file info
?>
 <table summary="File Details" cellpadding=2 cellspacing=1 border=0 width="100%">
  <tr>
   <td><?php echo $this->colorbars($this->bodyFgToRowDiv);?>   </td>
  </tr>
  <tr>
   <td class="ROW2">
<?php
			$fsize = filesize($path);
			$fmodified = date($this->date_format, filemtime($path));
			$faccessed = date($this->date_format, fileatime($path));
			if (function_exists('posix_getpwuid')) { $owner = posix_getpwuid(fileowner($path)); }
			if (function_exists('posix_getgrgid')) { $group = posix_getgrgid(filegroup($path)); }
			echo "<pre><strong>File details:</strong>\n";
			echo "<small>    file size: <strong>".$this->fixsize($fsize)." (".$fsize." bytes)</strong>\n";
			echo "last modified: <strong>".$fmodified."</strong>\n";
			echo "last accessed: <strong>".$faccessed."</strong>\n";
			if (function_exists('posix_getpwuid')) { echo "        owner: <strong>".$owner['name']." (".$owner['gecos'].')'."</strong>\n"; }
			if (function_exists('posix_getgrgid')) { echo "        group: <strong>".$group['name']."</strong>\n"; }
			echo "  permissions: <strong>".$this->display_perms($path)."</strong>";
			echo "</small></pre>\n";
			clearstatcache();
?>
   </td>
  </tr>
  <tr>
   <td><?php echo $this->colorbars($this->rowToBodyFgDiv);?>   </td>
  </tr>
 </table>
<?php
		} // end File Details

		$this->foot();
	} // end function DetailPage()

	/**
	* Display the source of a document.
	*/
	function DisplayCode() {

		$path = $this->rel_path.'/'.$this->file;

		if (!file_exists($path)) {
			$this->Error('File not found',$path);
		}

		$this->head('Viewing file: '.$this->rel_dir.'/'.$this->file,'');

		// show_source($path);
		$tstr = join('',file($path));
		$tstr = htmlspecialchars($tstr);

		// Tabs
		$tstr = str_replace(chr(9),'   ',$tstr) ; 

		// ASP tags & XML/PHP tags
		$aspbeg = "<span class=\"XML\">&lt;%</span><span class=\"black\">";
		$aspend = "</span><span class=\"XML\">%&gt;</span>";
		$tstr = str_replace('&lt;%',$aspbeg,$tstr);
		$tstr = str_replace('%&gt;',$aspend,$tstr);

		$xmlbeg = "<span class=\"XML\">&lt;?</span><span class=\"black\">";
		$xmlend = "</span><span class=\"XML\">?&gt;</span>";
		$tstr = str_replace('&lt;?',$xmlbeg,$tstr);
		$tstr = str_replace('?&gt;',$xmlend,$tstr);

		// C style comment
		$tstr = str_replace('/*','<span class=\'REM\'>/*',$tstr);
		$tstr = str_replace('*/','*/</span>',$tstr);

		// HTML comments
		$tstr = str_replace("&lt;!--","<i class=\"RED\">&lt;!--",$tstr) ; 
		$tstr = str_replace("--&gt;","--&gt;</i>",$tstr) ; 


		$this->colorbars($this->bodyFgToRowDiv);
		?>
 <table cellspacing=1 cellpadding=0 border=0 width="100%">
  <tr>
   <td class="ROW2">
   <br />
<?php

		echo "   <pre>\n";

		$tstr = split("\n",$tstr);
		for ($i = 0 ; $i < sizeof($tstr) ; ++$i) {
			// add line numbers
			echo "<br /><EM>";
			echo substr(("000".($i+1)), -4).":</EM> ";
			$line = $tstr[$i];
			// C++ style comments
			$pos = strpos($line,"//");
			// exceptions: two slashes aren't a script comment
			if (strstr($line,"//")
				&& ! ($pos>0 && substr($line,$pos-1,1)==":")
				&& ! (substr($line,$pos,8) == "//--&gt;")
				&& ! (substr($line,$pos,9) == "// --&gt;"))
			{
				$beg = substr($line,0,strpos($line,"//"));
				$end = strstr($line,"//");
				$line = $beg."<span class=\"REM\">".$end."</span>";
			}
			// shell & asp style comments
			$first = substr(ltrim($line),0,1);
			if ($first == "#"
				|| $first == "'")
			{
				$line = "<span class=\"REM\">".$line."</span>";
			}
			print($line);
		} // next i

		echo "   </pre>\n";
		echo "  </td></tr>\n </table>\n";
		echo " </tr></table>\n";
		$this->colorbars($this->rowToBodyFgDiv);
?>
 <form method="POST" action="<?php echo $this->self?>">
  <input type="HIDDEN" name="DIR" value="<?php echo $this->rel_dir?>"><br />
  <input type="SUBMIT" name="POSTACTION" value="Cancel">
 </form>
<?php
		$this->foot();

	} // end function DisplayCode()

	/*
	* Download a file.
	*/
	function Download() {
		$path = $this->base_dir.$this->rel_dir."/".$this->file;
		//SetCookie("Download",yep, time()+36000000, "/", "www.domain.com", 0);
		$size = filesize($path);
		//header("Content-Type: application/octet-stream");
		header("Content-Type: application/force-download");
		header("Content-Length: $size");
		// IE5.5 just downloads index.php if we don't do this TODO: What about IE6?
		if(preg_match("/MSIE 5.5/", $this->user_agent)) {
			header("Content-Disposition: filename=$this->file");
		} else {
			header("Content-Disposition: attachment; filename=$this->file");
		}
		header("Content-Transfer-Encoding: binary");
		$fh = fopen($path, "r");
		fpassthru($fh);
	} // end function Download()

	/*
	* Display an <img> link to an appropriate icon based ont $txt.
	*/
	function icon($txt,$alt='') {
		switch (strtolower($txt)) {
		case '.bmp' :	case '.gif' :	case '.png' :
		case '.jpg' :	case '.jpeg':	case '.tif' :
		case '.tiff':
			$d = 'image2.gif';
			break;
		case '.doc' :
			$d = 'layout.gif';
			break;
		case '.exe' :	case '.com'	:	case '.bin'	:	case '.bat' :
			$d = 'binary.gif';
			break;
		case '.hqx' :
			$d = 'binhex.gif';
			break;
		case '.bas' :	case '.c'   :	case '.cc'  :
		case '.src' :
			$d = 'c.gif';
			break;
		case 'file' :
			$d = "generic.gif";
			break;
		case 'dir' :
			$d = 'dir.gif';
			break;
		case 'opendir' :
			$d = 'folder.open.gif';
			break;
		case '.phps' :	case '.php3' :	case '.htm' :	case '.html':
		case '.asa' :	case '.asp' :	case '.cfm' :	case '.php3':
		case '.php' :	case '.phtml' :	case '.shtml' :
			$d = 'world1.gif';
			break;
		case '.pl'	:	case '.py'	:
			$d = 'p.gif';
			break;
		case '.wrl'	:	case '.vrml':	case '.vrm'	:	case '.iv'	:
			$d = 'world2.gif';
			break;
		case '.ps'	:	case '.ai'	:	case '.eps'	:
			$d = 'a.gif';
			break;
		case '.pdf' :
			$d = 'pdf.gif';
			break;
		case '.txt' :	case '.ini' :
			$d = 'text.gif';
			break;
		case '.xls' :
			$d = 'box2.gif';
			break;
		case '.dvi'	:
			$d = 'dvi.gif';
			break;
		case '.mpg' :	case '.mpeg':
			$d = 'movie.gif';
			break;
		case '.aiff':	case '.wav'	:	case '.it'	:	case '.mp3' :
			$d = 'sound2.gif';
			break;
		case '.conf':	case '.cfg':	case '.scr':	case '.sh':
		case '.shar':	case '.csh':	case '.ksh':	case '.tcl':
			$d = 'script.gif';
			break;
		case '.tar' :	case '.zip' :	case '.arc' :	case '.sit' :
		case '.gz'  :	case '.tgz' :	case '.Z'   :
			$d = 'compressed.gif';
			break;
		case 'view' :
			$d = 'index.gif';
			break;
		case 'box'	:
			$d = 'box1.gif';
			break;
		case 'up' :
			$d = 'back.gif';
			break;
		case 'blank' :
			$d = 'blank.gif';
			break;
		default :
			$d = 'unknown.gif';
		}

		return "<img src=\"/icons/".$d."\" alt=\"".$alt."\" border=0>";
	} // end function icon()

	function Navigate() {
		if (!is_dir($this->rel_path)) {
			$this->Error('Folder not found',$this->rel_path);
		}

		if (!($dir = @opendir($this->rel_path))) {
			$this->Error('Could not read folder',$this->rel_path);
		}

		$dirList = array();
		$hiddenFiles = array();
		$fileList = array();

		// read directory contents
		while ($item = readdir($dir)) {
			if ($item == '..' || $item == '.' ) continue;
			if (strstr(join(' ',$this->excluded),$item)) continue; // excluded
			if (is_dir($this->rel_path.'/'.$item)) {
				// directory
				$dirList[] = $item;
			} elseif (is_file($this->rel_path.'/'.$item)) {
				// file
				if (!$this->show_hidden
					&& substr($item,0,1)=='.')
				{
					// hidden file, do nothing
					$hiddenFiles[] = $item;
				} elseif ($this->qdig_gallery_mode == TRUE
					&& ! eregi("\.jpg$|\.jpeg$|\.jpe$|\.png$|\.gif$|\.bmp$|\.jpg.txt$|\.jpeg.txt$|\.jpe.txt$|\.png.txt$|\.gif.txt$|\.bmp.txt$",$item))
				{
					// hide all but image files for Qdig
					$hiddenFiles[] = $item;
				} else {
					$fileList[] = $item;
				}
			} else {
				// unknown
				// $this->Error("Unknown file type", $text.$this->rel_path."/".$item);
			}
		}
		closedir($dir);
		$emptyDir = !(sizeof($dirList) || sizeof($fileList) || sizeof($hiddenFiles));

		// start navigation page
		if ($this->qdig_gallery_mode == TRUE) {
			$text  = "Use this page to add or delete images and edit or\n";
			$text  .= " delete captions in <a href=\"{$this->qdig_file}\">your Qdig gallery</a>.<br />&nbsp;<br />\n";
		} else {
			$text  = "Use this page to view, add, delete or modify files.<br />&nbsp;<br />\n";
		}

		$this->head($this->title,$text);

		echo '<table border=0 cellpadding=1 cellspacing=1 width="'.$this->siteWidth.'">';


		// path location bar
		if ($this->base_dir != $this->rel_path) {
			$parent = dirname($this->rel_dir);
?>
  <tr>
   <td colspan=5><?php echo $this->colorbars($this->bodyFgToRowDiv);?>   </td>
  </tr>
  <tr>
   <td align=center class="ROW1"><?php echo $this->icon("opendir",$this->rel_dir)?>   </td>
   <td nowrap colspan=4 width="100%" class="ROW1">
<?php
		if ($this->breadcrumbs) {
			echo $this->path2bc($this->rel_dir);
		} else {
			echo '<a href="'.$this->self.'?&amp;D='.urlencode($parent).'">'.$this->rel_dir.'</a>';
		}
		?>
   </td>
 </tr>
 <tr>
  <td colspan=5><?php echo $this->colorbars($this->rowToBodyFgDiv);?>   </td>
 </tr>
<?php
		} // end parent bar

		$BG = array('ROW1','ROW2');

		// output subdirs list
		if (sizeof($dirList)>0) {
			sort($dirList);
?>
  <tr>
   <td colspan=5 class="top">FOLDERS</td>
  </tr>
  <tr>
   <td colspan=5><?php echo $this->colorbars($this->bodyFgToRowDiv);?>   </td>
  </tr>
<?php
			// iterate over dirs
			$i=0;
			while (list($key,$dir) = each($dirList)) {
				$i++;
				$bgs  = $BG[$i % 2];  // even or odd
				$tstr = '<a href="'.$this->self.'?&amp;D=';
				$tstr .= urlencode($this->rel_dir.'/'.$dir);
				$tstr .= '">'.$dir.'/</a>';
?>
  <tr>
   <td class="<?php echo $bgs?>" align=center>
    <a href="<?php echo $this->self?>?&amp;D=<?php echo urlencode($this->rel_dir.'/'.$dir)?>"><?php echo $this->icon('dir',$dir.'/')?></a>
   </td>
   <td nowrap width="100%" colspan=4 class="<?php echo $bgs?>">
    <a href="<?php echo $this->self?>?&amp;D=<?php echo urlencode($this->rel_dir.'/'.$dir)?>"><?php echo $dir?>/</a>
   </td>
  </tr>
  <tr>
   <td colspan=5><?php echo $this->colorbars($this->bodyFgDiv)?>   </td>
  </tr>
<?php
			}  // end iterate over dirs
		}  // end output subdirs list
	
?>
  <tr><td colspan=5><?php echo $this->colorbars($this->bodyFgToRowDiv);?>  </td></tr>
  <tr>
   <td colspan="5">
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
     <tr class="BAR">
      <form method="POST" action="<?php echo $this->self?>">
       <td>&nbsp;New</td>
       <td nowrap>
        <input type="RADIO" name="T" value="D" checked> FOLDER<br />
<?php
		// TODO: Fix this.
		if ($this->qdig_gallery_mode != TRUE) {
?>
        <input type="RADIO" name="T" value="F"> FILE
<?php		} ?>
       </td>
       <td nowrap>
        Name <input type="TEXT" name="FN" size=12>
       <input type="HIDDEN" name="POSTACTION" value="CREATE">
       <input type="HIDDEN" name="DIR" value="<?php echo $this->rel_dir?>">
       <input type="SUBMIT" value="CREATE">
       </td>
      </form>
      <form enctype="multipart/form-data" method="POST" action="<?php echo $this->self?>">
       <td>
<?php
		if ($this->qdig_gallery_mode == TRUE) {
?>
        <strong>Upload Image File: </strong>
<?php		} else {
?>
        <strong>Upload File: </strong>
<?php		} ?>
        <input type="HIDDEN" name="MAX_FILE_SIZE" value="<?php echo $this->size_limit?>">
        <input type="HIDDEN" name="DIR" value="<?php echo $this->rel_dir?>">
        <input type="HIDDEN" name="POSTACTION" value="UPLOAD">
        <input size=30 type="FILE" name="FN">
        <input type="SUBMIT" value="UPLOAD">
       </td>
      </form>

     </tr>
    </table>

   </td>
  </tr>
  <tr><td colspan=5><?php echo $this->colorbars($this->rowToBodyFgDiv);?>  </td></tr>
<?php
		// output files list
		if (sizeof($fileList)>0) {
?>
  <tr>
   <td class="top" colspan="2" nowrap>FILENAME</td>
   <td>&nbsp;</td>
   <td class="top">LAST UPDATE</td>
   <td class="top" align=right>FILE SIZE</td>
  </tr>
  <tr>
   <td colspan=5><?php echo $this->colorbars($this->bodyFgToRowDiv);?>   </td>
  </tr>
<?php
			// iterate over files
			$i   = 0;
			$tot = 0;
			sort($fileList);
			$BG = array('ROW1','ROW2');
			while (list($key,$file) = each($fileList)) {
				$i++;
				$bgs  = $BG[$i%2];
				$path = $this->rel_path.'/'.$file;
				$mod  = filemtime($path);
				$sz   = filesize($path);
				$tot += $sz; // add size to summary total
				$a = $b = '';

				if (($mod + $this->new_days*86400) > time()) {
					$a  = ' <span class="RED" title="Newer than '.$this->new_days.' days">*</span>';
				}

				$tstr = $this->self.'?op=details&amp;D='.urlencode($this->rel_dir).'&amp;F='.rawurlencode($file);
				$tstr  = '<a href="'.$tstr.'">'.$file.'</a>'.$a;

				$ext = strtolower(strrchr($file,'.'));
				if ( $ext=='' || strstr(join(' ',$this->allow_edit),$ext)) { 
					$b  = '<a href="'.$this->self.'?op=view&amp;F=';
					$b .= urlencode($file).'&amp;D='.urlencode($this->rel_dir);
					$b .= '" title="View File">';
					$b .= $this->icon('view','View Contents').'</a>';
				}
?>
  <tr>
   <td class="<?php echo $bgs?>" align="center">
    <a href="<?php echo $this->self?>?op=details&amp;F=<?php echo urlencode($file)?>&amp;D=<?php echo urlencode($this->rel_dir)?>"
     title="View/Edit"><?php echo $this->icon($ext,"File Details")?></a>
   </td>
   <td class="<?php echo $bgs?>" nowrap><?php echo $tstr?>   </td>
   <td class="<?php echo $bgs?>" align="center"><?php echo $b?>&nbsp;</td>
   <td class="<?php echo $bgs?>" nowrap><?php echo date($this->date_format,$mod)?>   </td>
   <td class="<?php echo $bgs?>" nowrap align="right"><?php echo $this->fixsize($sz)?>  </td>
  </tr>
<?php
	if ($i<sizeof($filelist)-1) {
?>
  <tr>
   <td colspan=5><?php echo $this->colorbars($this->rowDiv);?>   </td>
  </tr>
<?php
	}
			}  // end iterate over files
?>
 <tr>
  <td colspan=5><?php echo $this->colorbars($this->rowToBodyFgDiv);?>  </td>
 </tr>
<?php
?>
  <tr><td colspan=5><?php echo $this->colorbars($this->bodyFgToRowDiv);?>  </td></tr>
  <tr>
   <td colspan="5">
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
     <tr class="BAR">
      <form method="POST" action="<?php echo $this->self?>">
       <td>&nbsp;New</td>
       <td nowrap>
        <input type="RADIO" name="T" value="D" checked> FOLDER<br />
<?php
		// TODO Fixt this.
		if ($this->qdig_gallery_mode != TRUE) {
?>
        <input type="RADIO" name="T" value="F"> FILE
<?php		} ?>
       </td>
       <td nowrap>
        Name <input type="TEXT" name="FN" size=12>
       <input type="HIDDEN" name="POSTACTION" value="CREATE">
       <input type="HIDDEN" name="DIR" value="<?php echo $this->rel_dir?>">
       <input type="SUBMIT" value="CREATE">
       </td>
      </form>
      <form enctype="multipart/form-data" method="POST" action="<?php echo $this->self?>">
       <td>
<?php
		if ($this->qdig_gallery_mode == TRUE) {
?>
        <strong>Upload Image File: </strong>
<?php		} else { ?>
        <strong>Upload File: </strong>
<?php		} ?>
        <input type="HIDDEN" name="MAX_FILE_SIZE" value="<?php echo $this->size_limit?>">
        <input type="HIDDEN" name="DIR" value="<?php echo $this->rel_dir?>">
        <input type="HIDDEN" name="POSTACTION" value="UPLOAD">
        <input size=30 type="FILE" name="FN">
        <input type="SUBMIT" value="UPLOAD">
       </td>
      </form>

     </tr>
    </table>

   </td>
  </tr>
  <tr><td colspan=5><?php echo $this->colorbars($this->rowToBodyFgDiv);?>  </td></tr>
<?php
		}  // end ouput files list

		if ($emptyDir
			&& dirname($this->rel_path)!=dirname($this->base_dir))
		{
?>
 <form method="POST" action="<?php echo $this->self?>">
  <tr>
   <td colspan=5 class="BAR">
    <input type="HIDDEN" name="DIR" value="<?php echo $this->rel_dir?>">
    Delete this empty folder?
    <input type="CHECKBOX" name="CONFIRM">
    <input type="SUBMIT" name="POSTACTION" value="DELETE">
   </td>
  </tr>
 </form>
<?php
		} elseif (sizeof($hiddenFiles)>0
			&& !$this->show_hidden)
		{
			// show number of hidden files if any
			print "  <tr><td class=\"ROW1\" colspan=\"5\">\n   Unlisted hidden files: <strong>".sizeof($hiddenFiles)."</strong>\n  </td></tr>\n";
		}

		if ($this->show_summary
			&& $tot>0)
		{
			print "<tr><td colspan=\"4\">&nbsp;</td><td class=\"top\" align=\"right\">TOTAL ~ ".$this->fixsize($tot)."</td></tr>\n";
		}
?>
  <tr>
   <td colspan=5><?php echo $this->colorbars($this->bodyFgDiv)?>   </td>
  </tr>
 </table>
<?php
		$this->foot();
	} // end function Navigate()

	function Error($title,$text="") {
		$this->head('ERROR -- '.$title,'<b>'.$text.'</b>');
		print '<h3>'.$title."</h3>\n";
		$dir = !is_dir($this->base_dir.$this->rel_dir) ? "/" : $this->rel_dir;
		if (!strstr($text, 'Qwd=')) {
?>
 <form method="POST" action="<?php echo $this->self?>">
  <input type="HIDDEN" name="DIR" value="<?php echo $dir?>"><br />
  <input type="SUBMIT" name="POSTACTION" value="Cancel">
 </form>
<?php
		}
		$this->foot();
		exit;
	} // end function Error()

	function display_perms($file) {
		$mode = fileperms($file);

		if(($mode & 0xC000) === 0xC000) // Unix domain socket
			$type = 's';
		elseif(($mode & 0x4000) === 0x4000) // Directory
			$type = 'd';
		elseif(($mode & 0xA000) === 0xA000) // Symbolic link
			$type = 'l';
		elseif(($mode & 0x8000) === 0x8000) // Regular file
			$type = '-';
		elseif(($mode & 0x6000) === 0x6000) // Block special file
			$type = 'b';
		elseif(($mode & 0x2000) === 0x2000) // Character special file
			$type = 'c';
		elseif(($mode & 0x1000) === 0x1000) // Named pipe
			$type = 'p';
		else // Unknown
			$type = '?';

		/* Determine Type */
		if($mode & 0x1000) $type='p'; /* FIFO pipe */
		else if( $mode & 0x2000 ) $type='c'; /* Character special */
		else if( $mode & 0x4000 ) $type='d'; /* Directory */
		else if( $mode & 0x6000 ) $type='b'; /* Block special */
		else if( $mode & 0x8000 ) $type='-'; /* Regular */
		else if( $mode & 0xA000 ) $type='l'; /* Symbolic Link */
		else if( $mode & 0xC000 ) $type='s'; /* Socket */
		else $type='u'; /* UNKNOWN */

		/* Determine permissions */
		$owner['read'] = ($mode & 00400) ? 'r' : '-';
		$owner['write'] = ($mode & 00200) ? 'w' : '-';
		$owner['execute'] = ($mode & 00100) ? 'x' : '-';
		$group['read'] = ($mode & 00040) ? 'r' : '-';
		$group['write'] = ($mode & 00020) ? 'w' : '-';
		$group['execute'] = ($mode & 00010) ? 'x' : '-';
		$world['read'] = ($mode & 00004) ? 'r' : '-';
		$world['write'] = ($mode & 00002) ? 'w' : '-';
		$world['execute'] = ($mode & 00001) ? 'x' : '-';

		/* Adjust for SUID, SGID and sticky bit */
		if( $mode & 0x800 ) $owner['execute'] = ($owner['execute']=='x') ? 's' : 'S';
		if( $mode & 0x400 ) $group['execute'] = ($group['execute']=='x') ? 's' : 'S';
		if( $mode & 0x200 ) $world['execute'] = ($world['execute']=='x') ? 't' : 'T';

		$ret = sprintf('%1s', $type);
		$ret .= sprintf('%1s%1s%1s', $owner['read'], $owner['write'], $owner['execute']);
		$ret .= sprintf('%1s%1s%1s', $group['read'], $group['write'], $group['execute']);
		$ret .= sprintf("%1s%1s%1s\n", $world['read'], $world['write'], $world['execute']);
		return $ret;
	} // end function display_perms()

	function fixsize($size) {
		$j = 0;
		$ext = array('B','KB','MB','GB','TB');
		while ($size >= pow(1024,$j)) ++$j;
		return round($size / pow(1024,$j-1) * 100) / 100 . " " . $ext[$j-1];
	} // end function path2bc()

	function path2bc($path) {
		$link = '';
		$ret  = "<a href=\"".$this->self.'?&amp;D='.urlencode("/")."\">//</a> ";
		$path = substr($path,1,strlen($path));
		$arr = explode('/',$path);
		for ($i=0;$i<sizeof($arr);$i++) {
			$current = $arr[$i];
			$link .= '/'.$current;
			$ret .= "<a href=\"".$this->self.'?&amp;D='.urlencode($link)."\">".$current.'</a>';
			if ($i < sizeof($arr)-1) $ret .= ' / ';
		}
		return $ret;
	} // end function path2bc()

	function authenticate() {
		header("WWW-Authenticate: Basic realm=\"$this->title\", stale=FALSE");
		header("HTTP/1.0 401 Unauthorized");
		if ($this->qdig_refer == TRUE) {
			$text  = "<br />Back to the <a href=\"$this->http_referer\">Qdig gallery</a>.<br />";
			$qdig_return = TRUE;
		} else {
			$text = '';
		}
		$this->Error('Authorization failed',"You must login to access $this->title".$text);
		exit;
	} // end function authenticate()

	function load_skin () {
		$skinfile = dirname($this->script_filename).'/skin.txt';
		if (file_exists($skinfile)) {
			$fcontents = file ($skinfile);
			for ($i = 0; $i < count($fcontents); $i++) {
				$row = $fcontents[$i];
				$rowa = explode("\t",$row);
				if (count($rowa) == 2) {
					$keyval = trim($rowa[0]);
					$valval = trim($rowa[1]);
					if ($valval == "true") {$valval = true;}
					elseif ($valval == "false") {$valval = false;}
				}
				$this->$keyval = $valval;
			}
		}
	} // end function load_skin ()

	function colorbars($str) {
		if ($str != '') {
			$arr = explode(';',$str);
			echo "\n    <table summary=\"Color Bar\"cellspacing=0 cellpadding=0 border=0 width=\"100%\">\n";
			for ($i = 0; $i < count($arr); $i++) {
				$arr2 = explode(',',$arr[$i]);
				echo "     <tr bgcolor=\"".$arr2[0]."\"><td height=\"".$arr2[1]."\">";
				echo "<spacer type=\"block\" height=\"".$arr2[1]."\">";
				echo "</td></tr>\n";
				}
			echo "    </table>\n";
		}
	} // end function colorbars()
}
$filefarm = new FileFarm();
// vim: set noexpandtab tabstop=4 shiftwidth=4:
