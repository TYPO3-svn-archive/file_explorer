<?php

require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_fileexplorer_upload
{
	/* Global Configuration */
	var $base;

	function tx_fileexplorer_upload(&$base)
	{
	    $this->file  = '';
		$this->base = $base;
		$this->path  = PATH_site.$this->base->conf['upload_folder'];
	}

	function uploadSingleFile($targetFolder)
	{
		if (!t3lib_div::upload_copy_move($this->file['tmp_name'],$this->path.'/'.$targetFolder.$this->file['name']))
			$error= $this->pi_getLL('error.uploadFatalInternal');

        return $error;
	}

	function uploadFileChecks($targetFolder,$file){
		$error = array();
// 	    $file = ;
// print_r($file);
        if( empty($file['tmp_name']) || empty($file['name']) ){
            return array($this->base->pi_getLL('error.noFile'));
        }

        if( $this->base->conf['files.']['ignoreAllowed'] == 0 && $this->checkFileType($file['name'], $this->base->conf['files.']['allowedTypes'], true) === false ){
            $error[] = $this->pi_getLL('error.wrongFileType').$this->base->conf['files.']['allowedTypes'];
        }
        elseif( $this->base->conf['files.']['ignoreAllowed'] == 1 && $this->checkFileType($file['name'], $this->base->conf['files.']['disallowedTypes'], false) === false ){
        	$error[] = $this->base->pi_getLL('error.wrongFileType').$this->base->conf['files.']['disallowedTypes'];
        }

        if( ($file['size']/1024) > $this->base->conf['files.']['maxKb'] ){
            $error[] = $this->base->pi_getLL('error.fileTooBig').$this->base->conf['files.']['maxKb'];
        }
        if( count($error) > 0 ){
            return $error;
        }
        $file['name'] = $this->convertToFilename($file['name']);
        $file['name'] = $this->getNotExistingFileame($file['name'],$targetFolder);

		$this->file = $file;
	}

	function getFilename()
	{
	    return $this->file['name'];
	}

	function checkFileType($file, $tmpTypes, $checkAllowed)
	{
	    if($tmpTypes == '*' && $checkAllowed == true){
	        return true;
	    }
	    $types = explode(',', $tmpTypes);
		$tmp = explode('.', $file);
		$suffix = strtolower( $tmp[(count($tmp)-1)] );

		if( (in_array($suffix, $types) && $checkAllowed == true) || (!in_array($suffix, $types) && $checkAllowed == false) ){
			return true;
		}
		else{
			return false;
		}
	}

	function checkForZipFile(){
		$zipTypes = array('zip');
		$tmp = explode('.', $this->file['name']);
		$suffix = strtolower( $tmp[(count($tmp)-1)] );
		if ( in_array($suffix, $zipTypes)) {
			return true;
		}
		else{
			return false;
		}
	}

    function convertToFilename($string)
	{
		$string = strtolower( $string );

		$search = array(
// 						'/ö/',
// 							'/ü/',
// 							'/ä/',
							'/ß/',
// 							'/ /',
 							'/[^a-z0-9\-_\ \.\äöü]/',
							'/\_{1,}/'
							);
		$replace = array(
// 						 'oe',
// 							'ue',
// 							'ae',
							'ss',
// 							'_',
 							'',
							'_'
							);
		return preg_replace($search, $replace, $string);
	}

	function getNotExistingFileame($file,$path)
	{
		if( !is_file($this->path.'/'.$path.$file) ){
			return $file;
		}
		$number = '01';
		$file = $number.'_'.$file;
		while( is_file($this->path.'/'.$path.$file) ){
			$number = ((int)$number+1);
			for($i=0; $i <= (2-strlen($number)); $i++){
				$number = '0'.$number;
			}
			$file = $number.substr($file, 3);
		}
		return	$file;
	}


}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/classes/class.tx_fileexplorer_upload.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer/pi1/classes/class.tx_fileexplorer_upload.php']);
}
?>