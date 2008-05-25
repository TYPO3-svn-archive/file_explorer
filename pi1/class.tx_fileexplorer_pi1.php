<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 henning Borchers <hb@triquart.de>
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
require_once(t3lib_extMgm::extPath('file_explorer')."pi1/classes/class.tx_fileexplorer_controller.php");

/**
 * Plugin 'fileexplorer' for the 'file_explorer' extension.
 *
 * @author	henning Borchers <hb@triquart.de>
 * @package	TYPO3
 * @subpackage	tx_fileexplorer
 */
class tx_fileexplorer_pi1 extends tslib_pibase
{
	var $prefixId      = 'tx_fileexplorer_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_fileexplorer_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'file_explorer';	// The extension key.
    var $pi_checkCHash = true;

	function init($conf)
	{
        $this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 0;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		$this->_GP = t3lib_div::GPvar($this->prefixId);
		//!TODO: Secure and avoid possible mysql injections
		$this->_GP['id'] = intval($this->_GP['id']);
		$this->_GP['folder'] = intval($this->_GP['folder']);


 		$this->conf['templateFile'] = ( !empty($this->conf['template']) ) ? $this->conf['template']: 'EXT:'.$this->extKey.'/template/template.tmpl';


		if($this->_GP['popup'] != 1)
		{
			// Init FlexForm
			$this->pi_initPIflexForm();
	 		$piFlexForm = $this->cObj->data['pi_flexform'];
			if (!empty($piFlexForm))
			{
	    		foreach ( $piFlexForm['data'] as $sheet => $data )
	    		{
	    		    foreach ( $data as $lang => $value )
	    		    {
	    		        foreach ( $value as $key => $val )
	    		        {
	    		            $this->conf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
	    		        }
	    		    }
	    		}
			}

			$cObj = t3lib_div::makeInstance("tslib_cObj");
			$contextmenuJS = $cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'/js/functions.js');
			$arrayMarkers = array('folderDelConfirm','fileDelConfirm');
				foreach ($arrayMarkers as $marker) {
				$markerArray['###'.strtoupper($marker).'###'] = $this->pi_getLL('js.'.$marker);
			}

			$GLOBALS['TSFE']->additionalHeaderData['tx_jquerythickbox_inc'] .= t3lib_div::wrapJS($cObj->substituteMarkerArrayCached($contextmenuJS,$markerArray));

			$templateCode = $this->cObj->fileResource($this->conf['templateFile']);
			$key = 'EXT:file_explorer_' . md5($templateCode);
			if (!isset($GLOBALS['TSFE']->additionalHeaderData[$key])) {
				$headerParts = $this->cObj->getSubpart($templateCode, '###HEADER_ADDITIONS###');
				if ($headerParts) {
					$headerParts = $this->cObj->substituteMarker($headerParts, '###SITE_REL_PATH###', t3lib_extMgm::siteRelPath($this->extKey));
					$GLOBALS['TSFE']->additionalHeaderData[$key] = $headerParts;
				}
			}

		}
		else
		{
			// Load Flex Conf from Session
			$this->conf = array_merge($this->conf, $GLOBALS["TSFE"]->fe_user->getKey('ses', $this->prefixId));
		}

		$this->_GP['folder'] = ( !empty($this->_GP['folder']) ) ? $this->_GP['folder'] : $this->conf['root_page'];
		$this->conf['upload_folder'] = ( $this->conf['upload_folder'][strlen($this->conf['upload_folder'])-1] == '/' ) ? $this->conf['upload_folder'] : $this->conf['upload_folder'].'/';
		$this->conf['trash_folder'] = ( $this->conf['trash_folder'][strlen($this->conf['trash_folder'])-1] == '/' ) ? $this->conf['trash_folder'] : $this->conf['trash_folder'].'/';

		$GLOBALS["TSFE"]->fe_user->setKey('ses', $this->prefixId, $this->conf);
	}

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
    function main($content,$conf)
    {
        $this->init($conf);
		$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_controller');
		$controller = new $newClass($this);
        $content = $controller->handle();

        return '<div class="fileexplorer_allWrap">'.$content.'</div>';
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/class.tx_fileexplorer_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/class.tx_fileexplorer_pi1.php']);
}

?>