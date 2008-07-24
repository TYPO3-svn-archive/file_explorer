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

require_once(t3lib_extMgm::extPath('file_explorer')."pi1/classes/class.tx_fileexplorer_upload.php");


class tx_fileexplorer_data
{

	/* Global Configuration */
	var $base;
	var $zipFolderPath = array();
	var $zipObj;

	function tx_fileexplorer_data(&$base)
	{
		$this->base = $base;
	}

	function getFolderContent($pid)
	{
		if (empty($this->baseRelPid)){
			$this->baseRelPid = $pid;
		}
		$sql = "SELECT pid,file,tstamp FROM `tx_fileexplorer_files`
				WHERE deleted = 0 AND hidden = 0 AND pid = ".(int)$pid;
		$res = $GLOBALS['TYPO3_DB']->sql_query($sql);
        while( false != ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ){
			if ($row['pid'] == $this->baseRelPid)
 				$destinationPath = $row['file'];
			else
			 	$destinationPath = $this->getFolderPath($row['pid'],$this->baseRelPid).$row['file'];

        	$sourcePath = PATH_site.$this->base->conf['upload_folder'].$this->getFolderPath($row['pid'],$this->base->conf['root_page']).$row['file'];

        	$handle = fopen ($sourcePath, "r");
			$content = fread ($handle, filesize ($sourcePath));
			fclose ($handle);
        	$this->zipObj->addFile($content, $destinationPath, $row['tstamp']);
        }

		$sql = "SELECT uid
				FROM pages AS t1
				WHERE doktype = 150 AND pid = ".(int)$pid;
		$res = $GLOBALS['TYPO3_DB']->sql_query($sql);

		$this->zipFolderPath = array();
        while( false != ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ){
        	$folderPermissions = $this->getFolderPermission($row['uid'], $this->base->conf['fe_user']);
        	if($folderPermissions['read'] == 0){
        		continue;
        	}
        	$this->getFolderContent($row['uid']);
        }
	}


	function downloadFolder($uid)
	{
		$sql = "SELECT title FROM pages WHERE doktype = 150 AND uid = ".(int)$uid;
		$res = $GLOBALS['TYPO3_DB']->sql_query($sql);
		$folder = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		if( empty($folder['title']) )
			die("no such folder!");

		include_once(t3lib_extMgm::extPath('file_explorer')."pi1/classes/zip.lib.php");
		$this->zipObj = new zipfile();

		$this->getFolderContent($uid);

		// send header
		header("HTTP/1.1 200 OK");
		header("Content-Type: application/force-download");
		header('Content-Disposition: attachment; filename="'.$folder['title'].'.zip"');
		header("Content-Transfer-Encoding: binary");
		echo $this->zipObj->file();
        exit();
	}

	function downloadFile($uid)
	{
		$fileInfo = $this->getFile($uid);
		$path = PATH_site.$this->base->conf['upload_folder'].$this->getFolderPath($fileInfo['pid'],$this->base->conf['root_page']);
        $sql = "SELECT file FROM `tx_fileexplorer_files` WHERE uid = ".$uid;
        $res = $GLOBALS['TYPO3_DB'] ->sql_query($sql);
        $data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$size = filesize($path.$data['file']);
		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers
		$file_extension = strtolower(substr(strrchr($data['file'],"."),1));
		switch( $file_extension )
		{
		case "pdf": $ctype="application/pdf"; break;
		case "exe": $ctype="application/octet-stream"; break;
		case "zip": $ctype="application/zip"; break;
		case "doc": $ctype="application/msword"; break;
		case "xls": $ctype="application/vnd.ms-excel"; break;
		case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
		case "gif": $ctype="image/gif"; break;
		case "png": $ctype="image/png"; break;
		case "jpeg":
		case "jpg": $ctype="image/jpg"; break;
		default: $ctype="application/force-download";
		}
		header("Content-Type: $ctype");
		header('Content-Disposition: attachment; filename="'.$data['file'].'"');
		header("Content-Transfer-Encoding: binary");
 		header("Content-Length: ".filesize($path.$data['file']));

        readfile($path.$data['file']);

        exit();
	}

    function insertGroupRelations($relType, $pageUid, $arrGroupUid = array() )
    {
        $add = ( count($arrGroupUid) > 0 ) ? implode(',', $arrGroupUid) : 0;
        $sql = "UPDATE `pages` SET `tx_fileexplorer_".$relType."` = '".$add."'
                WHERE uid = ".$pageUid;

        $GLOBALS['TYPO3_DB']->sql_query($sql);
    }

