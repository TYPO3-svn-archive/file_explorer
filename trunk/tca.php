<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_fileexplorer_files"] = array (
	"ctrl" => $TCA["tx_fileexplorer_files"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,title,description,file,file_info,feCrUserId"
	),
	"feInterface" => $TCA["tx_fileexplorer_files"]["feInterface"],
	"columns" => array (
		'hidden' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'fe_group' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:file_explorer/locallang_db.xml:tx_fileexplorer_files.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"description" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:file_explorer/locallang_db.xml:tx_fileexplorer_files.description",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"feCrUserId" => Array (
    		"exclude" => 1,
    		"label" => "LLL:EXT:file_explorer/locallang_db.xml:tx_fileexplorer_files.feCrUserId",
    		"config" => Array (
    			"type" => "group",
    			"internal_type" => "db",
    			"allowed" => "fe_users",
    			"size" => 2,
    			"minitems" => 1,
    			"maxitems" => 1,
    		)
    	),
		"file" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:file_explorer/locallang_db.xml:tx_fileexplorer_files.file",
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"disallowed" => "php,php3",
				"max_size" => 500000,
				"uploadfolder" => "uploads/tx_fileexplorer",
				"size" => 4,
				"minitems" => 1,
				"maxitems" => 1,
			)
		),
		"file_info" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:file_explorer/locallang_db.xml:tx_fileexplorer_files.file_info",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title, description, file, file_info, feCrUserId")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);
?>