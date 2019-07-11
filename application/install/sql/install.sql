

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `committee_system_config`
-- ----------------------------
DROP TABLE IF EXISTS `committee_system_config`;
CREATE TABLE `committee_system_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统配置(1是，0否)',
  `group` varchar(20) NOT NULL DEFAULT 'base' COMMENT '分组',
  `title` varchar(20) NOT NULL COMMENT '配置标题',
  `name` varchar(50) NOT NULL COMMENT '配置名称，由英文字母和下划线组成',
  `value` text NOT NULL COMMENT '配置值',
  `type` varchar(20) NOT NULL DEFAULT 'input' COMMENT '配置类型()',
  `options` text NOT NULL COMMENT '配置项(选项名:选项值)',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '文件上传接口',
  `tips` varchar(255) NOT NULL COMMENT '配置提示',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='[系统] 系统配置';

-- ----------------------------
-- Records of hisi_system_config
-- ----------------------------
INSERT INTO `committee_system_config` VALUES ('1', '1', 'upload', '图片上传张数限制', 'upload_image_size', '5', 'input', '', '', '', '1', '1', '1490841797', '1490841797');
INSERT INTO `committee_system_config` VALUES ('2', '1', 'upload', '图片上传大小限制', 'upload_image_size', '0', 'input', '', '', '单位：KB，0表示不限制大小', '3', '1', '1490841797', '1491040778');
INSERT INTO `committee_system_config` VALUES ('3', '1', 'upload', '允许上传图片格式', 'upload_image_ext', 'jpg,png,gif,jpeg', 'input', '', '', '多个格式请用英文逗号（,）隔开', '4', '1', '1490842130', '1491040778');
INSERT INTO `committee_system_config` VALUES ('4', '1', 'sys', '开发模式', 'app_debug', '1', 'switch', '0:关闭\r\n1:开启', '', '&lt;strong class=&quot;red&quot;&gt;生产环境下一定要关闭此配置&lt;/strong&gt;', '3', '1', '1491005004', '1492093874');
INSERT INTO `committee_system_config` VALUES ('5', '1', 'databases', '备份目录', 'backup_path', './backup/database/', 'input', '', '', '数据库备份路径,路径必须以 / 结尾', '0', '1', '1491881854', '1491965974');
INSERT INTO `committee_system_config` VALUES ('6', '1', 'databases', '备份分卷大小', 'part_size', '20971520', 'input', '', '', '用于限制压缩后的分卷最大长度。单位：B；建议设置20M', '0', '1', '1491881975', '1491965974');
INSERT INTO `committee_system_config` VALUES ('7', '1', 'databases', '备份压缩开关', 'compress', '1', 'switch', '0:关闭\r\n1:开启', '', '压缩备份文件需要PHP环境支持gzopen,gzwrite函数', '0', '1', '1491882038', '1491965974');
INSERT INTO `committee_system_config` VALUES ('8', '1', 'databases', '备份压缩级别', 'compress_level', '4', 'radio', '1:最低\r\n4:一般\r\n9:最高', '', '数据库备份文件的压缩级别，该配置在开启压缩时生效', '0', '1', '1491882154', '1491965974');
INSERT INTO `committee_system_config` VALUES ('9', '1', 'base', '网站标题', 'site_title', '居委会管理系统', 'input', '', '', '网站标题是体现一个网站的主旨，要做到主题突出、标题简洁、连贯等特点，建议不超过28个字', '6', '1', '1492502354', '1494695131');
INSERT INTO `committee_system_config` VALUES ('10', '1', 'base', 'ICP备案信息', 'site_icp', '1111', 'input', '', '', '请填写ICP备案号，用于展示在网站底部，ICP备案官网：&lt;a href=&quot;http://www.miibeian.gov.cn&quot; target=&quot;_blank&quot;&gt;http://www.miibeian.gov.cn&lt;/a&gt;', '9', '1', '1494691721', '1494692046');
INSERT INTO `committee_system_config` VALUES ('11', '1', 'sys', '系统日志保留', 'system_log_retention', '30', 'input', '', '', '单位天，系统将自动清除 ? 天前的系统日志', '8', '1', '1542013958', '1542014158');

-- ----------------------------
-- Table structure for `committee_system_log`
-- ----------------------------
DROP TABLE IF EXISTS `committee_system_log`;
CREATE TABLE `committee_system_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) DEFAULT '',
  `url` varchar(200) DEFAULT '',
  `param` text,
  `remark` varchar(255) DEFAULT '',
  `count` int(10) unsigned NOT NULL DEFAULT '1',
  `ip` varchar(128) DEFAULT '',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统] 操作日志';

