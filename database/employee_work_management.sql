-- ============================================================================
-- Employee Work Management - MySQL schema (MySQL 8.0+ / InnoDB / utf8mb4)
--
-- This file mirrors the Laravel migrations in database/migrations/ exactly.
-- Import it ONCE for a fresh install, OR create the schema with migrations.
-- Do not do both: the migrations table below is pre-populated so that
-- `php artisan migrate` afterwards reports "Nothing to migrate".
--
-- Contains NO user accounts and NO credentials. Create the first administrator
-- with:  php artisan app:create-admin
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `employee_work_management`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `employee_work_management`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- Migration bookkeeping (keeps Laravel's migrate command in sync)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
  (1, '0001_01_01_000000_create_users_table', 1),
  (2, '0001_01_01_000001_create_sessions_table', 1),
  (3, '2026_10_12_000001_create_tasks_table', 1),
  (4, '2026_10_12_000002_create_daily_work_updates_table', 1);

-- ----------------------------------------------------------------------------
-- users
--   role        : admin | manager | employee
--   manager_id  : reporting manager (self-reference). Deleting a manager
--                 releases their team (SET NULL) rather than deleting it.
--   is_active   : deactivated accounts cannot sign in.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','employee') NOT NULL DEFAULT 'employee',
  `manager_id` bigint unsigned DEFAULT NULL,
  `job_title` varchar(120) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_is_active_index` (`role`, `is_active`),
  KEY `users_manager_id_foreign` (`manager_id`),
  CONSTRAINT `users_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- password_reset_tokens (Laravel auth scaffolding)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- sessions (used because SESSION_DRIVER=database)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- tasks
--   assigned_to : employee who owns the task
--   created_by  : admin or manager who allocated it
--   Overdue is derived at query time: due_date < today AND status <> 'completed'.
--   Users referenced here are protected from hard deletion (RESTRICT).
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text,
  `start_date` date NOT NULL,
  `due_date` date NOT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('todo','in_progress','completed') NOT NULL DEFAULT 'todo',
  `assigned_to` bigint unsigned NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_status_index` (`status`),
  KEY `tasks_due_date_index` (`due_date`),
  KEY `tasks_assigned_to_status_index` (`assigned_to`, `status`),
  KEY `tasks_created_by_foreign` (`created_by`),
  CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- daily_work_updates
--   One update per employee, task and work date (daily_updates_unique_per_day).
--   created_at is the submission timestamp.
--   Deleting a task removes its updates (CASCADE); employees cannot be deleted
--   while they have updates (RESTRICT).
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `daily_work_updates`;
CREATE TABLE `daily_work_updates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `work_date` date NOT NULL,
  `completed_work` text NOT NULL,
  `todays_progress` text NOT NULL,
  `pending_work` text,
  `blockers` text,
  `progress_percentage` tinyint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_updates_unique_per_day` (`task_id`, `employee_id`, `work_date`),
  KEY `daily_work_updates_employee_id_work_date_index` (`employee_id`, `work_date`),
  KEY `daily_work_updates_work_date_index` (`work_date`),
  KEY `daily_work_updates_employee_id_foreign` (`employee_id`),
  CONSTRAINT `daily_work_updates_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_work_updates_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
