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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('file_explorer')."pi1/classes/class.tx_fileexplorer_data.php");

class tx_fileexplorer_form
{

	/* Global Configuration */
	var $base;

	function tx_fileexplorer_form(&$base)
	{
		$this->base = $base;
		$this->path = PATH_site.$this->base->conf['upload_folder'];
		$this->cObj = t3lib_div::makeInstance("tslib_cObj");
		$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_data');
		$this->handleData = new $newClass($this->base);
	}

	function getHtml($action)
	{
	    $templateCode = $this->cObj->fileResource($this->base->conf["templateFile"]);
		$successControl = false;
	    switch ($action)
	    {
	        case 'create_folder':
	            $subpart = '###FOLDER_FORM###';
                if( isset($this->base->_GP['form']['submit']) )
                {
                    $result = $this->handleData->insertFolder();
					if (count($result) ==0){
						$successControl = true;
						break;
					}
                }
                $currentFolder = $this->handleData->getFolder( $this->base->_GP['folder'] );
                $this->base->_GP['form']['read_perms'] = $currentFolder['read_perms'];
                $this->base->_GP['form']['write_perms'] = $currentFolder['write_perms'];
	            $data = $this->getFolderForm('create', $result);
	            $markerArray = $data['markerArray'];
	            $subpartArray = $data['subpartArray'];
	            break;
	        case 'create_file':
	            $subpart = '###FILE_FORM###';
	            if( isset($this->base->_GP['form']['submit']) )
                {
                    $result = $this->handleData->insertFile($GLOBALS['TSFE']->fe_user->user['uid']);
					if (count($result) ==0){
						$successControl = true;
						break;
					}
                }
	            $data = $this->getFileForm('create', $result);
	            $markerArray = $data['markerArray'];
	            $subpartArray = $data['subpartArray'];
	            break;
	        case 'create_file_flash':
	            $subpart = '###FILE_FORM_FLASH###';
	            $markerArray = $this->getFileFormFlash($result);
	            break;
	        case 'edit_folder':
	            $subpart = '###FOLDER_FORM###';
                if( isset($this->base->_GP['form']['submit']) )
                {
                    $result = $this->handleData->editFolder();
					if (count($result) ==0){
						$successControl = true;
						break;
					}
                }
                $currentFolder = $this->handleData->getFolder( $this->base->_GP['id'] );
                $permissions = $this->handleData->getFolderPermission($this->base->_GP['id'], $GLOBALS['TSFE']->fe_user->user);
                $this->base->_GP['form'] = (!empty($this->base->_GP['form'])) ? $this->base->_GP['form'] : array();
                $this->base->_GP['form'] = array_merge($currentFolder, $this->base->_GP['form']);
	            $data = $this->getFolderForm('edit', $result, $permissions);
	            $markerArray = $data['markerArray'];
	            $subpartArray = $data['subpartArray'];
	            break;
	        case 'edit_file':
	            $subpart = '###FILE_FORM###';
                if( isset($this->base->_GP['form']['submit']) )
                {
                    $result = $this->handleData->editFile();
					if (count($result) ==0){
						$successControl = true;
						break;
					}
                }
                $currentFile = $this->handleData->getFile( $this->base->_GP['id'] );
	            $data = $this->getFileForm('edit', $result, $currentFile);
	            $markerArray = $data['markerArray'];
	            $subpartArray = $data['subpartArray'];
	            break;
	        case 'not_permitted':
	        	$subpart = '###NOT_PERMITTED###';
	        	break;
	    }
		if ($successControl){
			$subpart = '###SUCCESS_PAGE###';
			$markerArray['###SUCCESS_TEXT###'] = $this->base->pi_getLL('form.success');
		}
        $template = $this->cObj->getSubpart($templateCode, $subpart);

		return $this->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray );
	}


    function getFileFormFlash($error)
    {
        $maxFilesize = ini_get('upload_max_filesize');

        $out['###ERROR###']      = $error_msg;
        $out['###FLASH_FILE###'] = "typo3conf/ext/file_explorer/pi1/flash_upload/upload.swf?folder_id=".$this->base->_GP['folder']."&amp;mfs=".$maxFilesize."&amp;fe_user_cookie=".$_COOKIE['fe_typo_user']."&amp;user_agent=".base64_encode($_SERVER['HTTP_USER_AGENT'])."&amp;timestamp=".mktime();

        return $out;
    }

	function getFileForm($action = 'create', $error, $fileData = array())
	{
	    if( count($error) > 0 ){
	        foreach ($error AS $msg){
	            $error_msg .= $msg;
	        }
	    }

	    $this->base->_GP['form'] = (!empty($this->base->_GP['form'])) ? $this->base->_GP['form'] : array();
        $this->base->_GP['form'] = array_merge($fileData, $this->base->_GP['form']);
        unset($this->base->_GP['form']['writePermission']);

	    $hiddenFields = array( '[action]'  => $this->base->_GP['action'],
	                           '[folder]'  => $this->base->_GP['folder'],
	                           '[id]' => $this->base->_GP['id'],
	                           '[popup]'   => $this->base->_GP['popup'] );
        $out['markerArray']['###ERROR###']             = $error_msg;
        $out['markerArray']['###HIDDEN###']            = $this->getHiddenFields($hiddenFields);
		$out['markerArray']['###FORM_ACTIONURL###'] = htmlspecialchars(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'));

		$out['markerArray']['###SUBMIT_VALUE###'] = $this->base->pi_getLL('form.submit');
		$out['markerArray']['###SUBMIT_ONCLICK_VALUE###'] = $this->base->pi_getLL('form.submitDisabled');

        $out['markerArray']['###INPUT_TITLE###'] = $this->base->_GP['form']['title'];
        $out['markerArray']['###INPUT_DESCRIPTION###'] = $this->base->_GP['form']['description'];
        $out['markerArray']['###INPUT_FILE###']        = '<input type="file" name="upload[]" />';

		$out['markerArray']['###TITLE###'] = $this->base->pi_getLL('form.title');
		$out['markerArray']['###FILE###'] = $this->base->pi_getLL('form.file');
		$out['markerArray']['###DESCRIPTION###'] = $this->base->pi_getLL('form.description');


        if( $action == 'edit' ){
            $out['markerArray']['###INPUT_FILE###'] = '<div class="fileexplorer_formInputText_disabled">'.$this->base->_GP['form']['file'].'</div>';
        }

		if( ($fileData['writePermission'] == 1 && $action == 'edit') || $action == 'create' )
        	$out['subpartArray']['###NO_PERMISSIONS###'] = '';
        else{
        	$out['subpartArray']['###FORM_WRAP###'] = '';
			$out['markerArray']['###NO_PERMISSIONS_TEXT###'] =  $this->base->pi_getLL('form.noPerm');
		}
        return $out;
	}

    function getHiddenFields($fields)
	{
        $out = '';
        foreach($fields AS $name => $value){
            $out .= '<input type="hidden" name="'.$this->base->prefixId.$name.'" value="'.$value.'" />';
        }
        return $out;
    }

    function getCheckBoxes($name)
    {
        $sql = "SELECT uid,title FROM `fe_groups` WHERE uid IN (".$this->base->conf['valid_fe_user_groups'].")";

        $res = $GLOBALS['TYPO3_DB'] ->sql_query($sql);
        while( false != ($row = @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ){
		    $add = '';
		    if( @in_array($row['uid'], $this->base->_GP['form'][$name]) ){
		        $add = ' checked="checked" ';
		    }
             $out .= '<div><input type="checkbox" name="'.$this->base->prefixId.'[form]['.$name.'][]" value="'.$row['uid'].'" '.$add.' />'.$row['title'].'</div>';
	    }
        return $out;
    }

	function getFolderForm($action = 'create', $error, $permissions = array())
	{
		$out = array();

	    if( count($error) > 0 ){
	        foreach ($error AS $msg){
	            $error_msg .= $msg;
	        }
	    }

	    $hiddenFields = array( '[action]'  	=> $this->base->_GP['action'],
	                           '[folder]'  	=> $this->base->_GP['folder'],
	                           '[id]' 		=> $this->base->_GP['id'],
	                           '[popup]'   	=> $this->base->_GP['popup'] );

	    $out['markerArray']['###ERROR###'] = $error_msg;
        $out['markerArray']['###HIDDEN###'] = $this->getHiddenFields($hiddenFields);
        $out['markerArray']['###FORM_ACTIONURL###'] = htmlspecialchars(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'));
        $out['markerArray']['###INPUT_TITLE###'] = '<input class="fileexplorer_formInputText" type="text" name="'.$this->base->prefixId.'[form][title]" value="'.$this->base->_GP['form']['title'].'" />';

        $out['markerArray']['###SUBMIT_VALUE###'] = $this->base->pi_getLL('form.submit');
		$out['markerArray']['###SUBMIT_ONCLICK_VALUE###'] = $this->base->pi_getLL('form.submitDisabled');

        if( ($permissions['owner'] == 1 && $action == 'edit') || $action == 'create' ){
        	$out['subpartArray']['###NO_PERMISSIONS###'] = '';
            $permission = array( 'read'  => $this->getCheckBoxes('read_perms'),
                                 'write' => $this->getCheckBoxes('write_perms') );
        }
        else{
        	$out['subpartArray']['###FORM_WRAP###'] = '';
 			$out['markerArray']['###NO_PERMISSIONS_TEXT###'] =  $this->base->pi_getLL('form.noPerm');
        }
		$out['markerArray']['###PERM_READ###'] = $this->base->pi_getLL('form.permRead');
		$out['markerArray']['###TITLE###'] = $this->base->pi_getLL('form.title');
		$out['markerArray']['###PERM_WRITE###'] = $this->base->pi_getLL('form.permWrite');
        $out['markerArray']['###READ_PERMISSIONS###']  = $permission['read'];
        $out['markerArray']['###WRITE_PERMISSIONS###'] = $permission['write'];

        return $out;
	}

}//class end

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/classes/class.tx_fileexplorer_form.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/classes/class.tx_fileexplorer_controller.php']);
}
?>