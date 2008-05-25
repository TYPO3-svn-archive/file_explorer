<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_fileexplorer_files=1
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_fileexplorer_files", field "description"
	# ***************************************************************************************
RTE.config.tx_fileexplorer_files.description {

  disableColorPicker = 1

  hidePStyleItems = H1, H2, H3, H4, H5, H6, PRE
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {

    allowTags = b, strong, i, em, u, div, p, ol, li, ul

    tags.div.remap = P
  }
}
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_fileexplorer_pi1 = < plugin.tx_fileexplorer_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_fileexplorer_pi1.php','_pi1','list_type',0);

$TYPO3_CONF_VARS['FE']['eID_include']['tx_fileexplorer_pi1'] = 'EXT:file_explorer/pi1/eIDinit.php';
?>