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
// 		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->_GP = t3lib_div::GPvar($this->prefixId);
		//!TODO: Secure and avoid possible mysql injections
		$this->_GP['id'] = intval($this->_GP['id']);
		$this->_GP['folder'] = intval($this->_GP['folder']);

		if (count($this->_GP['form']) > 0){
			foreach ($this->_GP['form'] as $curField=>$val){
				$this->_GP['form'][$curField] = $this->RemoveXSS($val);
			}
		}

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


			$templateCode = $this->cObj->fileResource($this->conf['templateFile']);
			$key = 'EXT:file_explorer_js_css' . md5($templateCode);
			if (!isset($GLOBALS['TSFE']->additionalHeaderData[$key])) {
				$headerParts = $this->cObj->getSubpart($templateCode, '###HEADER_ADDITIONS###');
				if ($headerParts) {
					$headerParts = $this->cObj->substituteMarker($headerParts, '###SITE_REL_PATH###', t3lib_extMgm::siteRelPath($this->extKey));
 					$GLOBALS['TSFE']->additionalHeaderData[$key] = $headerParts;
				}
			}

			$cObj = t3lib_div::makeInstance("tslib_cObj");
			$contextmenuJS = $cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'/js/functions.js');
			$arrayMarkers = array('folderDelConfirm','fileDelConfirm');
				foreach ($arrayMarkers as $marker) {
				$markerArray['###'.strtoupper($marker).'###'] = $this->pi_getLL('js.'.$marker);
			}
			if ($this->conf['recursiveDelete']==1){
			  $markerArray['###FOLDERDELCONFIRM###'] = $this->pi_getLL('js.folderDelConfirmRecursive');
			}
			$key = 'EXT:file_explorer_contextmenu' . md5($templateCode);
			if (!isset($GLOBALS['TSFE']->additionalHeaderData[$key])) {
				$GLOBALS['TSFE']->additionalHeaderData[$key] = t3lib_div::wrapJS($cObj->substituteMarkerArrayCached($contextmenuJS,$markerArray));
			}
		}
		else
		{
			// Load Flex Conf from Session
			$this->conf = array_merge($this->conf, $GLOBALS["TSFE"]->fe_user->getKey('ses', $this->prefixId));
		}

		if (empty($this->_GP['folder'])){
		  if (!empty($this->conf['startfolder'])){
			$this->_GP['folder'] = $this->conf['startfolder'];
		  }
		  else{
			$this->_GP['folder'] = $this->conf['root_page'];
		  }
		}
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

	/**
	 * Wrapper for the RemoveXSS function.
	 * Removes potential XSS code from an input string.
	 *
	 * Using an external class by Travis Puderbaugh <kallahar@quickwired.com>
	 *
	 * @param	string		Input string
	 * @return	string		Input string with potential XSS code removed
	 */
	function RemoveXSS($val)	{
		// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
		// this prevents some character re-spacing such as <java\0script>
		// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
		$val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);

		// straight replacements, the user should never need these since they're normal characters
		// this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search.= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search.= '1234567890!@#$%^&*()';
		$search.= '~`";:?+/={}[]-_|\'\\';

		for ($i = 0; $i < strlen($search); $i++) {
			// ;? matches the ;, which is optional
			// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

			// &#x0040 @ search for the hex values
			$val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
			// &#00064 @ 0{0,7} matches '0' zero to seven times
			$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
		}

		// now the only remaining whitespace attacks are \t, \n, and \r
		$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
		$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
		$ra = array_merge($ra1, $ra2);

		$found = true; // keep replacing as long as the previous round replaced something
		while ($found == true) {
			$val_before = $val;
			for ($i = 0; $i < sizeof($ra); $i++) {
				$pattern = '/';
				for ($j = 0; $j < strlen($ra[$i]); $j++) {
					if ($j > 0) {
						$pattern .= '(';
						$pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
						$pattern .= '|(&#0{0,8}([9][10][13]);?)?';
						$pattern .= ')?';
					}
					$pattern .= $ra[$i][$j];
				}
				$pattern .= '/i';
				$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
				$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
				if ($val_before == $val) {
					// no replacements were made, so exit the loop
					$found = false;
				}
			}
		}

		return $val;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/class.tx_fileexplorer_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/class.tx_fileexplorer_pi1.php']);
}

?>