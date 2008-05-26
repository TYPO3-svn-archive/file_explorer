#
# Table structure for table 'tx_fileexplorer_files'
#
CREATE TABLE tx_fileexplorer_files (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	description text NOT NULL,
	feCrUserId varchar(255) DEFAULT '' NOT NULL,
	`file` blob NOT NULL,
	file_info varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_fileexplorer_read varchar(255) DEFAULT '' NOT NULL,
	tx_fileexplorer_write varchar(255) DEFAULT '' NOT NULL,
	tx_fileexplorer_feCrUserId varchar(255) DEFAULT '' NOT NULL,
	tx_fileexplorer_readPublic int(11) DEFAULT '0' NOT NULL
);