    function validateForm($required)
    {
        $error = array();
        foreach ($required AS $field){
            if( empty($this->base->_GP['form'][$field]) ){
                array_push($error,$this->base->pi_getLL('error.field_validation').$field.'<br/>');
            }
        }
        return $error;
    }

    function editFolder($folderPermissions)
    {
    	$required = array('title');
        $error = $this->validateForm($required);
        if( count($error) > 0 ){
            return $error;
        }
		if ($folderPermissions['write']!=1){
			die('not allowed');
		}
		//get the parent folder
		$tmpPath = $this->getPath($this->base->_GP['id'],$this->base->conf['root_page']);
		if (count($tmpPath)>1){
			$parentFolder = $tmpPath[count($tmpPath)-2];
			$parentFolderUid = $parentFolder['uid'];
		}
		else{
			$parentFolderUid = $this->base->conf['root_page'];
			//disallow normal user to rename folder in root folder
			if ($folderPermissions['owner']!=1)
			  die('not allowed');
		}

		if($this->getFolderId($this->base->_GP['form']['title'],$parentFolderUid) !=false && $this->getFolderId($this->base->_GP['form']['title'],$parentFolderUid) != $this->base->_GP['id']  ) die($this->base->pi_getLL('error.folderExisting'));

		//checks folder name
		$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_upload');
		$uploadObj  = new $newClass($this->base);
		$this->base->_GP['form']['title'] = $uploadObj->convertToFilename($this->base->_GP['form']['title']);

		$origFolderPath = $this->base->conf['upload_folder'].$this->getFolderPath($this->base->_GP['id'],$this->base->conf['root_page']);
    	$sql = "UPDATE `pages` SET `title` = '".$this->base->_GP['form']['title']."'
               WHERE `uid` = ".$this->base->_GP['id'];

    	$GLOBALS['TYPO3_DB']->sql_query($sql);
		$newFolderPath = $this->base->conf['upload_folder'].$this->getFolderPath($this->base->_GP['id'],$this->base->conf['root_page']);
		//strip slash
		if (substr($origFolderPath,-1,1) === "/"){
			$origFolderPath = substr($origFolderPath,0,-1);
		}
		if (substr($newFolderPath,-1,1) === "/"){
			$newFolderPath = substr($newFolderPath,0,-1);
		}

 		if (!rename($origFolderPath,$newFolderPath)) die($this->base->pi_getLL('error.renameFolderFs'));

    	$this->insertGroupRelations('read', $this->base->_GP['id'], $this->base->_GP['form']['read_perms']);
        $this->insertGroupRelations('write', $this->base->_GP['id'], $this->base->_GP['form']['write_perms']);
    }

    function insertFolder()
    {
        $required = array('title');
        $error = $this->validateForm($required);
        if( count($error) > 0 ){
            return $error;
        }
		//create folder in current folder on filesystem
		$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_upload');
		$uploadObj  = new $newClass($this->base);
		$folderName = $uploadObj->convertToFilename($this->base->_GP['form']['title']);

		$relFolder=$this->getFolderPath($this->base->_GP['folder'],$this->base->conf['root_page']);
		if (!mkdir($this->base->conf['upload_folder'].$relFolder.$folderName)) {
			$error[] = "Error creating folder on fs! Maybe already existing?";
			return $error;
		}

		$this->storeFolderEntry($this->base->_GP['folder'],$folderName,$GLOBALS['TSFE']->fe_user->user['uid'],$this->base->_GP['form']['read_perms'],$this->base->_GP['form']['write_perms']);

        return array();
    }


	function storeFolderEntry($folderPid,$folderTitle,$userId,$readPerm,$writePerm)
	{
	     $insert = array('pid'					=> $folderPid,
						'title'							=> $folderTitle,
						'tstamp'						=> time(),
						'crdate'						=> time(),
						'tx_fileexplorer_feCrUserId'	=> $userId,
        				'doktype' 						=> 150,
        				'sorting' 						=> 0
						 );
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', $insert);

        $pageUid = $GLOBALS['TYPO3_DB']->sql_insert_id();

        $this->insertGroupRelations('read', $pageUid, $readPerm);
        $this->insertGroupRelations('write', $pageUid, $writePerm);
		return $pageUid;
	}


