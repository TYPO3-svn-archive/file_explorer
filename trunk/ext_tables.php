<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::allowTableOnStandardPages('tx_fileexplorer_files');


t3lib_extMgm::addToInsertRecords('tx_fileexplorer_files');

$TCA["tx_fileexplorer_files"] = array (
	"ctrl" => array (
		'title' => 'LLL:EXT:file_explorer/locallang_db.xml:tx_fileexplorer_files',
		'label' => 'file',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icons/icon_tx_fileexplorer_files.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, titel, description, file",
	)
);

$tempColumns = Array (
	"tx_fileexplorer_read" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:file_explorer/locallang_db.xml:pages.tx_fileexplorer_tx_fileexplorer_read",
		"config" => Array (
			"type" => "group",
			"internal_type" => "db",
			"allowed" => "fe_groups",
			"size" => 6,
			"minitems" => 0,
			"maxitems" => 10,
		)
	),
	"tx_fileexplorer_write" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:file_explorer/locallang_db.xml:pages.tx_fileexplorer_tx_fileexplorer_write",
		"config" => Array (
			"type" => "group",
			"internal_type" => "db",
			"allowed" => "fe_groups",
			"size" => 6,
			"minitems" => 0,
			"maxitems" => 10,
		)
	),
	"tx_fileexplorer_feCrUserId" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:file_explorer/locallang_db.xml:pages.tx_fileexplorer_tx_fileexplorer_feCrUserId",
		"config" => Array (
			"type" => "group",
			"internal_type" => "db",
			"allowed" => "fe_users",
			"size" => 2,
			"minitems" => 1,
			"maxitems" => 1,
		)
	),
	"tx_fileexplorer_readPublic" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:file_explorer/locallang_db.xml:pages.tx_fileexplorer_tx_fileexplorer_readPublic",
		'config' => array (
			'type' => 'check',
			'default' => '0'
		)
	)
);


t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages",$tempColumns,1);


$TCA['pages']['types']['150']['showitem'] = 'title;LLL:EXT:lang/locallang_general.php:LGL.title;;;;1-1-1, tx_fileexplorer_read;;;;2-2-2, tx_fileexplorer_write;;;;3-3-3, tx_fileexplorer_feCrUserId;;;;4-4-4, tx_fileexplorer_readPublic;;;;5-5-5';

// Adding pages_types:
// t3lib_div::array_merge() MUST be used!
$PAGES_TYPES = t3lib_div::array_merge( array('150' => Array('icon' => ("../typo3conf/ext/file_explorer/icons/icon_tx_fileexplorer_folder.gif") ) ),
                                       $PAGES_TYPES);

$TCA['pages']['columns']['doktype']['config']['items'][] = array( 0 => 'fileexplorer', 1 => 150);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/flexform_ds.xml');
t3lib_extMgm::addPlugin(array('LLL:EXT:file_explorer/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_fileexplorer_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_fileexplorer_pi1_wizicon.php';

t3lib_extMgm::addStaticFile($_EXTKEY,'static/file_explorer/', 'file_explorer default TS');


?>