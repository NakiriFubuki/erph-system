-- ERPH1 数据库初始化（仅创建数据库，表结构请用 erph.sql 导入）
-- 方法一：在 MySQL 中执行本文件后，再执行: USE erph1; 然后导入 erph.sql
-- 方法二：phpMyAdmin 中新建数据库 erph1，再导入 erph.sql

CREATE DATABASE IF NOT EXISTS erph1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE erph1;

-- 表结构及初始数据请使用同目录下的 erph.sql 导入到 erph1 数据库中。