	function getFile($id)
	{
		$out = array();

	    $sql = "SELECT * FROM `tx_fileexplorer_files`
	            WHERE uid = ".$id;
	    $res = $GLOBALS['TYPO3_DB']->sql_query($sql);
	    $out = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

	    $out['writePermission'] = 0;

		//check root user
		$arrUserGroups = explode(',', $GLOBALS['TSFE']->fe_user->user['usegroup']);
		$rootGroups = explode(',', $this->base->conf['root_fe_user_groups']);
        foreach ($rootGroups AS $rootGroup){
		    if( in_array($rootGroup, $arrUserGroups) == true && $rootGroup != 0 ){
		        $out['writePermission'] = 1;
		    }
        }

        //check owner
        if($out['feCrUserId'] == $GLOBALS['TSFE']->fe_user->user['uid'] && $out['feCrUserId'] != 0)
        	$out['writePermission'] = 1;

	    return $out;
	}

    function getFolder($uid)
    {
        $sql = "SELECT tx_fileexplorer_read,tx_fileexplorer_write,tx_fileexplorer_feCrUserId,title FROM `pages` WHERE uid = ".$uid;
        $res = $GLOBALS['TYPO3_DB']->sql_query($sql);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

        $perm['read']  = explode(',', $row['tx_fileexplorer_read']);
        $perm['write'] = explode(',', $row['tx_fileexplorer_write']);

        foreach($perm['read'] AS $read){
            $out['read_perms'][] = $read;
        }
        foreach($perm['write'] AS $write){
            $out['write_perms'][] = $write;
        }
        $out['title']       = $row['title'];
        $out['feCrUserId']  = $row['tx_fileexplorer_feCrUserId'];

        return $out;
    }

	function deleteFile($file_uid,$onFs=true,$admin=false)
	{
		//Thats here because of the file_explorer_check backend module... we don't check permissions there and don't want to
		if (!($admin))
			$file = $this->getFile( $file_uid );

		$parentFolderPerm = $this->getFolderPermission($file['pid'],$this->base->conf['fe_user']);

	    if( (count($file) > 0 && $parentFolderPerm['write']==1) || $admin){
			$filePath = $this->base->conf['upload_folder']. $this->getFolderPath($file['pid'],$this->base->conf['root_page']).$file['file'];
    	    $sql = "DELETE FROM `tx_fileexplorer_files` WHERE uid = ".$file_uid;
    	    $GLOBALS['TYPO3_DB']->sql_query($sql);
			if ($onFs){
				if (!empty($this->base->conf['trash_folder']) && $this->base->conf['move_to_trash']==1){
					if(!@rename($filePath,$this->base->conf['trash_folder'].$file['file'])){
						die('error moving file: '.$filePath.' to trash folder: '.$this->base->conf['trash_folder']);
						return false;
					}
				}
				else{
					if(!@unlink($filePath)){
						die('error deleting file: '.$filePath);
						return false;
					}
				}
			}
 			return true;
	    } //no permission
		else return false;
	}

