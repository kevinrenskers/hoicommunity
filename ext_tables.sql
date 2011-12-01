#
# Table structure for table 'tx_hoicommunity_messages'
#
CREATE TABLE tx_hoicommunity_messages (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	folder_uid blob NOT NULL,
	subject varchar(255) DEFAULT '' NOT NULL,
	body text NOT NULL,
	been_read tinyint(3) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY been_read (been_read)
);



#
# Table structure for table 'tx_hoicommunity_folders'
#
CREATE TABLE tx_hoicommunity_folders (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
	is_inbox tinyint(3) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY cruser_id (cruser_id),
	KEY is_inbox (is_inbox)
);



#
# Table structure for table 'tx_hoicommunity_buddylist'
#
CREATE TABLE tx_hoicommunity_buddylist (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	fe_user_uid blob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY cruser_id (cruser_id)
);



#
# Table structure for table 'tx_hoicommunity_ignorelist'
#
CREATE TABLE tx_hoicommunity_ignorelist (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	fe_user_uid blob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY cruser_id (cruser_id)
);



#
# Table structure for table 'tx_hoicommunity_emoticons'
#
CREATE TABLE tx_hoicommunity_emoticons (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	string varchar(10) DEFAULT '' NOT NULL,
	file blob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_hoicommunity_log'
#
CREATE TABLE tx_hoicommunity_log (
	uid int(11) NOT NULL auto_increment,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	ip varchar(255) DEFAULT '' NOT NULL,
	nr int(11) DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid),
  KEY cruser_id (cruser_id),
  KEY ip (ip)
);



#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	first_name varchar(50) DEFAULT '' NOT NULL,
	last_name varchar(50) DEFAULT '' NOT NULL,
	tx_hoicommunity_birthday int(11) DEFAULT '0' NOT NULL,
	tx_hoicommunity_information text NOT NULL,
	tx_hoicommunity_activation varchar(20) DEFAULT '' NOT NULL,

	KEY tx_hoicommunity_activation (tx_hoicommunity_activation)
);