<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL/MariaDB DDL based on the supplied campus_equipment_system export.
        DB::unprepared(<<<'SQL'
CREATE TABLE `borrowing_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `borrowing_request_id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `issued_by` bigint(20) UNSIGNED DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `borrow_date` datetime DEFAULT NULL,
  `expected_return_date` datetime NOT NULL,
  `actual_return_date` datetime DEFAULT NULL,
  `return_condition` enum('good','minor_damage','major_damage','lost') DEFAULT NULL,
  `status` enum('approved','issued','returned','overdue','lost','cancelled') NOT NULL DEFAULT 'approved',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `borrowing_requests` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  `status` enum('pending','approved','rejected','issued','partially_returned','returned','cancelled') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `category_name` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `damage_reports` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `reported_by` bigint(20) UNSIGNED NOT NULL,
  `borrowing_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `incident_type` enum('minor_damage','major_damage','lost') NOT NULL,
  `description` text NOT NULL,
  `evidence_url` varchar(1000) DEFAULT NULL,
  `assessment_amount` decimal(10,2) DEFAULT NULL,
  `assessed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `assessed_at` datetime DEFAULT NULL,
  `report_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('reported','under_assessment','assessed','resolved','closed') NOT NULL DEFAULT 'reported',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `equipment` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `asset_tag` varchar(40) NOT NULL,
  `equipment_name` varchar(100) NOT NULL,
  `brand` varchar(60) DEFAULT NULL,
  `model` varchar(80) DEFAULT NULL,
  `serial_number` varchar(80) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(10,2) DEFAULT NULL,
  `equipment_condition` enum('good','minor_damage','major_damage') NOT NULL DEFAULT 'good',
  `status` enum('available','borrowed','under_maintenance','lost','retired') NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `feedback` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `feedback_type` enum('equipment','system','service','other') NOT NULL,
  `rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `comments` text NOT NULL,
  `status` enum('new','reviewed','closed') NOT NULL DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `fines` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `borrowing_item_id` bigint(20) UNSIGNED NOT NULL,
  `fine_type` enum('overdue','damage','lost') NOT NULL,
  `fine_description` text DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT NULL,
  `assessment_status` enum('not_required','pending','assessed') NOT NULL DEFAULT 'not_required',
  `payment_status` enum('unpaid','partially_paid','paid') NOT NULL DEFAULT 'unpaid',
  `issued_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `fine_payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `fine_id` bigint(20) UNSIGNED NOT NULL,
  `payment_reference` varchar(50) NOT NULL,
  `stripe_payment_intent_id` varchar(100) DEFAULT NULL,
  `stripe_checkout_session_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'MYR',
  `payment_status` enum('pending','successful','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `inventory_movements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `from_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_location_id` bigint(20) UNSIGNED NOT NULL,
  `moved_by` bigint(20) UNSIGNED NOT NULL,
  `movement_date` datetime NOT NULL DEFAULT current_timestamp(),
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `locations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `location_name` varchar(100) NOT NULL,
  `building` varchar(80) DEFAULT NULL,
  `room_number` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `maintenance_records` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `damage_report_id` bigint(20) UNSIGNED DEFAULT NULL,
  `maintenance_schedule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `maintenance_type` enum('preventive','corrective') NOT NULL,
  `maintenance_description` text NOT NULL,
  `scheduled_date` datetime DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `completion_date` datetime DEFAULT NULL,
  `status` enum('reported','in_repair','repaired','cannot_repair','cancelled') NOT NULL DEFAULT 'reported',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `maintenance_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `schedule_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `interval_months` int(10) UNSIGNED NOT NULL,
  `last_maintenance_date` date DEFAULT NULL,
  `next_maintenance_date` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(40) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `repair_records` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `maintenance_record_id` bigint(20) UNSIGNED NOT NULL,
  `performed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `repair_description` text NOT NULL,
  `repair_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `start_date` datetime DEFAULT NULL,
  `completion_date` datetime DEFAULT NULL,
  `repair_outcome` enum('successful','unsuccessful') DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `ticket_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `ticket_reply_id` bigint(20) UNSIGNED DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `ticket_replies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `name` varchar(100) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `role` enum('student','staff','admin') NOT NULL DEFAULT 'student',
  `student_staff_id` varchar(30) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `account_status` enum('active','suspended','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `violation_records` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `borrowing_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `violation_type` enum('overdue','damage','lost','other') NOT NULL,
  `description` text NOT NULL,
  `violation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','resolved','closed') NOT NULL DEFAULT 'pending',
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE `id_sequences` (
  `sequence_name` varchar(64) NOT NULL,
  `next_value` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`sequence_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);



















        DB::unprepared(<<<'SQL'
ALTER TABLE `borrowing_items` ADD UNIQUE KEY `uq_borrowing_request_equipment` (`borrowing_request_id`,`equipment_id`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_borrowing_items_equipment` (`equipment_id`),
  ADD KEY `idx_borrowing_items_issued_by` (`issued_by`),
  ADD KEY `idx_borrowing_items_received_by` (`received_by`),
  ADD KEY `idx_borrowing_items_status_return` (`status`,`expected_return_date`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `borrowing_requests` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_borrowing_requests_user` (`user_id`),
  ADD KEY `idx_borrowing_requests_approver` (`approved_by`),
  ADD KEY `idx_borrowing_requests_status_date` (`status`,`request_date`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `categories` ADD UNIQUE KEY `uq_categories_name` (`category_name`),
  ADD UNIQUE KEY `public_id` (`public_id`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `damage_reports` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_damage_reports_equipment` (`equipment_id`),
  ADD KEY `idx_damage_reports_reporter` (`reported_by`),
  ADD KEY `idx_damage_reports_borrowing_item` (`borrowing_item_id`),
  ADD KEY `idx_damage_reports_assessed_by` (`assessed_by`),
  ADD KEY `idx_damage_reports_status` (`status`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `equipment` ADD UNIQUE KEY `uq_equipment_asset_tag` (`asset_tag`),
  ADD UNIQUE KEY `uq_equipment_serial_number` (`serial_number`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_equipment_category` (`category_id`),
  ADD KEY `idx_equipment_location` (`location_id`),
  ADD KEY `idx_equipment_status` (`status`)
SQL);


        DB::unprepared(<<<'SQL'
CREATE TABLE `equipment_status_histories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `old_status` varchar(40) DEFAULT NULL,
  `new_status` varchar(40) NOT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_equipment_status_histories_equipment_date` (`equipment_id`,`changed_at`),
  KEY `idx_equipment_status_histories_changed_by` (`changed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `feedback` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_feedback_user` (`user_id`),
  ADD KEY `idx_feedback_equipment` (`equipment_id`),
  ADD KEY `idx_feedback_status` (`status`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `fines` ADD UNIQUE KEY `uq_fines_item_type` (`borrowing_item_id`,`fine_type`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_fines_payment_status` (`payment_status`),
  ADD KEY `idx_fines_assessment_status` (`assessment_status`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `fine_payments` ADD UNIQUE KEY `uq_fine_payments_reference` (`payment_reference`),
  ADD UNIQUE KEY `uq_fine_payments_intent` (`stripe_payment_intent_id`),
  ADD UNIQUE KEY `uq_fine_payments_session` (`stripe_checkout_session_id`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_fine_payments_fine_status` (`fine_id`,`payment_status`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `inventory_movements` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_inventory_movements_equipment_date` (`equipment_id`,`movement_date`),
  ADD KEY `idx_inventory_movements_from_location` (`from_location_id`),
  ADD KEY `idx_inventory_movements_to_location` (`to_location_id`),
  ADD KEY `idx_inventory_movements_moved_by` (`moved_by`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `locations` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_locations_name` (`location_name`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `maintenance_records` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_maintenance_records_equipment` (`equipment_id`),
  ADD KEY `idx_maintenance_records_damage_report` (`damage_report_id`),
  ADD KEY `idx_maintenance_records_schedule` (`maintenance_schedule_id`),
  ADD KEY `idx_maintenance_records_requested_by` (`requested_by`),
  ADD KEY `idx_maintenance_records_assigned_to` (`assigned_to`),
  ADD KEY `idx_maintenance_records_status` (`status`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `maintenance_schedules` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_maintenance_schedules_equipment` (`equipment_id`),
  ADD KEY `idx_maintenance_schedules_creator` (`created_by`),
  ADD KEY `idx_maintenance_schedules_next_date` (`status`,`next_maintenance_date`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `notifications` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_notifications_user_read_date` (`user_id`,`is_read`,`created_at`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `repair_records` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_repair_records_maintenance` (`maintenance_record_id`),
  ADD KEY `idx_repair_records_performed_by` (`performed_by`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `tickets` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_tickets_user` (`user_id`),
  ADD KEY `idx_tickets_equipment` (`equipment_id`),
  ADD KEY `idx_tickets_assigned_to` (`assigned_to`),
  ADD KEY `idx_tickets_status_date` (`status`,`created_at`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `ticket_attachments` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_ticket_attachments_ticket` (`ticket_id`),
  ADD KEY `idx_ticket_attachments_reply` (`ticket_reply_id`),
  ADD KEY `idx_ticket_attachments_uploader` (`uploaded_by`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `ticket_replies` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_ticket_replies_ticket` (`ticket_id`),
  ADD KEY `idx_ticket_replies_user` (`user_id`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `users` ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD UNIQUE KEY `uq_users_student_staff_id` (`student_staff_id`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_users_role_status` (`role`,`account_status`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `violation_records` ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `idx_violation_records_user` (`user_id`),
  ADD KEY `idx_violation_records_borrowing_item` (`borrowing_item_id`),
  ADD KEY `idx_violation_records_status` (`status`)
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `borrowing_items` ADD CONSTRAINT `fk_borrowing_items_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_borrowing_items_issued_by` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_borrowing_items_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_borrowing_items_request` FOREIGN KEY (`borrowing_request_id`) REFERENCES `borrowing_requests` (`id`) ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `borrowing_requests` ADD CONSTRAINT `fk_borrowing_requests_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_borrowing_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `damage_reports` ADD CONSTRAINT `fk_damage_reports_assessed_by` FOREIGN KEY (`assessed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_damage_reports_borrowing_item` FOREIGN KEY (`borrowing_item_id`) REFERENCES `borrowing_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_damage_reports_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_damage_reports_reported_by` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `equipment` ADD CONSTRAINT `fk_equipment_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_equipment_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON UPDATE CASCADE
SQL);


        DB::unprepared(<<<'SQL'
ALTER TABLE `equipment_status_histories` ADD CONSTRAINT `fk_equipment_status_histories_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `feedback` ADD CONSTRAINT `fk_feedback_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `fines` ADD CONSTRAINT `fk_fines_borrowing_item` FOREIGN KEY (`borrowing_item_id`) REFERENCES `borrowing_items` (`id`) ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `fine_payments` ADD CONSTRAINT `fk_fine_payments_fine` FOREIGN KEY (`fine_id`) REFERENCES `fines` (`id`) ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `inventory_movements` ADD CONSTRAINT `fk_inventory_movements_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inventory_movements_from_location` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inventory_movements_moved_by` FOREIGN KEY (`moved_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inventory_movements_to_location` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`id`) ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `maintenance_records` ADD CONSTRAINT `fk_maintenance_records_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_records_damage_report` FOREIGN KEY (`damage_report_id`) REFERENCES `damage_reports` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_records_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_records_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_records_schedule` FOREIGN KEY (`maintenance_schedule_id`) REFERENCES `maintenance_schedules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `maintenance_schedules` ADD CONSTRAINT `fk_maintenance_schedules_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_schedules_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `notifications` ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `repair_records` ADD CONSTRAINT `fk_repair_records_maintenance` FOREIGN KEY (`maintenance_record_id`) REFERENCES `maintenance_records` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_repair_records_performed_by` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `tickets` ADD CONSTRAINT `fk_tickets_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tickets_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `ticket_attachments` ADD CONSTRAINT `fk_ticket_attachments_reply` FOREIGN KEY (`ticket_reply_id`) REFERENCES `ticket_replies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ticket_attachments_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ticket_attachments_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `ticket_replies` ADD CONSTRAINT `fk_ticket_replies_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ticket_replies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `equipment_status_histories` ADD CONSTRAINT `fk_equipment_status_histories_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE `violation_records` ADD CONSTRAINT `fk_violation_records_borrowing_item` FOREIGN KEY (`borrowing_item_id`) REFERENCES `borrowing_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_violation_records_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
SQL);


        // Public ID triggers copied from the supplied MariaDB schema.
        // These run for Eloquent inserts AND handwritten DB::insert()/DB::statement() inserts.
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_borrowing_items_public_id` BEFORE INSERT ON `borrowing_items` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('borrowing_items', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('BI', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_borrowing_requests_public_id` BEFORE INSERT ON `borrowing_requests` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('borrowing_requests', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('BR', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_categories_public_id` BEFORE INSERT ON `categories` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('categories', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('CAT', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_damage_reports_public_id` BEFORE INSERT ON `damage_reports` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('damage_reports', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('DR', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_equipment_public_id` BEFORE INSERT ON `equipment` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('equipment', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('E', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_feedback_public_id` BEFORE INSERT ON `feedback` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('feedback', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('FB', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_fines_public_id` BEFORE INSERT ON `fines` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('fines', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('F', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_fine_payments_public_id` BEFORE INSERT ON `fine_payments` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('fine_payments', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('FP', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_inventory_movements_public_id` BEFORE INSERT ON `inventory_movements` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('inventory_movements', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('IM', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_locations_public_id` BEFORE INSERT ON `locations` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('locations', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('L', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_maintenance_records_public_id` BEFORE INSERT ON `maintenance_records` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('maintenance_records', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('MR', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_maintenance_schedules_public_id` BEFORE INSERT ON `maintenance_schedules` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('maintenance_schedules', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('MS', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_notifications_public_id` BEFORE INSERT ON `notifications` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('notifications', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('N', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_repair_records_public_id` BEFORE INSERT ON `repair_records` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('repair_records', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('RR', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_tickets_public_id` BEFORE INSERT ON `tickets` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('tickets', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('T', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_ticket_attachments_public_id` BEFORE INSERT ON `ticket_attachments` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('ticket_attachments', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('TA', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_ticket_replies_public_id` BEFORE INSERT ON `ticket_replies` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('ticket_replies', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('TR', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_users_public_id` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('users', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('U', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_violation_records_public_id` BEFORE INSERT ON `violation_records` FOR EACH ROW BEGIN
    IF NEW.public_id IS NULL OR NEW.public_id = '' THEN
        INSERT INTO id_sequences (sequence_name, next_value)
        VALUES ('violation_records', LAST_INSERT_ID(1))
        ON DUPLICATE KEY UPDATE
        next_value = LAST_INSERT_ID(next_value + 1);

        SET NEW.public_id =
            CONCAT('VR', LPAD(LAST_INSERT_ID(), 5, '0'));
    END IF;
END;
SQL);


    }

    public function down(): void
    {
        // Drop public ID triggers before dropping their tables.
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_borrowing_items_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_borrowing_requests_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_categories_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_damage_reports_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_equipment_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_feedback_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_fines_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_fine_payments_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_inventory_movements_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_locations_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_maintenance_records_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_maintenance_schedules_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_notifications_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_repair_records_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_tickets_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_ticket_attachments_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_ticket_replies_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_users_public_id`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_violation_records_public_id`');

        // Reverse dependency order.
        DB::statement('DROP TABLE IF EXISTS `ticket_attachments`');
        DB::statement('DROP TABLE IF EXISTS `ticket_replies`');
        DB::statement('DROP TABLE IF EXISTS `tickets`');
        DB::statement('DROP TABLE IF EXISTS `repair_records`');
        DB::statement('DROP TABLE IF EXISTS `maintenance_records`');
        DB::statement('DROP TABLE IF EXISTS `violation_records`');
        DB::statement('DROP TABLE IF EXISTS `damage_reports`');
        DB::statement('DROP TABLE IF EXISTS `borrowing_items`');
        DB::statement('DROP TABLE IF EXISTS `borrowing_requests`');
        DB::statement('DROP TABLE IF EXISTS `maintenance_schedules`');
        DB::statement('DROP TABLE IF EXISTS `inventory_movements`');
        DB::statement('DROP TABLE IF EXISTS `fine_payments`');
        DB::statement('DROP TABLE IF EXISTS `fines`');
        DB::statement('DROP TABLE IF EXISTS `feedback`');
        DB::statement('DROP TABLE IF EXISTS `equipment_status_histories`');
        DB::statement('DROP TABLE IF EXISTS `equipment`');
        DB::statement('DROP TABLE IF EXISTS `categories`');
        DB::statement('DROP TABLE IF EXISTS `locations`');
        DB::statement('DROP TABLE IF EXISTS `notifications`');
        DB::statement('DROP TABLE IF EXISTS `users`');
        DB::statement('DROP TABLE IF EXISTS `id_sequences`');
    }
};
