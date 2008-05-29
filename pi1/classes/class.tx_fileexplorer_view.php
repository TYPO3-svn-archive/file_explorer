<?php

require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_fileexplorer_view
{
	var $iconSize	   = '';
	var $base;

	function tx_fileexplorer_view(&$base)
	{
		$this->base = $base;
		$this->cObj = t3lib_div::makeInstance("tslib_cObj");
		$this->templateCode = $this->cObj->fileResource($this->base->conf["templateFile"]);
	}

	function displayList($data, $currentFolder, $path, $file = array())
	{
	    $result = '';
	    switch ($this->base->_GP['view'])
	    {
	        case 'review':
	            $mainSubpart = '###REVIEW_TEMPLATE###';
	            $this->view = 'review';
	            $this->iconSize = 'medium';
	            break;
            default:
                $mainSubpart = '###LIST_TEMPLATE###';
                $this->view = 'list';
                $this->iconSize = 'small';
                $this->base->_GP['view'] = 'list';
                break;
	    }
	    $template = $this->cObj->getSubpart($this->templateCode, $mainSubpart);
		$markerArray['###EDIT_ICON###'] = $this->cObj->IMAGE($this->base->conf['icons.']['editIcon.']);
		$markerArray['###DELETE_ICON###'] = $this->cObj->IMAGE($this->base->conf['icons.']['deleteIcon.']);
		$markerArray['###VIEW_ICON###'] = $this->cObj->IMAGE($this->base->conf['icons.']['viewIcon.']);
		$markerArray['###DOWNLOAD_ICON###'] = $this->cObj->IMAGE($this->base->conf['icons.']['downloadIcon.']);
		$markerArray['###BROWSE_ICON###'] = $this->cObj->IMAGE($this->base->conf['icons.']['browseIcon.']);


		$markerArray['###EDIT_TEXT###']=$this->base->pi_getLL('contextMenu.edit');
		$markerArray['###DELETE_TEXT###']=$this->base->pi_getLL('contextMenu.delete');
		$markerArray['###VIEW_TEXT###']=$this->base->pi_getLL('contextMenu.view');
		$markerArray['###DOWNLOAD_TEXT###']=$this->base->pi_getLL('contextMenu.download');
		$markerArray['###BROWSE_TEXT###'] = $this->base->pi_getLL('contextMenu.browse');


        $subpartArray['###FILELIST###'] = $this->wrapItems($template, '###FILELIST###', $data, $currentFolder,$path);
        $subpartArray['###HEADER###'] = $this->wrapHeader($template, '###HEADER###', $currentFolder, $path);

    	return $this->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray, array());
	}

	function displayDetail($file,$path)
	{
	    $this->view = 'detail';
	    $this->iconSize = 'large';
	    $result = '';
	    $mainSubpart = '###DETAIL_TEMPLATE###';
	    $template = $this->cObj->getSubpart($this->templateCode, $mainSubpart);

	    $thumb = $this->getFileThumbParams($file['file'],'',$path,$file['title']);

	    $file['description'] = (empty($file['description']))?$this->base->pi_getLL('detail.no_description'):$file['description'];
        $link['download'] = 'index.php?eID='.$this->base->prefixId.'&amp;action=download_file&amp;id='.$file['uid'];

	    $markerArray['###IMAGE###'] = $this->getThumb($thumb['file'], $file['title'], $thumb['params']);
	    $markerArray['###DESCRIPTION###']  = nl2br($file['description']);
		$markerArray['###NOTE###'] = $this->base->pi_getLL('detail.note');

	    if(strlen($file['title']) > $this->base->conf['fe_display.']['max_titleLength.']['detail'])
            $markerArray['###TITLE###']  = substr($file['title'], 0, $this->base->conf['fe_display.']['max_titleLength.']['detail']).'...';
		else
			$markerArray['###TITLE###'] = $file['title'];

	    if(strlen($file['file']) > $this->base->conf['fe_display.']['max_fileLength.']['detail'])
	   		$markerArray['###FILENAME###'] = substr($file['file'], 0, $this->base->conf['fe_display.']['max_fileLength.']['detail']).'...';
		else
			$markerArray['###FILENAME###']  = $file['file'];


		$wrappedSubpartArray['###DOWNLOAD_LINK###'] = array('<a href="'.$link['download'].'">','</a>');

    	return $this->cObj->substituteMarkerArrayCached($template, $markerArray, array(), $wrappedSubpartArray);
	}


	function getThumb($file, $alt = '', $thumbParams = '', $crop = 0)
	{

        $maxW = $this->base->conf['thumbnails.'][$this->view.'.']['maxW'];
        $maxH = $this->base->conf['thumbnails.'][$this->view.'.']['maxH'];

		if($crop == 0){
	        $img = Array();
	        $img['file'] = $file;
	        $img['params'] = $thumbParams;
	        $img['altText'] = $alt;
	        $img['titleText'] = $alt;
	        $img['file.']['maxW'] = $maxW;
	        $img['file.']['maxH'] = $maxH;
		}
		else
		{
			$img = array(	'file'  => 'GIFBUILDER',
			             	'file.' => array( 	'format' 	=> 'jpeg',
			                               		'10'		=> 'IMAGE',
						                   		'10.' 		=> array( 	'file' => $file,
						                                            	'align' => 'center',
						                                       		)
	                                      	),
	        				'params' 	=> $thumbParams,
	                        'altText' 	=> $alt,
	                        'titleText' => $alt
            			);

			$imageSize = getimagesize($file);
			$img_w = $imageSize[0];
			$img_h = $imageSize[1];

			if($img_w < $maxW)
				$maxW = $img_w;
			if($img_h < $maxH)
				$maxH = $img_h;

			$factor_w = $img_w/$maxW;
			$factor_h = $img_h/$maxH;

			if( $factor_h > $factor_w ){
				$img['file.']['10.']['file.']['maxW'] = $maxW;
			}
			elseif( $factor_w > $factor_h ){
				$img['file.']['10.']['file.']['maxH'] = $maxH;
			}
			else{
				$img['file.']['10.']['file.']['maxH'] = $maxH;
				$img['file.']['10.']['file.']['maxW'] = $maxW;
			}
			$img['file.']['XY'] = $maxW.','.$maxH;
		}

	    return $GLOBALS['TSFE']->cObj->IMAGE($img);
	}

	function getFileThumbParams($file, $id = -1,$path,$title)
	{
		$thumb = array(	'crop' 	=> 0,
						'params' 	=> 'class="fileexplorer_thumbIcon"',
						'file'		=> $this->base->conf['mimetypes.']['empty.']['file_name']);
		$selectedType = '';
	    $tmp = explode('.', $file);
	    $suffix = strtolower($tmp[(count($tmp)-1)]);

		foreach($this->base->conf['mimetypes.'] AS $typeName => $suffixList){
			if (t3lib_div::inList($suffixList,$suffix)){
				$selectedType = $typeName;
				break;
			}

		}

	    if($selectedType != ''){
	    	$thumb['params'] = 'class="fileexplorer_thumbIcon"';
	    	$thumb['file']   = $this->base->conf['mimetypes.'][$selectedType.'.']['file_name'];
	    }
	    elseif( t3lib_div::inList( $this->base->conf['thumbnails.']['as_images'],$suffix) ){
	        $thumb['params'] = 'class="fileexplorer_thumbImage"';
	        $thumb['file']   = $this->base->conf['upload_folder'].$path.$file;
	        $thumb['crop']   = (int)$this->base->conf['thumbnails.'][$this->view.'.']['crop'];
	    }
	    {
	    	$thumb['params'] .= ' id="cMenuItem_'.$id.'" onmouseover="fileexplorer_cMenu('.$id.','.$GLOBALS['TSFE']->id.',\'file\',\''.$title.'\',\''.$this->view.'\');" ';
	    }

	    return $thumb;
	}

    function getNavigationPath($path)
    {
        $result = '';
        if(count($path) < 1)
        	$result .= '/';
        foreach ($path AS $folder){
		    $link = htmlspecialchars( $this->base->pi_getPageLink($GLOBALS['TSFE']->id,'', array($this->base->prefixId.'[folder]' => $folder['uid'],
                                                                                              $this->base->prefixId.'[view]' => $this->base->_GP['view']) ));
		    $result .= '<a href="'.$link.'" class="fileexplorer_pathNavigation">/'.$folder['title'].'</a>';
		}
		return $result;
    }

	function getRelFolderPath($path){
		$pathName = array();
		foreach ($path AS $curPath){
			$pathName[] = $curPath['title'];
		}
		$relPath = implode('/',$pathName).'/';
		return $relPath;
	}

	function wrapItems($templateCode, $part, $data, $currentFolder,$path)
	{
	    $result = '';
		$template = $this->cObj->getSubpart($templateCode, $part);


		// get backfolder [..]
	    if($currentFolder['isRoot'] != 1){
            $template_back      = $this->cObj->getSubpart($template, '###BACK_FOLDER###');
            $thumb['file']      = 'typo3conf/ext/'.$this->base->extKey.'/icons/mimetypes/'.$this->iconSize.'/dialog-ok.png';
            $thumb['params']    = 'class="fileexplorer_folderIcon"';
            $link['back'] = htmlspecialchars($this->base->pi_getPageLink($GLOBALS['TSFE']->id,'', array($this->base->prefixId.'[folder]' => $currentFolder['pid'],$this->base->prefixId.'[view]' => $this->base->_GP['view']) ));
            $wrappedSubpartArray['###LINK_WRAP###'] = array('<a href="'.$link['back'].'" >','</a>');

            $markerArray['###FILENAME###'] = $this->base->pi_getLL('link.back');
            $markerArray['###ICON###']  = $this->getThumb($thumb['file'],$this->base->pi_getLL('link.back.desc'), $thumb['params']);
            $result .= $this->cObj->substituteMarkerArrayCached($template_back, $markerArray, array(), $wrappedSubpartArray);
        }

		foreach ($data AS $row){
		    $template_item = $this->cObj->getSubpart($template, '###ITEM###');

		    $markerArray['###DATE_CREATE###'] = date( $this->base->conf['fe_display.']['dateFormat'], $row['crdate']);
		    $markerArray['###DATE_EDIT###']   = date( $this->base->conf['fe_display.']['dateFormat'], $row['tstamp']);
		    $markerArray['###TITLE###']       = $row['title'];
		    $markerArray['###FILENAME###']    = $row['file'];
		    $markerArray['###AUTHOR###']      = $row['username'];

		    switch($this->base->_GP['view']){
		        case 'review':
		            if(strlen($row['title']) > $this->base->conf['fe_display.']['max_titleLength.']['review']) {
		                $markerArray['###TITLE###']    = substr($row['title'], 0, $this->base->conf['fe_display.']['max_titleLength.']['review']).'...';
		            }
		            if(strlen($row['file']) > $this->base->conf['fe_display.']['max_fileLength.']['review']) {
		                $markerArray['###FILENAME###']    = substr($row['file'], 0, $this->base->conf['fe_display.']['max_fileLength.']['review']).'...';
		            }
		            break;
		        case 'list':
		            if(strlen($row['title']) > $this->base->conf['fe_display.']['max_titleLength.']['list']) {
		                $markerArray['###TITLE###']    = substr($row['title'], 0, $this->base->conf['fe_display.']['max_titleLength.']['list']).'...';
		            }
		            if(strlen($row['file']) > $this->base->conf['fe_display.']['max_fileLength.']['list']) {
		                $markerArray['###FILENAME###']    = substr($row['file'], 0, $this->base->conf['fe_display.']['max_fileLength.']['list']).'...';
		            }
		            break;
		    }

		    $thumb = array('crop' => 0);

		    // get folder
			if($row['isFolder'] == 1)
		    {
		        $folderName = $markerArray['###TITLE###'];
		        $markerArray['###FILE_SIZE###'] = '';
				// get foldersize
				if ($this->base->conf['fe_display.']['showFolderSize']==1){
 					$tmpFolderSize = round($this->getFolderSize($this->base->conf['upload_folder'].$this->getRelFolderPath($path).$row['title'])/1024);
					if($tmpFolderSize > 1024){
						$markerArray['###FILE_SIZE###'] = round($tmpFolderSize/1024, 1)." MB";
					}
					else{
						$markerArray['###FILE_SIZE###'] = $tmpFolderSize." KB";
					}
				}

		        $thumb['file']        = 'typo3conf/ext/'.$this->base->extKey.'/icons/mimetypes/'.$this->iconSize.'/folder-open.png';
		        $thumb['params']      = 'class="fileexplorer_folderIcon" id="cMenuItem_'.$row['uid'].'" onmouseover="fileexplorer_cMenu('.$row['uid'].','.$GLOBALS['TSFE']->id.',\'folder\',\''.(str_replace("'", "\'", $row['title'])).'\',\''.$this->view.'\');"';
		        $link['folder']      = htmlspecialchars($this->base->pi_getPageLink($GLOBALS['TSFE']->id,'', array($this->base->prefixId.'[folder]' => $row['uid'],$this->base->prefixId.'[view]'   => $this->base->_GP['view']) ));

                if($row['write'] != 1){
		            $markerArray['###LINK_EDIT###'] = '';
		        }
		        if($row['owner'] != 1){
		            $markerArray['###DELETE_LINK###'] = '';
		        }

		        $wrappedSubpartArray['###LINK_WRAP###'] = array('<a href="'.$link['folder'].'" >','</a>');

                $markerArray['###FILENAME###']      = $folderName;
                $markerArray['###TITLE###']         = '';
                $markerArray['###ITEM_ID###']       = 'feFolder_'.$row['uid'];

		        //$wrappedSubpartArray['###THUMB_LINK###'] = array('<a href="'.$link['folder'].'">','</a>');
		        //$wrappedSubpartArray['###THUMB_LINK###'] = array('<div id="cMenuItem_Folder_'.$row['uid'].'" onclick="fileexplorer_cMenu('.$row['uid'].', \'folder\');">','</div>');

		    }
		    // get files
		    else{
		    	$markerArray['###ITEM_ID###'] = 'feFile_'.$row['uid'];

		        // get filesize
		        $tmpFilesize = round(filesize($this->base->conf['upload_folder'].$this->getRelFolderPath($path).$row['file'])/1024);
		        if($tmpFilesize > 1024){
		            $markerArray['###FILE_SIZE###'] = round($tmpFilesize/1024, 1)." MB";
		        }
		        else{
		            $markerArray['###FILE_SIZE###'] = $tmpFilesize." KB";
		        }

                if($row['owner'] != 1){
		            $markerArray['###DELETE_LINK###'] = '';
		            $markerArray['###LINK_EDIT###'] = '';
		        }

    		    // get the thickbox title parameter
                if(strlen($row['title']) > $this->base->conf['fe_display.']['max_titleLength.']['detail']){
                    $thickboxTitel = substr($row['title'], 0, $this->base->conf['fe_display.']['max_titleLength.']['detail']).'...';
                }
                else{
                    $thickboxTitel = $row['title'];
                }
		        // get the thickbox thumbnail
                $thumb = $this->getFileThumbParams($row['file'], $row['uid'],$this->getRelFolderPath($path),$row['title']);

                // get the thickbox link
                $link['file_thumb']  = htmlspecialchars($this->base->pi_getPageLink($GLOBALS['TSFE']->id,'', array($this->base->prefixId.'[view]' => 'detail',$this->base->prefixId.'[id]' => $row['uid'],$this->base->prefixId.'[popup]' => '1', 'type' => 769, 'height' => '500', 'width' => '600') ));
		        $wrappedSubpartArray['###LINK_WRAP###'] = array('<a href="'.$link['file_thumb'].'" onclick="tb_show(\''.$thickboxTitel.'\', \''.$link['file_thumb'].'\', \'\');return false;">','</a>');

		    }

		    $markerArray['###ICON###'] = $this->getThumb($thumb['file'], $row['title'], $thumb['params'], $thumb['crop']);

		    $result .= $this->cObj->substituteMarkerArrayCached($template_item, $markerArray, array(), $wrappedSubpartArray );
		}
		return $result;
	}

	function wrapHeader($template, $part, $currentFolder, $path)
	{
	    $result = '';
		$template_item = $this->cObj->getSubpart($template, $part);

	    $link['back'] = htmlspecialchars($this->base->pi_getPageLink($GLOBALS['TSFE']->id,'', array($this->base->prefixId.'[folder]' => $currentFolder['pid'],$this->base->prefixId.'[view]'   => $this->base->_GP['view'])) );
	    $markerArray['###LINK_BACK###'] = '<a href="'.$link['back'].'">'.$this->base->pi_getLL('link.back').'</a>';
	    if($currentFolder['isRoot'] == 1){
            $markerArray['###LINK_BACK###'] = '';
        }
		//!TODO:Using pi_getPageLink does not work when realurl is activated, the type param is missing
		$link['create_folder'] = htmlspecialchars($this->base->pi_getPageLink($GLOBALS['TSFE']->id,'',
				   array('type' => 769,
						 	$this->base->prefixId.'[folder]' => $this->base->_GP['folder'],
							$this->base->prefixId.'[action]' => 'create_folder',
	  						$this->base->prefixId.'[popup]'  => 1,
		  					'height' => 300,
		  					'width' => 300,
		  					'keepThis' => 'true',
		 					'TB_iframe' => 'true' )
																			 ));
		$link['create_file'] = htmlspecialchars($this->base->pi_getPageLink($GLOBALS['TSFE']->id,'',
					array( 'type' => 769,
						    $this->base->prefixId.'[folder]' => $this->base->_GP['folder'],
		 					$this->base->prefixId.'[action]' => 'create_file',
							$this->base->prefixId.'[popup]'  => 1,
	    					'height' => 300,
		   					'width' => 300,
		  					'keepThis' => 'true',
		 					'TB_iframe' => 'true' )
																		   ));
			$link['create_file_flash'] = htmlspecialchars( $this->base->pi_getPageLink($GLOBALS['TSFE']->id,'',
					array( 'type' => 769,
						    $this->base->prefixId.'[folder]' => $this->base->_GP['folder'],
		 					$this->base->prefixId.'[action]' => 'create_file_flash',
							$this->base->prefixId.'[popup]'  => 1,
	   						'height' => 260,
		   					'width' => 380 )
																				));

        $markerArray['###LINK_CREATE_FOLDER_TEXT###']     = '<span class="file_explorer_no_perm">'.$this->base->pi_getLL('link.create_folder').'</span>';
        $markerArray['###LINK_CREATE_FILE_TEXT###']       = '<span class="file_explorer_no_perm">'.$this->base->pi_getLL('link.create_file').'</span>';
        $markerArray['###LINK_CREATE_FILE_FLASH_TEXT###'] = '<span class="file_explorer_no_perm">'.$this->base->pi_getLL('link.create_file_flash').'</span>';

		$wrappedSubpartArray['###LINK_CREATE_FILE_WRAP###'] = '';
		$wrappedSubpartArray['###LINK_CREATE_FOLDER_WRAP###'] = '';
		$wrappedSubpartArray['###LINK_CREATE_FILE_FLASH_WRAP###'] ='';
        if($currentFolder['isRoot'] == 1 && $currentFolder['permission']['owner'] == 1)
        {
			$wrappedSubpartArray['###LINK_CREATE_FOLDER_WRAP###'] = array('<a href="'.$link['create_folder'].'" class="thickbox" title="'.$this->base->pi_getLL('link.create_folder').'">','</a>');
            $markerArray['###LINK_CREATE_FOLDER_TEXT###']   = $this->base->pi_getLL('link.create_folder');
        }
        elseif($currentFolder['isRoot'] != 1 && $currentFolder['permission']['write'] == 1)
        {
        	$wrappedSubpartArray['###LINK_CREATE_FOLDER_WRAP###'] = array('<a href="'.$link['create_folder'].'" class="thickbox" title="'.$this->base->pi_getLL('link.create_folder').'">','</a>');
			$markerArray['###LINK_CREATE_FOLDER_TEXT###']=$this->base->pi_getLL('link.create_folder');
        	$wrappedSubpartArray['###LINK_CREATE_FILE_WRAP###']  =array('<a href="'.$link['create_file'].'" class="thickbox" title="'.$this->base->pi_getLL('link.create_file').'">','</a>');
			$markerArray['###LINK_CREATE_FILE_TEXT###'] = $this->base->pi_getLL('link.create_file');
        	$wrappedSubpartArray['###LINK_CREATE_FILE_FLASH_WRAP###'] = array('<a href="'.$link['create_file_flash'].'" class="thickbox" title="'.$this->base->pi_getLL('link.create_file_flash').'">','</a>');
			$markerArray['###LINK_CREATE_FILE_FLASH_TEXT###']=$this->base->pi_getLL('link.create_file_flash');
        }

        /*
        if($currentFolder['isRoot'] == 1)
        {
        	$markerArray['###LINK_CREATE_FOLDER###']     = '<span class="file_explorer_no_perm">'.$this->base->pi_getLL('link.create_folder').'</span>';
            $markerArray['###LINK_CREATE_FILE###']       = '<span class="file_explorer_no_perm">'.$this->base->pi_getLL('link.create_file').'</span>';
            $markerArray['###LINK_CREATE_FILE_FLASH###'] = '<span class="file_explorer_no_perm">'.$this->base->pi_getLL('link.create_file_flash').'</span>';
        }
*/

        if($this->view == 'review')
        {
            $changeView['link']  = htmlspecialchars($this->base->pi_getPageLink($GLOBALS['TSFE']->id,'', array($this->base->prefixId.'[folder]' => $this->base->_GP['folder'],$this->base->prefixId.'[view]' => 'list' ) ));
            $changeView['title'] = $this->base->pi_getLL('change_view.list');
            $changeView['img']   = '<img style="border-width:0px;" src="typo3conf/ext/'.$this->base->extKey.'/icons/icon_tx_fileexplorer_listview.gif" alt="'.$this->base->pi_getLL('change_view.list').'" />';
        }
        else
        {
            $changeView['link']  = htmlspecialchars($this->base->pi_getPageLink($GLOBALS['TSFE']->id,'', array($this->base->prefixId.'[folder]' => $this->base->_GP['folder'],$this->base->prefixId.'[view]' => 'review' ) ));
            $changeView['img']   = '<img style="border-width:0px;" src="typo3conf/ext/'.$this->base->extKey.'/icons/icon_tx_fileexplorer_miniview.gif" alt="'.$this->base->pi_getLL('change_view.review').'" />';
        }

        $markerArray['###CHANGE_VIEW###'] = '<a href="'.$changeView['link'].'">'.$changeView['img'].'</a>';

        $markerArray['###NAVIGATION_PATH###'] = $this->getNavigationPath($path);

		$markerArray['###CREATE_DATE###'] = $this->base->pi_getLL('list.createDate');
		$markerArray['###SIZE###'] =$this->base->pi_getLL('list.size');
		$markerArray['###AUTHOR###'] = $this->base->pi_getLL('list.author');

	    $result = $this->cObj->substituteMarkerArrayCached($template_item, $markerArray, $subpartArray,$wrappedSubpartArray);

		return $result;
	}

	function getFolderSize($path){
		if (!is_dir($path))
			return filesize($path);
		$size=0;
		foreach (scandir($path) as $file){
			if ($file=='.' or $file=='..')
				continue;
		$size+=$this->getFolderSize($path.'/'.$file);
		}
		return $size;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/classes/class.tx_fileexplorer_view.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/classes/class.tx_fileexplorer_view.php']);
}
?>