-- ----------------------------
-- Records of hisi_system_log
-- ----------------------------

-- ----------------------------
-- Table structure for `committee_system_menu`
-- ----------------------------
DROP TABLE IF EXISTS `committee_system_menu`;
CREATE TABLE `committee_system_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID(快捷菜单专用)',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `module` varchar(20) NOT NULL COMMENT '模块名或插件名，插件名格式:plugins.插件名',
  `title` varchar(20) NOT NULL COMMENT '菜单标题',
  `icon` varchar(80) NOT NULL DEFAULT 'aicon ai-shezhi' COMMENT '菜单图标',
  `url` varchar(200) NOT NULL COMMENT '链接地址(模块/控制器/方法)',
  `param` varchar(200) NOT NULL DEFAULT '' COMMENT '扩展参数',
  `target` varchar(20) NOT NULL DEFAULT '_self' COMMENT '打开方式(_blank,_self)',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `debug` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '开发模式可见',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统菜单，系统菜单不可删除',
  `nav` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否为菜单显示，1显示0不显示',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态1显示，0隐藏',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 管理菜单';

-- ----------------------------
-- Records of committee_system_menu
-- ----------------------------
INSERT INTO `committee_system_menu` VALUES ('1', '0', '0', 'system', '首页', '', 'system/index', '', '_self', '0', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('2', '0', '0', 'system', '系统', '', 'system/system', '', '_self', '1', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('3', '0', '0', 'system', '工作区', 'aicon ai-shezhi', 'system/workspace', '', '_self', '2', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('4', '0', '1', 'system', '后台首页', 'aicon ai-caidan', 'system/index', '', '_self', '0', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('6', '0', '2', 'system', '系统基础', 'aicon ai-gongneng', 'system/system', '', '_self', '1', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('8', '0', '2', 'system', '系统扩展', 'aicon ai-shezhi', 'system/extend', '', '_self', '3', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('10', '0', '6', 'system', '系统设置', 'aicon ai-icon01', 'system/system/index', '', '_self', '1', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('12', '0', '6', 'system', '系统菜单', 'aicon ai-systemmenu', 'system/menu/index', '', '_self', '3', '1', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('13', '0', '6', 'system', '管理员角色', '', 'system/user/role', '', '_self', '4', '0', '1', '0', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('14', '0', '6', 'system', '系统管理员', 'aicon ai-tubiao05', 'system/user/index', '', '_self', '5', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('15', '0', '6', 'system', '系统日志', 'aicon ai-xitongrizhi-tiaoshi', 'system/log/index', '', '_self', '7', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('17', '0', '8', 'system', '本地模块', 'aicon ai-mokuaiguanli1', 'system/module/index', '', '_self', '1', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('24', '0', '4', 'system', '首页', '', 'system/index/index', '', '_self', '100', '0', '1', '0', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('25', '0', '4', 'system', '清空缓存', '', 'system/index/clear', '', '_self', '2', '0', '1', '0', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('26', '0', '12', 'system', '添加菜单', '', 'system/menu/add', '', '_self', '1', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('27', '0', '12', 'system', '修改菜单', '', 'system/menu/edit', '', '_self', '2', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('28', '0', '12', 'system', '删除菜单', '', 'system/menu/del', '', '_self', '3', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('29', '0', '12', 'system', '状态设置', '', 'system/menu/status', '', '_self', '4', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('30', '0', '12', 'system', '排序设置', '', 'system/menu/sort', '', '_self', '5', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('31', '0', '12', 'system', '添加快捷菜单', '', 'system/menu/quick', '', '_self', '6', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('32', '0', '12', 'system', '导出菜单', '', 'system/menu/export', '', '_self', '7', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('33', '0', '13', 'system', '添加角色', '', 'system/user/addrole', '', '_self', '1', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('34', '0', '13', 'system', '修改角色', '', 'system/user/editrole', '', '_self', '2', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('35', '0', '13', 'system', '删除角色', '', 'system/user/delrole', '', '_self', '3', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('36', '0', '13', 'system', '状态设置', '', 'system/user/statusRole', '', '_self', '4', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('37', '0', '14', 'system', '添加管理员', '', 'system/user/adduser', '', '_self', '1', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('38', '0', '14', 'system', '修改管理员', '', 'system/user/edituser', '', '_self', '2', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('39', '0', '14', 'system', '删除管理员', '', 'system/user/deluser', '', '_self', '3', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('40', '0', '14', 'system', '状态设置', '', 'system/user/status', '', '_self', '4', '0', '1', '0', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('41', '0', '4', 'system', '个人信息设置', '', 'system/user/info', '', '_self', '5', '0', '1', '0', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('60', '0', '10', 'system', '基础配置', '', 'system/system/index', 'group=base', '_self', '1', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('61', '0', '10', 'system', '系统配置', '', 'system/system/index', 'group=sys', '_self', '2', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('62', '0', '10', 'system', '上传配置', '', 'system/system/index', 'group=upload', '_self', '3', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('63', '0', '10', 'system', '开发配置', '', 'system/system/index', 'group=develop', '_self', '4', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('64', '0', '17', 'system', '生成模块', '', 'system/module/design', '', '_self', '6', '1', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('65', '0', '17', 'system', '安装模块', '', 'system/module/install', '', '_self', '1', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('66', '0', '17', 'system', '卸载模块', '', 'system/module/uninstall', '', '_self', '2', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('67', '0', '17', 'system', '状态设置', '', 'system/module/status', '', '_self', '3', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('68', '0', '17', 'system', '设置默认模块', '', 'system/module/setdefault', '', '_self', '4', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('69', '0', '17', 'system', '删除模块', '', 'system/module/del', '', '_self', '5', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('80', '0', '8', 'system', '框架升级', 'aicon ai-iconfontshengji', 'system/upgrade/index', '', '_self', '4', '0', '1', '1', '1', '1491352728');
INSERT INTO `committee_system_menu` VALUES ('81', '0', '80', 'system', '获取升级列表', '', 'system/upgrade/lists', '', '_self', '0', '0', '1', '1', '1', '1491353504');
INSERT INTO `committee_system_menu` VALUES ('82', '0', '80', 'system', '安装升级包', '', 'system/upgrade/install', '', '_self', '0', '0', '1', '1', '1', '1491353568');
INSERT INTO `committee_system_menu` VALUES ('83', '0', '80', 'system', '下载升级包', '', 'system/upgrade/download', '', '_self', '0', '0', '1', '1', '1', '1491395830');
INSERT INTO `committee_system_menu` VALUES ('84', '0', '6', 'system', '数据库管理', 'aicon ai-shujukuguanli', 'system/database/index', '', '_self', '6', '0', '1', '1', '1', '1491461136');
INSERT INTO `committee_system_menu` VALUES ('85', '0', '84', 'system', '备份数据库', '', 'system/database/export', '', '_self', '0', '0', '1', '1', '1', '1491461250');
INSERT INTO `committee_system_menu` VALUES ('86', '0', '84', 'system', '恢复数据库', '', 'system/database/import', '', '_self', '0', '0', '1', '1', '1', '1491461315');
INSERT INTO `committee_system_menu` VALUES ('87', '0', '84', 'system', '优化数据库', '', 'system/database/optimize', '', '_self', '0', '0', '1', '1', '1', '1491467000');
INSERT INTO `committee_system_menu` VALUES ('88', '0', '84', 'system', '删除备份', '', 'system/database/del', '', '_self', '0', '0', '1', '1', '1', '1491467058');
INSERT INTO `committee_system_menu` VALUES ('89', '0', '84', 'system', '修复数据库', '', 'system/database/repair', '', '_self', '0', '0', '1', '1', '1', '1491880879');
INSERT INTO `committee_system_menu` VALUES ('90', '0', '21', 'system', '设置默认等级', '', 'system/member/setdefault', '', '_self', '0', '0', '1', '1', '1', '1491966585');
INSERT INTO `committee_system_menu` VALUES ('91', '0', '10', 'system', '数据库配置', '', 'system/system/index', 'group=databases', '_self', '5', '0', '1', '0', '1', '1492072213');
INSERT INTO `committee_system_menu` VALUES ('107', '0', '15', 'system', '删除日志', '', 'system/log/del', 'table=admin_log', '_self', '100', '0', '1', '0', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('108', '0', '15', 'system', '清空日志', '', 'system/log/clear', '', '_self', '100', '0', '1', '0', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('109', '0', '17', 'system', '编辑模块', '', 'system/module/edit', '', '_self', '100', '0', '1', '0', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('110', '0', '17', 'system', '模块图标上传', '', 'system/module/icon', '', '_self', '100', '0', '1', '0', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('114', '0', '8', 'system', '应用市场', 'aicon ai-app-store', 'system/cloud/index', '', '_self', '0', '0', '1', '1', '1', '1490315067');
INSERT INTO `committee_system_menu` VALUES ('115', '0', '114', 'system', '安装应用', '', 'system/cloud/install', '', '_self', '0', '0', '1', '1', '1', '1490315067');


-- ----------------------------
-- Table structure for `committee_system_module`
-- ----------------------------
DROP TABLE IF EXISTS `committee_system_module`;
CREATE TABLE `committee_system_module` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '系统模块',
  `name` varchar(50) NOT NULL COMMENT '模块名(英文)',
  `identifier` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL COMMENT '模块标题',
  `intro` varchar(255) NOT NULL COMMENT '模块简介',
  `icon` varchar(80) NOT NULL DEFAULT 'aicon ai-mokuaiguanli' COMMENT '图标',
  `version` varchar(20) NOT NULL COMMENT '版本号',
  `sort` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `config` text NOT NULL COMMENT '配置',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `default` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='[系统] 模块';

-- ----------------------------
-- Records of hisi_system_module
-- ----------------------------
INSERT INTO `committee_system_module` VALUES ('1', '1', 'system', 'system', '系统管理模块', '系统核心模块，用于后台各项管理功能模块及功能拓展', '', '1.0.0', '0', '1', '', '1489998096', '1489998096', null);
INSERT INTO `committee_system_module` VALUES ('2', '1', 'index', 'system', '默认模块', '推荐使用扩展模块作为默认首页。', '', '1.0.0', '0', '1', '', '1489998096', '1489998096', null);
INSERT INTO `committee_system_module` VALUES ('3', '1', 'install', 'system', '系统安装模块', '系统安装模块，勿动。', '', '1.0.0', '0', '1', '', '1489998096', '1489998096', null);

-- ----------------------------
-- Table structure for `committee_system_role`
-- ----------------------------
DROP TABLE IF EXISTS `committee_system_role`;
CREATE TABLE `committee_system_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `intro` varchar(200) NOT NULL COMMENT '角色简介',
  `auth` text NOT NULL COMMENT '角色权限',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='[系统] 管理角色';

-- ----------------------------
-- Records of hisi_system_role
-- ----------------------------
INSERT INTO `committee_system_role` VALUES ('1', '超级管理员', '拥有系统最高权限', '0', '1489411760', '0', '1');

-- ----------------------------
-- Table structure for `committee_system_user`
-- ----------------------------
DROP TABLE IF EXISTS `committee_system_user`;
CREATE TABLE `committee_system_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '角色ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(64) NOT NULL,
  `nick` varchar(50) NOT NULL COMMENT '昵称',
  `mobile` varchar(11) NOT NULL,
  `email` varchar(50) NOT NULL COMMENT '邮箱',
  `auth` text NOT NULL COMMENT '权限',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `last_login_ip` varchar(128) NOT NULL COMMENT '最后登陆IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆时间',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统] 管理用户';

-- ----------------------------
-- Records of committee_system_user
-- ----------------------------