<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Henning Borchers <hb@triquart.de>
*  (c) 2008 Cyrill Helg <typo3 (ät) phlogi (dot) net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

// ignore first init (flash upload opens this script two times!)
if( ($_FILES['upload']) && ( $_GET['action']=='create_file_flash' || $_POST['action']=='create_file_flash' ) )
{
    die();
}

//file_put_contents ( PATH_site."typo3conf/ext/file_explorer/pi1/log/".date('Y-m-d_H_i_s',time())."_".microtime_float()."_request_start.log",print_r($_COOKIE,true));

tslib_eidtools::connectDB();

require_once(t3lib_extMgm::extPath('file_explorer')."pi1/classes/class.tx_fileexplorer_data.php");

	/* Fake class LL support, source: http://bugs.typo3.org/print_bug_page.php?bug_id=5231*/
	class csConvObj {
	    function parse_charset() {
	        return 'utf-8';
	    }
	    function utf8_decode($l,$c) {
	        return $l;
	    }
	}
	$TSFE = new stdClass;
	$TSFE->csConvObj = new csConvObj;

	//$LOCAL_LANG = t3lib_div::readLLfile('EXT:fefilebrowser/pi1/locallang.xml','de');

class tx_fileexplorer_eIDinit
{
	var $prefixId      = 'tx_fileexplorer_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_fileexplorer_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'file_explorer';	// The extension key.

	function tx_fileexplorer_eIDinit()
	{
	    $this->_GP = t3lib_div::_GET();
		//!TODO: Secure and avoid possible mysql injections
		$this->_GP['id'] = intval($this->_GP['id']);
		$this->_GP['folder'] = intval($this->_GP['folder']);

	    if( $_GET['action']=='create_file_flash' || $_POST['action']=='create_file_flash' )
	    {
	    	$_SERVER['HTTP_USER_AGENT'] = base64_decode($this->_GP['user_agent']);
	    	$_COOKIE['fe_typo_user'] = $this->_GP['fe_typo_user'];
	    }

	    $this->fe_user = tslib_eidtools::initFeUser();
		$this->conf = $this->fe_user->getKey('ses', 'tx_fileexplorer_pi1');
		$this->conf['fe_user'] = $this->fe_user->user;

		if( empty($this->conf['fe_user']['uid']) )
			die('fatal error: not logged in?');

		//language stuff
		$this->lang = t3lib_div::_GET('lang');
		if($this->lang == '') $this->lang = 'default';

	}

    function main()
    {
		$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_data');
		$handleData = new $newClass($this);
		$LOCAL_LANG = t3lib_div::readLLfile('EXT:file_explorer/pi1/locallang.xml',$this->lang);
        switch ($this->_GP['action'])
        {
            case 'create_file_flash':
                $_FILES['upload'] = $this->getFlashFiles();
				//!TODO: Check if this will work
				$folderPermission = $handleData->getFolderPermission($this->base->_GP['folder'],$this->conf['fe_user']);
				$handleData->insertFile($this->conf['fe_user']['uid'],$folderPermission);
                break;
            case 'delete_file':
                if (!$handleData->deleteFile($this->_GP['id'])){
				  return $LOCAL_LANG[$this->lang]['error.deleteFile'];
				 }
                break;
            case 'delete_folder':
				$errorMsg = $handleData->deleteFolder($this->_GP['id']);

                if( $errorMsg !== true)
                {
					return $errorMsg;
//                 	return $LOCAL_LANG[$this->lang]['error.recursiveDelete'];
                }
				return '';
                break;
            case 'download_file':
                $handleData->downloadFile($this->_GP['id']);
                break;
            case 'download_folder':
                $handleData->downloadFolder($this->_GP['id']);
                break;

        }
    }
    function getFlashFiles()
    {
        $out = array();
        if( !empty($_FILES['Filedata']['tmp_name']) && $_FILES['Filedata']['error'] == 0 )
        {
            $out['name'][0]        = $_FILES['Filedata']['name'];
            $out['tmp_name'][0]    = $_FILES['Filedata']['tmp_name'];
            $out['size'][0]        = $_FILES['Filedata']['size'];
        }
        return $out;
    }
	function debugToFile($additionalContent)
	{
	  $content =
	  "files\n".
	  print_r($_FILES, true).
	  "get\n".
	  print_r($_GET, true).
	  "post\n".
	  print_r($_POST, true).
	  "request\n".
	  print_r($_REQUEST, true).
	  "cookie\n".
	  print_r($_COOKIE, true).
	  "conf\n".
	  print_r($this->conf, true).
	  "_GP\n".
	  print_r($this->_GP, true).
	  "user_agent\n".
	  $_SERVER['HTTP_USER_AGENT'].
	  "\nadditional_output:\n".
	  $additionalContent."\n----EOF----\n"
	  ;

	  file_put_contents ( PATH_site.date('Y-m-d').'_'.time()."_request.log",
										  $content
										  );
										  echo $content;
	}
}

$eIDinit = t3lib_div::makeInstance('tx_fileexplorer_eIDinit');

echo $eIDinit->main();


?>