	function deleteFolder($id,$onFs=true)
	{

		$folderPermissions = $this->getFolderPermission($id,$this->base->conf['fe_user']);

		if ($folderPermissions['write']!=1){
			die('not allowed');
		}

		if(count($this->getPath($id,$this->base->conf['root_page']))==1 && !($folderPermissions['owner']==1)){
			die('not allowed');
		}
		
		$relPath=$this->getFolderPath($id,$this->base->conf['root_page']);
		$fullPath = PATH_site.$this->base->conf['upload_folder'].$relPath;

	    $childItems = 0;
	    $sql = "SELECT uid FROM `pages` WHERE pid = ".$id." AND deleted = 0 AND hidden = 0 LIMIT 0,1";
	    $res = $GLOBALS['TYPO3_DB']->sql_query($sql);
	    $childItems += $GLOBALS['TYPO3_DB']->sql_num_rows($res);

	    $sql = "SELECT uid FROM `tx_fileexplorer_files` WHERE pid = ".$id." AND deleted = 0 AND hidden = 0 LIMIT 0,1";
	    $res = $GLOBALS['TYPO3_DB']->sql_query($sql);
	    $childItems += $GLOBALS['TYPO3_DB']->sql_num_rows($res);

	    if($childItems > 0){
			//check if we allow recursive delete
			if ($this->base->conf['recursiveDelete'] == 1){
			  $this->foldersToDel = array();
			  $this->filesToDel = array();
 			  $this->foldersFilesNotDeleted = array();
			  $this->getFolderFilesRecursive($id);
			  $this->foldersToDel = array_reverse($this->foldersToDel); //sort with depth

			  foreach ($this->filesToDel as $curFile){
				if (!$this->deleteFile($curFile['uid'])){
				  array_push($this->foldersFilesNotDeleted,$curFile['fpath'].$curFile['fname']);
				}	
			  }
			  //add this relative root folder to the list
			  array_push($this->foldersToDel,array('uid' => $id, 'fullPath' => $fullPath));
			  foreach ($this->foldersToDel as $curFolder){
				$sql = "DELETE FROM `pages` WHERE uid = ".$curFolder['uid'];
				$GLOBALS['TYPO3_DB']->sql_query($sql);
				if($onFs){
				  //remove folder from fs
 				  if (!@rmdir($curFolder['fullPath']))
					array_push($this->foldersFilesNotDeleted,$curFolder['fullPath']);
				}
			  }
			  if (!empty($this->foldersFilesNotDeleted)){
				$errorText = 'Could not remove the following folders/files: '."\n";
				foreach ($this->foldersFilesNotDeleted as $curFileFolder){
				  $errorText .= '- '.$curFileFolder."\n";
				}
				return $errorText;
			  }
			  return true;
			}// end of recursive delete
	    }
	    else{
			$sql = "DELETE FROM `pages` WHERE uid = ".$id;
            $GLOBALS['TYPO3_DB']->sql_query($sql);
			if($onFs){
				//remove folder from fs
				if (!@rmdir($fullPath))
					die('error deleting folder from fs: '.$this->base->conf['upload_folder'].$relPath);
			}
        return true;
	    }
	}



	function getFolderFilesRecursive($pid){
	  //get the files in current folder
	  $sql = "SELECT uid,pid,file FROM `tx_fileexplorer_files`
	  WHERE deleted = 0 AND hidden = 0 AND pid = ".(int)$pid;
	  $res = $GLOBALS['TYPO3_DB']->sql_query($sql);
	  while( false != ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ){
		$sourcePath = PATH_site.$this->base->conf['upload_folder'].$this->getFolderPath($row['pid'],$this->base->conf['root_page']);
		array_push($this->filesToDel,array('uid' => $row['uid'], 'fname'=>$row['file'],'fpath'=>str_replace('//','/',$sourcePath)));
	  }
	  
	  //get all folders in current folder ($pid)
	  $sql = "SELECT title, uid, tx_fileexplorer_read, tx_fileexplorer_write, tx_fileexplorer_feCrUserId
	  FROM pages
	  WHERE doktype = 150 AND deleted = 0 AND pid = ".(int)$pid;
	  $res = $GLOBALS['TYPO3_DB']->sql_query($sql);

	  while( false != ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ){
		$folderPermissions = $this->getFolderPermission($row['uid'], $this->base->conf['fe_user']);
		if($folderPermissions['read'] != 1 || $folderPermissions['write'] != 1){
		  array_push($this->foldersFilesNotDeleted,$fullPath);
		  continue;
		}
		$fullPath = PATH_site.$this->base->conf['upload_folder'].$this->getFolderPath($row['uid'],$this->base->conf['root_page']);
		// 						if (substr($fullPath,-1,1) === "/"){
		// 							$fullPath = substr($fullPath,0,-1);
		// 						}
		array_push($this->foldersToDel,array('uid' => $row['uid'], 'fullPath' => $fullPath/*, 'depth'=>$depth*/));
		$this->getFolderFilesRecursive($row['uid']);
	  }
	}


	function editFile($folderPermission)
	{
		//the folder needs write permission
		if ($folderPermission['write']!=1){
		  die('not allowed');
		}
		$required = array('title');
		$error = $this->validateForm($required);
        if( count($error) > 0 ){
            return $error;
        }
		$sql = "UPDATE `tx_fileexplorer_files` SET
                `title` = '".$this->base->_GP['form']['title']."',
                `description` = '".$this->base->_GP['form']['description']."'
                WHERE `uid` = ".$this->base->_GP['id'];
		$GLOBALS['TYPO3_DB']->sql_query($sql);
	}

