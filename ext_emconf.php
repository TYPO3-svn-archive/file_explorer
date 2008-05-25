<?php

########################################################################
# Extension Manager/Repository config file for ext: "file_explorer"
#
# Auto generated 25-05-2008 20:28
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'File Explorer',
	'description' => 'The File Explorer is a frontend and backend filemanagement extension.
		This extension contains read and write permissions for secure filemanagement.
		The frontend usability will be increased by a modern GUI and a multiple-file upload via flash.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '2.0.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 1,
	'createDirs' => 'uploads/tx_fileexplorer/_trash_',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Henning Borchers',
	'author_email' => 'henning_borchers@hotmail.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:137:{s:9:"ChangeLog";s:4:"6b4d";s:10:"README.txt";s:4:"ac45";s:12:"ext_icon.gif";s:4:"e5d3";s:17:"ext_localconf.php";s:4:"4960";s:14:"ext_tables.php";s:4:"9544";s:14:"ext_tables.sql";s:4:"4e0d";s:28:"ext_typoscript_editorcfg.txt";s:4:"6fc1";s:15:"flexform_ds.xml";s:4:"6fb2";s:13:"locallang.xml";s:4:"fb71";s:16:"locallang_db.xml";s:4:"0690";s:17:"locallang_tca.xml";s:4:"0b5b";s:9:"popup.css";s:4:"d1d3";s:7:"tca.php";s:4:"17d9";s:34:"static/file_explorer/constants.txt";s:4:"d41d";s:30:"static/file_explorer/setup.txt";s:4:"9b66";s:17:"js/contextmenu.js";s:4:"4df8";s:21:"js/flash_detection.js";s:4:"b9d4";s:20:"js/flexigrid.pack.js";s:4:"1908";s:15:"js/functions.js";s:4:"3883";s:14:"doc/manual.sxw";s:4:"8e79";s:19:"doc/wizard_form.dat";s:4:"b129";s:20:"doc/wizard_form.html";s:4:"de94";s:14:"pi1/ce_wiz.gif";s:4:"1cbb";s:33:"pi1/class.tx_fileexplorer_pi1.php";s:4:"cfc4";s:41:"pi1/class.tx_fileexplorer_pi1_wizicon.php";s:4:"8851";s:13:"pi1/clear.gif";s:4:"cc11";s:15:"pi1/eIDinit.php";s:4:"4239";s:17:"pi1/locallang.xml";s:4:"cfec";s:30:"pi1/classes/PHPUnzip.class.php";s:4:"64eb";s:48:"pi1/classes/class.tx_fileexplorer_controller.php";s:4:"5046";s:42:"pi1/classes/class.tx_fileexplorer_data.php";s:4:"4ccc";s:42:"pi1/classes/class.tx_fileexplorer_form.php";s:4:"4659";s:44:"pi1/classes/class.tx_fileexplorer_upload.php";s:4:"8cec";s:42:"pi1/classes/class.tx_fileexplorer_view.php";s:4:"2149";s:23:"pi1/classes/zip.lib.php";s:4:"cd33";s:27:"pi1/flash_upload/upload.swf";s:4:"bd8e";s:38:"pi1/flash_upload/assets/audio/Ding.mp3";s:4:"8259";s:39:"pi1/flash_upload/assets/styles/main.css";s:4:"0189";s:22:"template/flexigrid.css";s:4:"dc02";s:18:"template/style.css";s:4:"755e";s:22:"template/template.tmpl";s:4:"e603";s:22:"template/images/bg.gif";s:4:"5c8f";s:30:"template/images/btn-sprite.gif";s:4:"f662";s:23:"template/images/ddn.png";s:4:"42bf";s:22:"template/images/dn.png";s:4:"59ce";s:24:"template/images/fhbg.gif";s:4:"3e44";s:25:"template/images/first.gif";s:4:"6f39";s:22:"template/images/hl.png";s:4:"30f9";s:24:"template/images/last.gif";s:4:"1168";s:24:"template/images/line.gif";s:4:"3ef4";s:24:"template/images/load.gif";s:4:"a272";s:24:"template/images/load.png";s:4:"44aa";s:29:"template/images/magnifier.png";s:4:"a81f";s:24:"template/images/next.gif";s:4:"20a0";s:24:"template/images/prev.gif";s:4:"90b9";s:22:"template/images/up.png";s:4:"4626";s:23:"template/images/uup.png";s:4:"67b3";s:23:"template/images/wbg.gif";s:4:"92b1";s:26:"icons/bg_boxhead_black.gif";s:4:"6e66";s:25:"icons/bg_boxhead_blue.gif";s:4:"ef96";s:16:"icons/delete.gif";s:4:"b55e";s:14:"icons/edit.gif";s:4:"a4f8";s:17:"icons/go-jump.gif";s:4:"a1e0";s:36:"icons/icon_tx_fileexplorer_files.gif";s:4:"18b0";s:37:"icons/icon_tx_fileexplorer_folder.gif";s:4:"e5d3";s:39:"icons/icon_tx_fileexplorer_listview.gif";s:4:"6f29";s:39:"icons/icon_tx_fileexplorer_miniview.gif";s:4:"5487";s:14:"icons/save.gif";s:4:"e46a";s:14:"icons/view.gif";s:4:"b9aa";s:49:"icons/mimetypes/large/application-certificate.png";s:4:"6d9f";s:47:"icons/mimetypes/large/applications-graphics.png";s:4:"9016";s:49:"icons/mimetypes/large/applications-multimedia.png";s:4:"4beb";s:44:"icons/mimetypes/large/applications-other.png";s:4:"9bbb";s:42:"icons/mimetypes/large/audio-volume-low.png";s:4:"0620";s:41:"icons/mimetypes/large/audio-x-generic.png";s:4:"4c8b";s:36:"icons/mimetypes/large/emblem-web.png";s:4:"e479";s:31:"icons/mimetypes/large/empty.png";s:4:"552e";s:40:"icons/mimetypes/large/font-x-generic.png";s:4:"b2ba";s:45:"icons/mimetypes/large/format-justify-left.png";s:4:"04c9";s:45:"icons/mimetypes/large/graphics-svg-editor.png";s:4:"7b16";s:41:"icons/mimetypes/large/image-x-generic.png";s:4:"41e0";s:38:"icons/mimetypes/large/media-floppy.png";s:4:"9e6a";s:43:"icons/mimetypes/large/package-x-generic.png";s:4:"d73b";s:35:"icons/mimetypes/large/text-html.png";s:4:"88ee";s:40:"icons/mimetypes/large/text-x-generic.png";s:4:"72dc";s:39:"icons/mimetypes/large/text-x-source.png";s:4:"d019";s:35:"icons/mimetypes/large/user-home.png";s:4:"2698";s:41:"icons/mimetypes/large/video-x-generic.png";s:4:"6d8e";s:43:"icons/mimetypes/large/x-office-document.png";s:4:"0b42";s:46:"icons/mimetypes/large/x-office-spreadsheet.png";s:4:"75c4";s:31:"icons/mimetypes/small/Thumbs.db";s:4:"61e5";s:49:"icons/mimetypes/small/application-certificate.png";s:4:"a694";s:50:"icons/mimetypes/small/application-x-executable.png";s:4:"b9f4";s:47:"icons/mimetypes/small/applications-graphics.png";s:4:"2f98";s:42:"icons/mimetypes/small/audio-volume-low.png";s:4:"9156";s:41:"icons/mimetypes/small/audio-x-generic.png";s:4:"96ef";s:39:"icons/mimetypes/small/dialog-cancel.png";s:4:"c1f1";s:35:"icons/mimetypes/small/dialog-ok.png";s:4:"fa03";s:38:"icons/mimetypes/small/document-new.png";s:4:"644e";s:36:"icons/mimetypes/small/emblem-web.png";s:4:"a2d8";s:31:"icons/mimetypes/small/empty.png";s:4:"e485";s:36:"icons/mimetypes/small/folder-new.png";s:4:"b57e";s:37:"icons/mimetypes/small/folder-open.png";s:4:"2aee";s:40:"icons/mimetypes/small/font-x-generic.png";s:4:"d052";s:45:"icons/mimetypes/small/graphics-svg-editor.png";s:4:"d164";s:30:"icons/mimetypes/small/home.png";s:4:"ed62";s:41:"icons/mimetypes/small/image-x-generic.png";s:4:"ed39";s:45:"icons/mimetypes/small/internet-group-chat.png";s:4:"f5e7";s:38:"icons/mimetypes/small/media-floppy.png";s:4:"96b1";s:39:"icons/mimetypes/small/misc-cd-image.png";s:4:"5070";s:43:"icons/mimetypes/small/package-x-generic.png";s:4:"0630";s:45:"icons/mimetypes/small/system-file-manager.png";s:4:"84cf";s:35:"icons/mimetypes/small/text-html.png";s:4:"1791";s:40:"icons/mimetypes/small/text-x-generic.png";s:4:"f9e3";s:39:"icons/mimetypes/small/text-x-source.png";s:4:"deb2";s:41:"icons/mimetypes/small/user-trash-full.png";s:4:"530b";s:41:"icons/mimetypes/small/video-x-generic.png";s:4:"fc74";s:47:"icons/mimetypes/small/x-office-address-book.png";s:4:"35b8";s:43:"icons/mimetypes/small/x-office-document.png";s:4:"4f7e";s:46:"icons/mimetypes/small/x-office-spreadsheet.png";s:4:"06ad";s:42:"icons/mimetypes/medium/audio-x-generic.png";s:4:"d463";s:36:"icons/mimetypes/medium/dialog-ok.png";s:4:"5d77";s:39:"icons/mimetypes/medium/document-new.png";s:4:"af33";s:32:"icons/mimetypes/medium/empty.png";s:4:"bbba";s:38:"icons/mimetypes/medium/folder-open.png";s:4:"42a7";s:41:"icons/mimetypes/medium/font-x-generic.png";s:4:"2e5e";s:46:"icons/mimetypes/medium/format-justify-left.png";s:4:"6008";s:46:"icons/mimetypes/medium/graphics-svg-editor.png";s:4:"35ec";s:31:"icons/mimetypes/medium/home.png";s:4:"7bd4";s:42:"icons/mimetypes/medium/image-x-generic.png";s:4:"2ea7";s:39:"icons/mimetypes/medium/media-floppy.png";s:4:"848b";s:44:"icons/mimetypes/medium/package-x-generic.png";s:4:"df48";s:36:"icons/mimetypes/medium/text-html.png";s:4:"788a";s:41:"icons/mimetypes/medium/text-x-generic.png";s:4:"6008";s:42:"icons/mimetypes/medium/video-x-generic.png";s:4:"1251";s:44:"icons/mimetypes/medium/x-office-document.png";s:4:"f412";s:47:"icons/mimetypes/medium/x-office-spreadsheet.png";s:4:"ffd7";}',
);

?>