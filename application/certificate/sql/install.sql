/*
 sql安装文件
*/

# Dump of table db_example_category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `db_certificate`;

CREATE TABLE `db_certificate` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '证明人',
  `community_name` varchar(50) NOT NULL COMMENT '社区名称',
  `present_address` varchar(50) NOT NULL COMMENT '居住地址',
  `residence_address` varchar(50) NOT NULL COMMENT '户籍地址',
  `use` varchar(100) NOT NULL COMMENT '使用',
  `basis` varchar(100) NOT NULL COMMENT '依据',
  `title` varchar(50) NOT NULL COMMENT '抬头',
  `images` varchar(255) NOT NULL DEFAULT '',
  `charge_name` varchar(20) NOT NULL DEFAULT '',
  `number` varchar(20) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name_charge_number_create` (`name`,`charge_name`,`number`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