    function insertFile($user_id,$folderPermission)
    {
		//the folder needs write permission
		if ($folderPermission['write']!=1){
		  die('not allowed');
		}
        $this->base->_GP['form']['title'] = (!empty($this->base->_GP['form']['title']))
        								? $this->base->_GP['form']['title'] : $_FILES['upload']['name'][0];

		$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_upload');
		$upload = new $newClass($this->base);
		$uploadErr = $upload->uploadFileChecks($this->getFolderPath($this->base->_GP['folder'],$this->base->conf['root_page']),
											  array(  'name' 		=> $_FILES['upload']['name'][0],
	    									'tmp_name' 	=> $_FILES['upload']['tmp_name'][0],
	    									'size' 		=> $_FILES['upload']['size'][0] )
											  	);

		if( count($uploadErr) > 0 ){
				return $uploadErr;
        }

		if ($this->base->conf['unpackZipFile'] == 1 && $upload->checkForZipFile()){
			$this->handleZipFileExtraction($upload->file['tmp_name'],$upload->getFilename(),$this->getFolderPath($this->base->_GP['folder'],$this->base->conf['root_page']),$user_id,$this->base->_GP['folder']);
		}
		else{
		    $uploadErr = $upload->uploadSingleFile($this->getFolderPath($this->base->_GP['folder'],$this->base->conf['root_page']));
			$this->storeFileEntry($user_id,$upload->getFilename(),$this->base->_GP['form']['title'],$this->base->_GP['folder'],$this->base->_GP['form']['description']);
		}
        if( count($uploadErr) > 0 ){
				return $uploadErr;
         }

        return array();
	}

	function storeFileEntry($userId,$filename,$fileTitle,$folderId,$fileDescription='')
	{
		   $insert = array('pid'		=> $folderId,
						'title'			=> $fileTitle,
						'tstamp'		=> time(),
						'crdate'		=> time(),
						'feCrUserId'	=> $userId,
        				'sorting' 		=> 0,
        				'description' 	=> $fileDescription,
        				'file' 			=> $filename
						 );
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_fileexplorer_files', $insert);
	}

	function handleZipFileExtraction($zipFile,$zipFileName,$relStorePath,$userId,$relRootFolderId)
	{
		include_once(t3lib_extMgm::extPath('file_explorer')."pi1/classes/PHPUnzip.class.php");
		$zip  = new PHPUnzip();
		$open = $zip->Open($zipFile);
		if (!$open) die("Failed to open file.");

		$zip->SetOption(ZIPOPT_FILE_OUTPUT, true); // save data to files, instead reading to memory
		$zip->SetOption(ZIPOPT_OUTPUT_PATH,$this->base->conf['upload_folder'].$relStorePath); // where to save the files, include trailing /
		//!TODO: What shall we do here? =)
		$zip->SetOption(ZIPOPT_OVERWRITE_EXISTING, false);

		$success = $zip->Read();

		if (!$success) {
			echo "Error {$zip->error} encountered: {$zip->error_str}.<br /><br />";
			exit();
		}

		//!TODO: Flash upload permission choice!
		//if no permissions are set, (i.e flash upload)
		if (empty($this->base->_GP['form']['read_perms']) || empty($this->base->_GP['form']['write_perms']) ){
			 $sql = "SELECT `tx_fileexplorer_read`, `tx_fileexplorer_write` FROM `pages` WHERE `uid` = '".$relRootFolderId."'";
	   		 $res = $GLOBALS['TYPO3_DB']->sql_query($sql);
       		 $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			 if (empty($this->base->_GP['form']['read_perms']))
				 $this->base->_GP['form']['read_perms'] = explode(',',$row['tx_fileexplorer_read']);
			 if (empty($this->base->_GP['form']['write_perms']))
			 	$this->base->_GP['form']['write_perms'] = explode(',',$row['tx_fileexplorer_write']);
		}

		$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_upload');
		$upload = new $newClass($this->base);
		//insert all the new directories and the files created
		$folderPidArray = array();
		if (count($zip->createdDirectories) >0){
			foreach( $zip->createdDirectories AS $curDirectory){
				$folderSplit = explode('/',$curDirectory);
				//dive into that folder to get the new pid's
				$parentFolderId = $relRootFolderId;
					foreach($folderSplit AS $singleFolderName){
						//check if the folder already exists
						if ($this->getFolderId($singleFolderName,$parentFolderId)) {
							$parentFolderId = $this->getFolderId($singleFolderName,$parentFolderId);
							//this makes sure that also folders (and their pids) that already exist are in the needed $folderPidArray
							$folderPidArray[$curDirectory.'/'] =$parentFolderId;
							continue;
						}
						$newPid = $this->storeFolderEntry($parentFolderId,$singleFolderName,$GLOBALS['TSFE']->fe_user->user['uid'],$this->base->_GP['form']['read_perms'],$this->base->_GP['form']['write_perms']);
						$parentFolderId = $newPid;
						$folderPidArray[$curDirectory.'/'] =$newPid;
					}
			}
		}
		//now store the file records
		foreach($zip->createdFiles AS $curFile){

			$uploadErr = $upload->uploadFileChecks($this->base->conf['upload_folder'].$relStorePath,
									array(  'name' 		=> $curFile['filename'],
									'tmp_name' 	=> 'notEmptyFromZip',
									'size' 		=> 1 )
										);
			if (!empty($uploadErr)){
				//delete the file
				@unlink($this->base->conf['upload_folder'].$relStorePath.$folderPidArray[$curFile['path']].$curFile['filename']);
				continue;
			}
			if (!empty($curFile['path'])){
				//get the pid for this path
				if (!empty($folderPidArray[$curFile['path']])) {
					$this->storeFileEntry($userId,$curFile['filename'],$curFile['filename'],$folderPidArray[$curFile['path']],$this->base->pi_getLL('extract.descriptionFile').$zipFileName);
				}
				else{ //we are in serious trouble
					die($this->base->pi_getLL('error.uploadFatalInternal'));
				}
			}
			else{
				//store in relative rootFolder
				//!TODO: make a language thing, what about the title of the files? Maybe just the filename without the ending?
				$this->storeFileEntry($userId,$curFile['filename'],$curFile['filename'],$relRootFolderId,$this->base->pi_getLL('extract.descriptionFile').$zipFileName);
			}
		}
	}


