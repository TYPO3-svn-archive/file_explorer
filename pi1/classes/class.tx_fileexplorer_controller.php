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

require_once(t3lib_extMgm::extPath('file_explorer')."pi1/classes/class.tx_fileexplorer_form.php");
require_once(t3lib_extMgm::extPath('file_explorer')."pi1/classes/class.tx_fileexplorer_view.php");
require_once(t3lib_extMgm::extPath('file_explorer')."pi1/classes/class.tx_fileexplorer_data.php");

class tx_fileexplorer_controller
{
	/* Extension Stuff */
	var $dataObj;
	var $base;

	function tx_fileexplorer_controller(&$base)
	{
		$this->base= $base;

		$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_data');
		$this->dataObj= new $newClass($this->base);

	    if( empty($this->base->_GP['action']) && empty($this->base->_GP['view']) )
	    {
	        $this->base->_GP['view'] = $this->base->conf['default_view'];
	    }
	}

	function handle()
	{
	    $out = '';

// 		print_r($this->base->_GP);
	    if( !empty($this->base->_GP['action']) )
	    {
	    	// FORM DISPLAY
			$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_form');
			$form = new $newClass($this->base);

	    	switch($this->base->_GP['action'])
		    {
		        case 'create_folder':
		            $out .= $form->getHtml('create_folder');
		            break;
				case 'create_file':
		            $out .= $form->getHtml('create_file');
		            break;
	            case 'create_file_flash':
		            $out .= $form->getHtml('create_file_flash');
		            break;
	            case 'edit_file':
		            $out .= $form->getHtml('edit_file');
		            break;
	            case 'edit_folder':
		            $out .= $form->getHtml('edit_folder');
		            break;
	            default:
	                $out .= 'DEFAULT_';
	                break;
		    }
	    }
	    else
	    {
	    	// GENERAL DISPLAY
			$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_view');
			$view = new $newClass($this->base);
	        if( $this->base->_GP['view'] == 'detail' )
	        {
				$curFile = $this->dataObj->getFile($this->base->_GP['id']);
	            $out .= $view->displayDetail($curFile,$this->dataObj->getFolderPath($curFile['pid'],$this->base->conf['root_page']));
	        }
	        elseif( !empty($this->base->_GP['view']) )
	        {
	            $out .= $view->displayList( $this->dataObj->getData($this->base->_GP['folder']), $this->dataObj->getCurrentFolder($this->base->_GP['folder']), $this->dataObj->getPath($this->base->_GP['folder'],$this->base->conf['root_page']));
	        }
	    }
        return $out;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/classes/class.tx_fileexplorer_controller.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/classes/class.tx_fileexplorer_form.php']);
}
?>