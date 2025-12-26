-- 用户管理系统数据库结构

-- 用户表
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(100) NOT NULL COMMENT '微信openid',
  `unionid` varchar(100) DEFAULT NULL COMMENT '微信unionid',
  `nickname` varchar(100) DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `role` tinyint(1) NOT NULL DEFAULT '1' COMMENT '角色：1-普通用户 2-店铺管理 3-团队成员 4-市代理 5-总管理员',
  `parent_id` int(11) DEFAULT '0' COMMENT '上级ID',
  `shop_id` int(11) DEFAULT '0' COMMENT '所属店铺ID',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0-禁用 1-启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid` (`openid`),
  KEY `role` (`role`),
  KEY `parent_id` (`parent_id`),
  KEY `shop_id` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- 店铺表
CREATE TABLE `shops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '店铺名称',
  `address` varchar(255) DEFAULT NULL COMMENT '店铺地址',
  `contact_phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `manager_id` int(11) NOT NULL COMMENT '管理员ID',
  `city_agent_id` int(11) NOT NULL COMMENT '市代理ID',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0-禁用 1-启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `manager_id` (`manager_id`),
  KEY `city_agent_id` (`city_agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='店铺表';

-- WIFI信息表
CREATE TABLE `wifi_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL COMMENT '店铺ID',
  `ssid` varchar(100) NOT NULL COMMENT 'WIFI名称',
  `password` varchar(100) NOT NULL COMMENT 'WIFI密码',
  `encryption_type` varchar(20) DEFAULT 'WPA' COMMENT '加密类型：WPA/WEP/None',
  `is_hidden` tinyint(1) DEFAULT '0' COMMENT '是否隐藏：0-否 1-是',
  `qr_code_url` varchar(255) DEFAULT NULL COMMENT '小程序码URL',
  `scan_count` int(11) DEFAULT '0' COMMENT '扫码次数',
  `created_by` int(11) NOT NULL COMMENT '创建人ID',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0-禁用 1-启用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='WIFI信息表';

-- 扫码记录表
CREATE TABLE `scan_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wifi_id` int(11) NOT NULL COMMENT 'WIFI ID',
  `user_id` int(11) NOT NULL COMMENT '扫码用户ID',
  `scan_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '扫码时间',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP地址',
  `user_agent` varchar(255) DEFAULT NULL COMMENT '用户代理',
  PRIMARY KEY (`id`),
  KEY `wifi_id` (`wifi_id`),
  KEY `user_id` (`user_id`),
  KEY `scan_time` (`scan_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='扫码记录表';

-- 权限表
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '权限名称',
  `code` varchar(50) NOT NULL COMMENT '权限代码',
  `description` varchar(255) DEFAULT NULL COMMENT '权限描述',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限表';

-- 角色权限关联表
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` tinyint(1) NOT NULL COMMENT '角色',
  `permission_code` varchar(50) NOT NULL COMMENT '权限代码',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission` (`role`,`permission_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色权限关联表';

-- 插入初始权限数据
INSERT INTO `permissions` (`name`, `code`, `description`) VALUES
('用户管理', 'user_manage', '管理用户信息'),
('店铺管理', 'shop_manage', '管理店铺信息'),
('WIFI管理', 'wifi_manage', '管理WIFI信息'),
('数据统计', 'data_statistics', '查看数据统计'),
('系统设置', 'system_setting', '系统参数设置');

-- 插入角色权限关联数据
INSERT INTO `role_permissions` (`role`, `permission_code`) VALUES
(5, 'user_manage'),(5, 'shop_manage'),(5, 'wifi_manage'),(5, 'data_statistics'),(5, 'system_setting'),
(4, 'user_manage'),(4, 'shop_manage'),(4, 'wifi_manage'),(4, 'data_statistics'),
(3, 'shop_manage'),(3, 'wifi_manage'),
(2, 'wifi_manage');