	function getFolderId($folderName,$pid)
	{
		//!TODO: Use a nice exec_SELCECTquery here and everywhere else
		 $sql = "SELECT uid FROM `pages`
	            WHERE `deleted` = '0' AND `title` = '".$folderName."'"." AND `pid` = '".$pid."'";
	    $res = $GLOBALS['TYPO3_DB'] ->sql_query($sql);
		if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		else return false;
		if (count($row) < 1) return false;
		return $row['uid'];
	}

	function getCurrentFolder($folder_id)
	{
	    $sql = "SELECT uid,pid,title FROM `pages`
	            WHERE uid = ".$folder_id;
	    $res = $GLOBALS['TYPO3_DB'] ->sql_query($sql);
	    $data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

	    $data['permission'] = $this->getFolderPermission($folder_id, $GLOBALS['TSFE']->fe_user->user);
	    if( $this->base->_GP['folder'] == $this->base->conf['root_page'] ){
	        $data['isRoot'] = 1;
	        $data['permission']['write'] = 1;
	    }
	    else{
	        $data['isRoot'] = 0;
	    }
	    // redirect not permitted user
	    if($data['permission']['read'] == 0 && $data['permission']['owner'] == 0 && $data['isRoot'] == 0){
	    	header("Location: index.php?id=".$GLOBALS['TSFE']->id);
	    }

		return $data;
	}

	function getPath($folder_id,$root_page){
	    $path      = array();
	    while($root_page != $folder_id){
	        $sql    = "SELECT * FROM `pages` WHERE `uid` = ".$folder_id;
	        $res    = $GLOBALS['TYPO3_DB']->sql_query($sql);
	        $row    = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	        $path[] = array( 'title' => $row['title'], 'uid' => $row['uid'] );
	        $folder_id = $row['pid'];

	        if(empty($row['pid']))
	        	die("getPath error");//!TODO
	    }
	    $path = array_reverse($path);
	    return $path;
	}

	function getFolderPath($folder_id, $root_page)
	{
		$path=$this->getPath($folder_id,$root_page);
		$pathName = array();
		foreach ($path AS $curPath){
			$pathName[] = $curPath['title'];
		}
		$relPath = implode('/',$pathName).'/';
		return $relPath;
	}

