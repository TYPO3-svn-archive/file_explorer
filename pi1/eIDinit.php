<?php
/*
$_FILES = array
(
    'Filedata' => Array
        (
            'name' => '00-Linkin_Park-Hybrid_Theory-IRC.nfo',
            'type' => 'application/octet-stream',
            'tmp_name' => '/tmp/phpVAh3rz',
            'error' => '0',
            'size' => '4781'
        )
);
$_GET =
Array
(
    'eID' => 'tx_fileexplorer_pi1',
    'fe_typo_user' => $_COOKIE['fe_typo_user'],
    'user_agent' => base64_encode($_SERVER['HTTP_USER_AGENT']),
    'action' => 'create_file_flash',
    'folder' => '76',
);
$_POST =
Array
(
    'Filename' => '00-Linkin_Park-Hybrid_Theory-IRC.nfo',
    'Upload' => 'Submit Query',
);
$_REQUEST =
Array
(
    'eID' => 'tx_fileexplorer_pi1',
    'fe_typo_user' => $_COOKIE['fe_typo_user'],
    'user_agent' => base64_encode($_SERVER['HTTP_USER_AGENT']),
    'action' => 'create_file_flash',
    'folder' => '76',
    'Filename' => '00-Linkin_Park-Hybrid_Theory-IRC.nfo',
    'Upload' => 'Submit Query',
);
*/

// simulate flash



// ignore first init (flash upload opens this script two times!)
if( ($_FILES['upload']) && ( $_GET['action']=='create_file_flash' || $_POST['action']=='create_file_flash' ) )
{
    die();
}

//file_put_contents ( PATH_site."typo3conf/ext/file_explorer/pi1/log/".date('Y-m-d_H_i_s',time())."_".microtime_float()."_request_start.log",print_r($_COOKIE,true));

tslib_eidtools::connectDB();

require_once(t3lib_extMgm::extPath('file_explorer')."pi1/classes/class.tx_fileexplorer_data.php");

/**
 * Simple function to replicate PHP5 behaviour
 */
function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return (float)$usec;
}


class tx_fileexplorer_eIDinit
{
	var $prefixId      = 'tx_fileexplorer_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_fileexplorer_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'file_explorer';	// The extension key.

	function tx_fileexplorer_eIDinit()
	{
	    $this->_GP = $_GET;
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
	}

    function main()
    {
		$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_data');
		$handleData = new $newClass($this);

        switch ($this->_GP['action'])
        {
            case 'create_file_flash':
                $_FILES['upload'] = $this->getFlashFiles();
                $handleData->insertFile($this->conf['fe_user']['uid']);
                //$this->debug();
                break;
            case 'delete_file':
                $handleData->deleteFile($this->_GP['id']);
                break;
            case 'delete_folder':
                if($handleData->deleteFolder($this->_GP['id']) === false)
                {
                	return "Achtung:\ndieser Ordner konnte nicht geloescht werden, da er Unterelemente enthaelt.";
                }
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
    function debug()
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
        $_SERVER['HTTP_USER_AGENT']
        ;

        file_put_contents ( PATH_site."typo3conf/ext/file_explorer/pi1/log/".date('Y-m-d_H_i_s',time())."_".microtime_float()."_request.log",
        $content
        );

        //echo $content;
    }
}

$eIDinit = t3lib_div::makeInstance('tx_fileexplorer_eIDinit');

echo $eIDinit->main();

?>