	function getFolderPermission($pageUid, $feUser,$type='')
	{
		$out = array('owner' => 0, 'read' => 0, 'write' => 0);
		$arrUserGroups = explode(',', $feUser['usergroup']);
		// check root user
		$rootGroups = explode(',', $this->base->conf['root_fe_user_groups']);
        foreach ($rootGroups AS $rootGroup){
		    if( in_array($rootGroup, $arrUserGroups) == true && $rootGroup != 0 ){
		        $out['owner']  = 1;
		        $out['read']   = 1;
		        $out['write']  = 1;
		        return $out;
		    }
        }
	    // check createUser == owner?
	    $sql = "SELECT * FROM `pages` WHERE uid = ".$pageUid."
	            AND tx_fileexplorer_feCrUserId = '".$feUser['uid']."'
	            AND tx_fileexplorer_feCrUserId != 0";
	    $res = $GLOBALS['TYPO3_DB']->sql_query($sql);
	    if( $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0 ){
	        $out['owner']  = 1;
	        $out['read']   = 1;
	        $out['write']  = 1;
	        return $out;
	    }

	    $sql = "SELECT uid,pid,tx_fileexplorer_readPublic,tx_fileexplorer_feCrUserId,tx_fileexplorer_read,tx_fileexplorer_write FROM `pages` WHERE `uid` = ".$pageUid;
	    $res = $GLOBALS['TYPO3_DB']->sql_query($sql);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);


        // check public folder
        if($row['tx_fileexplorer_readPublic'] == 1){
        	$out['read']   = 1;
        }

        // check not logged-in user
        if(empty($feUser['uid']))
        	return $out;

        // check userless folder
        if( empty($row['tx_fileexplorer_feCrUserId']) ){
        	$out['read']   = 1;
	        $out['write']  = 1;
        }

        // check permitted userGroups read
        $allowedReadGroups = explode(',', $row['tx_fileexplorer_read']);
        foreach ($allowedReadGroups AS $allowedGroup){
		    if( in_array($allowedGroup, $arrUserGroups) == true ){
		        $out['read'] = 1;
		    }
        }

        // check permitted userGroups write
        $allowedWriteGroups = explode(',', $row['tx_fileexplorer_write']);
        foreach ($allowedWriteGroups AS $allowedGroup){
		    if( in_array($allowedGroup, $arrUserGroups) == true ){
		        $out['write'] = 1;
		    }
        }
		//do not allow changes for folders in root folder
		if ($type=='edit_folder' && $row['pid']==$this->base->conf['root_page']){
// 			print_r($)
			$out['write'] = 0;
			return $out;
		}

	    return $out;
	}

	function getData($folder_id)
	{
	    $i = 0;
	    $sql = "SELECT t1.*, t2.username FROM `pages` AS t1
	            LEFT JOIN `fe_users` AS t2 ON (t1.tx_fileexplorer_feCrUserId = t2.uid)
	            WHERE t1.`deleted` = 0 AND t1.`hidden` = 0 AND t1.`doktype` = 150 AND t1.pid = ".$folder_id."
	            ORDER BY t1.title";
	    $res = $GLOBALS['TYPO3_DB']->sql_query($sql);

	    $data = array();
        while( false != ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ){
		    $permission['folder'] = $this->getFolderPermission($row['uid'], $GLOBALS['TSFE']->fe_user->user);
            if( $permission['folder']['read'] == 1 ){
                $data[$i] = $row;
    		    $data[$i]['isFolder']  = 1;
    		    $data[$i] = array_merge($permission['folder'] , $data[$i]);
    		    $i++;
            }
		}
		$sql = "SELECT t1.*, t2.username FROM `tx_fileexplorer_files` AS t1
		        LEFT JOIN `fe_users` AS t2 ON (t1.feCrUserId = t2.uid)
	            WHERE t1.`file` != '' AND t1.`deleted` = 0 AND t1.`hidden` = 0 AND t1.pid = ".$folder_id."
	            ORDER BY t1.title";
	    $res = $GLOBALS['TYPO3_DB']->sql_query($sql);
		while( false != ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ){
            $data[$i] = $row;
		    if( $row['feCrUserId'] == $GLOBALS['TSFE']->fe_user->user['uid'] ){
		        $data[$i]['owner'] = 1;
		    }
		    $data[$i]['isFolder'] = 0;
		    $i++;
		}
		return $data;
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/classes/class.tx_fileexplorer_data.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/classes/class.tx_fileexplorer_data.php']);
}
?>