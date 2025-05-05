-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 04, 2025 at 09:24 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clix_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_iot_table`
--

CREATE TABLE `active_iot_table` (
  `_active_iot_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `active_outage_table`
--

CREATE TABLE `active_outage_table` (
  `_active_outage_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_log_table`
--

CREATE TABLE `admin_log_table` (
  `_admin_log_id_` bigint(20) NOT NULL,
  `_admin_id_` bigint(20) NOT NULL,
  `_action_performed_` text NOT NULL,
  `_log_time_` datetime NOT NULL,
  `_ip_address_` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_table`
--

CREATE TABLE `admin_table` (
  `_admin_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_notifiaction_table`
--

CREATE TABLE `alert_notifiaction_table` (
  `_alt_not_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `balance_table`
--

CREATE TABLE `balance_table` (
  `_balance_id_` bigint(20) NOT NULL,
  `_user_id_` bigint(20) NOT NULL,
  `_iot_id_` bigint(20) NOT NULL,
  `_current_balance_` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banned_client_table`
--

CREATE TABLE `banned_client_table` (
  `_banned_client_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_table`
--

CREATE TABLE `client_table` (
  `_client_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `declined_request_table`
--

CREATE TABLE `declined_request_table` (
  `_declined_request_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `electricity_table`
--

CREATE TABLE `electricity_table` (
  `_electricity_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_table`
--

CREATE TABLE `feedback_table` (
  `_feedback_id_` bigint(20) NOT NULL,
  `_user_id_` bigint(20) DEFAULT NULL,
  `_feedback_time_` datetime NOT NULL,
  `_feedback_name_` varchar(255) NOT NULL,
  `_feedback_subject_` varchar(255) NOT NULL,
  `_feedback_message_` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gas_table`
--

CREATE TABLE `gas_table` (
  `_gas_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `high_impact_table`
--

CREATE TABLE `high_impact_table` (
  `_high_impact_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inactive_iot_table`
--

CREATE TABLE `inactive_iot_table` (
  `_inactive_iot_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `information_notification_table`
--

CREATE TABLE `information_notification_table` (
  `_info_not_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `iot_table`
--

CREATE TABLE `iot_table` (
  `_iot_id_` bigint(20) NOT NULL,
  `_utility_id_` bigint(20) NOT NULL,
  `_iot_label_` varchar(255) DEFAULT NULL,
  `_iot_latitude_` decimal(9,6) NOT NULL,
  `_iot_longitude_` decimal(9,6) NOT NULL,
  `_last_reported_time_` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `low_impact_table`
--

CREATE TABLE `low_impact_table` (
  `_low_impact_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medium_impact_table`
--

CREATE TABLE `medium_impact_table` (
  `_medium_impact_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_table`
--

CREATE TABLE `notification_table` (
  `_notification_id_` bigint(20) NOT NULL,
  `_user_id_` bigint(20) DEFAULT NULL,
  `_notification_time_` datetime NOT NULL,
  `_notification_title_` varchar(255) NOT NULL,
  `_notification_message_` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `other_alert_notification_table`
--

CREATE TABLE `other_alert_notification_table` (
  `_other_alt_not_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `outage_alert_notification_table`
--

CREATE TABLE `outage_alert_notification_table` (
  `_other_alt_not_id_` bigint(20) NOT NULL,
  `_outage_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `outage_mapping_table`
--

CREATE TABLE `outage_mapping_table` (
  `_outage_map_id_` bigint(20) NOT NULL,
  `_outage_id_` bigint(20) NOT NULL,
  `_iot_id_` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `outage_table`
--

CREATE TABLE `outage_table` (
  `_outage_id_` bigint(20) NOT NULL,
  `_utility_id_` bigint(20) NOT NULL,
  `_start_time_` datetime NOT NULL,
  `_end_time_` datetime NOT NULL,
  `_affected_area_` varchar(255) NOT NULL,
  `_latitude_` decimal(9,6) NOT NULL,
  `_longitude_` decimal(9,6) NOT NULL,
  `_range_km_` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pending_request_table`
--

CREATE TABLE `pending_request_table` (
  `_pending_request_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recharge_table`
--

CREATE TABLE `recharge_table` (
  `_recharge_id_` bigint(20) NOT NULL,
  `_user_id_` bigint(20) NOT NULL,
  `_iot_id_` bigint(20) NOT NULL,
  `_recharge_time_` datetime NOT NULL,
  `_recharge_amount_` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reminder_notification_table`
--

CREATE TABLE `reminder_notification_table` (
  `_rem_not_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_table`
--

CREATE TABLE `request_table` (
  `_request_id_` bigint(20) NOT NULL,
  `_user_id_` bigint(20) NOT NULL,
  `_iot_id_` bigint(20) NOT NULL,
  `_latitude_` decimal(9,6) NOT NULL,
  `_longitude_` decimal(9,6) NOT NULL,
  `_request_time_` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resolved_outage_table`
--

CREATE TABLE `resolved_outage_table` (
  `_resolved_outage_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unpaid_iot_table`
--

CREATE TABLE `unpaid_iot_table` (
  `_unpaid_iot_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usage_table`
--

CREATE TABLE `usage_table` (
  `_usage_id_` bigint(20) NOT NULL,
  `_user_id_` bigint(20) NOT NULL,
  `_iot_id_` bigint(20) NOT NULL,
  `_usage_time_` datetime NOT NULL,
  `_usage_amount_` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_login_log_table`
--

CREATE TABLE `user_login_log_table` (
  `_user_log_id_` bigint(20) NOT NULL,
  `_user_id_` bigint(20) NOT NULL,
  `_log_time_` datetime NOT NULL,
  `_ip_address_` varchar(255) NOT NULL,
  `_device_latitude_` decimal(9,6) NOT NULL,
  `_device_longitude_` decimal(9,6) NOT NULL,
  `_device_name_` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `_user_id_` bigint(20) NOT NULL,
  `_first_name_` varchar(255) NOT NULL,
  `_last_name_` varchar(255) NOT NULL,
  `_date_of_birth_` date NOT NULL,
  `_nid_` bigint(20) DEFAULT NULL,
  `_email_` varchar(255) NOT NULL,
  `_phone_` varchar(255) NOT NULL,
  `_current_address_` varchar(255) NOT NULL,
  `_password_` varchar(255) NOT NULL,
  `_profile_picture_` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utility_table`
--

CREATE TABLE `utility_table` (
  `_utility_id_` bigint(20) NOT NULL,
  `_cost_per_unit_` decimal(9,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `water_table`
--

CREATE TABLE `water_table` (
  `_water_id_` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_iot_table`
--
ALTER TABLE `active_iot_table`
  ADD PRIMARY KEY (`_active_iot_id_`),
  ADD UNIQUE KEY `_active_iot_id__UNIQUE` (`_active_iot_id_`);

--
-- Indexes for table `active_outage_table`
--
ALTER TABLE `active_outage_table`
  ADD PRIMARY KEY (`_active_outage_id_`),
  ADD UNIQUE KEY `_active_outage_id__UNIQUE` (`_active_outage_id_`);

--
-- Indexes for table `admin_log_table`
--
ALTER TABLE `admin_log_table`
  ADD PRIMARY KEY (`_admin_log_id_`),
  ADD UNIQUE KEY `admin_log_id_UNIQUE` (`_admin_log_id_`),
  ADD KEY `log_admin_fk1_idx` (`_admin_id_`);

--
-- Indexes for table `admin_table`
--
ALTER TABLE `admin_table`
  ADD PRIMARY KEY (`_admin_id_`),
  ADD UNIQUE KEY `_admin_id__UNIQUE` (`_admin_id_`);

--
-- Indexes for table `alert_notifiaction_table`
--
ALTER TABLE `alert_notifiaction_table`
  ADD PRIMARY KEY (`_alt_not_id_`),
  ADD UNIQUE KEY `_alt_not_id__UNIQUE` (`_alt_not_id_`);

--
-- Indexes for table `balance_table`
--
ALTER TABLE `balance_table`
  ADD PRIMARY KEY (`_balance_id_`),
  ADD UNIQUE KEY `_balance_id__UNIQUE` (`_balance_id_`),
  ADD KEY `balance_user_fk1_idx` (`_user_id_`),
  ADD KEY `balance_iot_fk1_idx` (`_iot_id_`);

--
-- Indexes for table `banned_client_table`
--
ALTER TABLE `banned_client_table`
  ADD PRIMARY KEY (`_banned_client_id_`),
  ADD UNIQUE KEY `_banned_client_id__UNIQUE` (`_banned_client_id_`);

--
-- Indexes for table `client_table`
--
ALTER TABLE `client_table`
  ADD PRIMARY KEY (`_client_id_`),
  ADD UNIQUE KEY `_client_id_UNIQUE` (`_client_id_`);

--
-- Indexes for table `declined_request_table`
--
ALTER TABLE `declined_request_table`
  ADD PRIMARY KEY (`_declined_request_id_`),
  ADD UNIQUE KEY `_declined_request_id__UNIQUE` (`_declined_request_id_`);

--
-- Indexes for table `electricity_table`
--
ALTER TABLE `electricity_table`
  ADD PRIMARY KEY (`_electricity_id_`),
  ADD UNIQUE KEY `_electricity_id__UNIQUE` (`_electricity_id_`);

--
-- Indexes for table `feedback_table`
--
ALTER TABLE `feedback_table`
  ADD PRIMARY KEY (`_feedback_id_`),
  ADD UNIQUE KEY `idfeedback_table_UNIQUE` (`_feedback_id_`),
  ADD KEY `feedback_user_fk1_idx` (`_user_id_`);

--
-- Indexes for table `gas_table`
--
ALTER TABLE `gas_table`
  ADD PRIMARY KEY (`_gas_id_`),
  ADD UNIQUE KEY `_gas_id__UNIQUE` (`_gas_id_`);

--
-- Indexes for table `high_impact_table`
--
ALTER TABLE `high_impact_table`
  ADD PRIMARY KEY (`_high_impact_id_`),
  ADD UNIQUE KEY `_high_impact_id__UNIQUE` (`_high_impact_id_`);

--
-- Indexes for table `inactive_iot_table`
--
ALTER TABLE `inactive_iot_table`
  ADD PRIMARY KEY (`_inactive_iot_id_`),
  ADD UNIQUE KEY `_inactive_iot_id__UNIQUE` (`_inactive_iot_id_`);

--
-- Indexes for table `information_notification_table`
--
ALTER TABLE `information_notification_table`
  ADD PRIMARY KEY (`_info_not_id_`),
  ADD UNIQUE KEY `_info_not_id__UNIQUE` (`_info_not_id_`);

--
-- Indexes for table `iot_table`
--
ALTER TABLE `iot_table`
  ADD PRIMARY KEY (`_iot_id_`),
  ADD UNIQUE KEY `_iot_id__UNIQUE` (`_iot_id_`),
  ADD KEY `iot_utility_fk1_idx` (`_utility_id_`);

--
-- Indexes for table `low_impact_table`
--
ALTER TABLE `low_impact_table`
  ADD PRIMARY KEY (`_low_impact_id_`),
  ADD UNIQUE KEY `_low_impact_id__UNIQUE` (`_low_impact_id_`);

--
-- Indexes for table `medium_impact_table`
--
ALTER TABLE `medium_impact_table`
  ADD PRIMARY KEY (`_medium_impact_id_`),
  ADD UNIQUE KEY `_medium_impact_id__UNIQUE` (`_medium_impact_id_`);

--
-- Indexes for table `notification_table`
--
ALTER TABLE `notification_table`
  ADD PRIMARY KEY (`_notification_id_`),
  ADD UNIQUE KEY `_notification_id__UNIQUE` (`_notification_id_`);

--
-- Indexes for table `other_alert_notification_table`
--
ALTER TABLE `other_alert_notification_table`
  ADD PRIMARY KEY (`_other_alt_not_id_`),
  ADD UNIQUE KEY `_other_not_id__UNIQUE` (`_other_alt_not_id_`);

--
-- Indexes for table `outage_alert_notification_table`
--
ALTER TABLE `outage_alert_notification_table`
  ADD PRIMARY KEY (`_other_alt_not_id_`),
  ADD UNIQUE KEY `_other_alt_not_id__UNIQUE` (`_other_alt_not_id_`),
  ADD KEY `outage_alt_not_fk2_idx` (`_outage_id_`);

--
-- Indexes for table `outage_mapping_table`
--
ALTER TABLE `outage_mapping_table`
  ADD PRIMARY KEY (`_outage_map_id_`),
  ADD UNIQUE KEY `_outage_map_id__UNIQUE` (`_outage_map_id_`),
  ADD KEY `outage_mapping_fk1_idx` (`_outage_id_`),
  ADD KEY `outage_mapping_fk2_idx` (`_iot_id_`);

--
-- Indexes for table `outage_table`
--
ALTER TABLE `outage_table`
  ADD PRIMARY KEY (`_outage_id_`),
  ADD UNIQUE KEY `_outage_id__UNIQUE` (`_outage_id_`),
  ADD KEY `outage_utility_fk1_idx` (`_utility_id_`);

--
-- Indexes for table `pending_request_table`
--
ALTER TABLE `pending_request_table`
  ADD PRIMARY KEY (`_pending_request_id_`),
  ADD UNIQUE KEY `_pending_id__UNIQUE` (`_pending_request_id_`);

--
-- Indexes for table `recharge_table`
--
ALTER TABLE `recharge_table`
  ADD PRIMARY KEY (`_recharge_id_`),
  ADD UNIQUE KEY `_recharge_id__UNIQUE` (`_recharge_id_`),
  ADD KEY `recharge_user_fk1_idx` (`_user_id_`),
  ADD KEY `recharge_iot_fk1_idx` (`_iot_id_`);

--
-- Indexes for table `reminder_notification_table`
--
ALTER TABLE `reminder_notification_table`
  ADD PRIMARY KEY (`_rem_not_id_`),
  ADD UNIQUE KEY `_rem_not_id__UNIQUE` (`_rem_not_id_`);

--
-- Indexes for table `request_table`
--
ALTER TABLE `request_table`
  ADD PRIMARY KEY (`_request_id_`),
  ADD UNIQUE KEY `_request_id__UNIQUE` (`_request_id_`),
  ADD KEY `request_user_fk1_idx` (`_user_id_`),
  ADD KEY `request_iot_fk1_idx` (`_iot_id_`);

--
-- Indexes for table `resolved_outage_table`
--
ALTER TABLE `resolved_outage_table`
  ADD PRIMARY KEY (`_resolved_outage_id_`),
  ADD UNIQUE KEY `_inactive_outage_id__UNIQUE` (`_resolved_outage_id_`);

--
-- Indexes for table `unpaid_iot_table`
--
ALTER TABLE `unpaid_iot_table`
  ADD PRIMARY KEY (`_unpaid_iot_id_`),
  ADD UNIQUE KEY `_unpaid_iot_id__UNIQUE` (`_unpaid_iot_id_`);

--
-- Indexes for table `usage_table`
--
ALTER TABLE `usage_table`
  ADD PRIMARY KEY (`_usage_id_`),
  ADD UNIQUE KEY `_usage_id__UNIQUE` (`_usage_id_`),
  ADD KEY `usage_user_fk1_idx` (`_user_id_`),
  ADD KEY `usage_iot_fk1_idx` (`_iot_id_`);

--
-- Indexes for table `user_login_log_table`
--
ALTER TABLE `user_login_log_table`
  ADD PRIMARY KEY (`_user_log_id_`),
  ADD UNIQUE KEY `_user_log_id__UNIQUE` (`_user_log_id_`),
  ADD KEY `login_log_user_fk1_idx` (`_user_id_`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`_user_id_`),
  ADD UNIQUE KEY `idnew_table_UNIQUE` (`_user_id_`),
  ADD UNIQUE KEY `_email__UNIQUE` (`_email_`),
  ADD UNIQUE KEY `_phone__UNIQUE` (`_phone_`),
  ADD UNIQUE KEY `_nid__UNIQUE` (`_nid_`);

--
-- Indexes for table `utility_table`
--
ALTER TABLE `utility_table`
  ADD PRIMARY KEY (`_utility_id_`),
  ADD UNIQUE KEY `_utility_id__UNIQUE` (`_utility_id_`);

--
-- Indexes for table `water_table`
--
ALTER TABLE `water_table`
  ADD PRIMARY KEY (`_water_id_`),
  ADD UNIQUE KEY `_water_id__UNIQUE` (`_water_id_`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_log_table`
--
ALTER TABLE `admin_log_table`
  MODIFY `_admin_log_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `balance_table`
--
ALTER TABLE `balance_table`
  MODIFY `_balance_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback_table`
--
ALTER TABLE `feedback_table`
  MODIFY `_feedback_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `iot_table`
--
ALTER TABLE `iot_table`
  MODIFY `_iot_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_table`
--
ALTER TABLE `notification_table`
  MODIFY `_notification_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `outage_mapping_table`
--
ALTER TABLE `outage_mapping_table`
  MODIFY `_outage_map_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `outage_table`
--
ALTER TABLE `outage_table`
  MODIFY `_outage_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recharge_table`
--
ALTER TABLE `recharge_table`
  MODIFY `_recharge_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request_table`
--
ALTER TABLE `request_table`
  MODIFY `_request_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usage_table`
--
ALTER TABLE `usage_table`
  MODIFY `_usage_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_login_log_table`
--
ALTER TABLE `user_login_log_table`
  MODIFY `_user_log_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `_user_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utility_table`
--
ALTER TABLE `utility_table`
  MODIFY `_utility_id_` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `active_iot_table`
--
ALTER TABLE `active_iot_table`
  ADD CONSTRAINT `active_iot_fk1` FOREIGN KEY (`_active_iot_id_`) REFERENCES `iot_table` (`_iot_id_`);

--
-- Constraints for table `active_outage_table`
--
ALTER TABLE `active_outage_table`
  ADD CONSTRAINT `outage_active_fk1` FOREIGN KEY (`_active_outage_id_`) REFERENCES `outage_table` (`_outage_id_`);

--
-- Constraints for table `admin_log_table`
--
ALTER TABLE `admin_log_table`
  ADD CONSTRAINT `log_admin_fk1` FOREIGN KEY (`_admin_id_`) REFERENCES `admin_table` (`_admin_id_`);

--
-- Constraints for table `admin_table`
--
ALTER TABLE `admin_table`
  ADD CONSTRAINT `admin_user_fk1` FOREIGN KEY (`_admin_id_`) REFERENCES `user_table` (`_user_id_`);

--
-- Constraints for table `alert_notifiaction_table`
--
ALTER TABLE `alert_notifiaction_table`
  ADD CONSTRAINT `alt_not_fk1` FOREIGN KEY (`_alt_not_id_`) REFERENCES `notification_table` (`_notification_id_`);

--
-- Constraints for table `balance_table`
--
ALTER TABLE `balance_table`
  ADD CONSTRAINT `balance_iot_fk1` FOREIGN KEY (`_iot_id_`) REFERENCES `iot_table` (`_iot_id_`),
  ADD CONSTRAINT `balance_user_fk1` FOREIGN KEY (`_user_id_`) REFERENCES `user_table` (`_user_id_`);

--
-- Constraints for table `banned_client_table`
--
ALTER TABLE `banned_client_table`
  ADD CONSTRAINT `banned_client_fk1` FOREIGN KEY (`_banned_client_id_`) REFERENCES `client_table` (`_client_id_`);

--
-- Constraints for table `client_table`
--
ALTER TABLE `client_table`
  ADD CONSTRAINT `client_user_fk1` FOREIGN KEY (`_client_id_`) REFERENCES `user_table` (`_user_id_`);

--
-- Constraints for table `declined_request_table`
--
ALTER TABLE `declined_request_table`
  ADD CONSTRAINT `declined_request_fk1` FOREIGN KEY (`_declined_request_id_`) REFERENCES `request_table` (`_request_id_`);

--
-- Constraints for table `electricity_table`
--
ALTER TABLE `electricity_table`
  ADD CONSTRAINT `electricity_utility_fk1` FOREIGN KEY (`_electricity_id_`) REFERENCES `utility_table` (`_utility_id_`);

--
-- Constraints for table `feedback_table`
--
ALTER TABLE `feedback_table`
  ADD CONSTRAINT `feedback_user_fk1` FOREIGN KEY (`_user_id_`) REFERENCES `user_table` (`_user_id_`);

--
-- Constraints for table `gas_table`
--
ALTER TABLE `gas_table`
  ADD CONSTRAINT `gas_utility_fk1` FOREIGN KEY (`_gas_id_`) REFERENCES `utility_table` (`_utility_id_`);

--
-- Constraints for table `high_impact_table`
--
ALTER TABLE `high_impact_table`
  ADD CONSTRAINT `high_impact_fk1` FOREIGN KEY (`_high_impact_id_`) REFERENCES `outage_mapping_table` (`_outage_map_id_`);

--
-- Constraints for table `inactive_iot_table`
--
ALTER TABLE `inactive_iot_table`
  ADD CONSTRAINT `inactive_iot_fk1` FOREIGN KEY (`_inactive_iot_id_`) REFERENCES `iot_table` (`_iot_id_`);

--
-- Constraints for table `information_notification_table`
--
ALTER TABLE `information_notification_table`
  ADD CONSTRAINT `info_not_fk1` FOREIGN KEY (`_info_not_id_`) REFERENCES `notification_table` (`_notification_id_`);

--
-- Constraints for table `iot_table`
--
ALTER TABLE `iot_table`
  ADD CONSTRAINT `iot_utility_fk1` FOREIGN KEY (`_utility_id_`) REFERENCES `utility_table` (`_utility_id_`);

--
-- Constraints for table `low_impact_table`
--
ALTER TABLE `low_impact_table`
  ADD CONSTRAINT `low_impact_fk1` FOREIGN KEY (`_low_impact_id_`) REFERENCES `outage_mapping_table` (`_outage_map_id_`);

--
-- Constraints for table `medium_impact_table`
--
ALTER TABLE `medium_impact_table`
  ADD CONSTRAINT `medium_impact_fk1` FOREIGN KEY (`_medium_impact_id_`) REFERENCES `outage_mapping_table` (`_outage_map_id_`);

--
-- Constraints for table `other_alert_notification_table`
--
ALTER TABLE `other_alert_notification_table`
  ADD CONSTRAINT `other_alt_not_fk1` FOREIGN KEY (`_other_alt_not_id_`) REFERENCES `alert_notifiaction_table` (`_alt_not_id_`);

--
-- Constraints for table `outage_alert_notification_table`
--
ALTER TABLE `outage_alert_notification_table`
  ADD CONSTRAINT `outage_alt_not_fk1` FOREIGN KEY (`_other_alt_not_id_`) REFERENCES `alert_notifiaction_table` (`_alt_not_id_`),
  ADD CONSTRAINT `outage_alt_not_fk2` FOREIGN KEY (`_outage_id_`) REFERENCES `outage_table` (`_outage_id_`);

--
-- Constraints for table `outage_mapping_table`
--
ALTER TABLE `outage_mapping_table`
  ADD CONSTRAINT `outage_mapping_fk1` FOREIGN KEY (`_outage_id_`) REFERENCES `outage_table` (`_outage_id_`),
  ADD CONSTRAINT `outage_mapping_fk2` FOREIGN KEY (`_iot_id_`) REFERENCES `iot_table` (`_iot_id_`);

--
-- Constraints for table `outage_table`
--
ALTER TABLE `outage_table`
  ADD CONSTRAINT `outage_utility_fk1` FOREIGN KEY (`_utility_id_`) REFERENCES `utility_table` (`_utility_id_`);

--
-- Constraints for table `pending_request_table`
--
ALTER TABLE `pending_request_table`
  ADD CONSTRAINT `pending_request_fk1` FOREIGN KEY (`_pending_request_id_`) REFERENCES `request_table` (`_request_id_`);

--
-- Constraints for table `recharge_table`
--
ALTER TABLE `recharge_table`
  ADD CONSTRAINT `recharge_iot_fk1` FOREIGN KEY (`_iot_id_`) REFERENCES `iot_table` (`_iot_id_`),
  ADD CONSTRAINT `recharge_user_fk1` FOREIGN KEY (`_user_id_`) REFERENCES `user_table` (`_user_id_`);

--
-- Constraints for table `reminder_notification_table`
--
ALTER TABLE `reminder_notification_table`
  ADD CONSTRAINT `rem_not_fk1` FOREIGN KEY (`_rem_not_id_`) REFERENCES `notification_table` (`_notification_id_`);

--
-- Constraints for table `request_table`
--
ALTER TABLE `request_table`
  ADD CONSTRAINT `request_iot_fk1` FOREIGN KEY (`_iot_id_`) REFERENCES `iot_table` (`_iot_id_`),
  ADD CONSTRAINT `request_user_fk1` FOREIGN KEY (`_user_id_`) REFERENCES `user_table` (`_user_id_`);

--
-- Constraints for table `resolved_outage_table`
--
ALTER TABLE `resolved_outage_table`
  ADD CONSTRAINT `resolved_outage_fk1` FOREIGN KEY (`_resolved_outage_id_`) REFERENCES `outage_table` (`_outage_id_`);

--
-- Constraints for table `unpaid_iot_table`
--
ALTER TABLE `unpaid_iot_table`
  ADD CONSTRAINT `unpaid_iot_fk1` FOREIGN KEY (`_unpaid_iot_id_`) REFERENCES `iot_table` (`_iot_id_`);

--
-- Constraints for table `usage_table`
--
ALTER TABLE `usage_table`
  ADD CONSTRAINT `usage_iot_fk1` FOREIGN KEY (`_iot_id_`) REFERENCES `iot_table` (`_iot_id_`),
  ADD CONSTRAINT `usage_user_fk1` FOREIGN KEY (`_user_id_`) REFERENCES `user_table` (`_user_id_`);

--
-- Constraints for table `user_login_log_table`
--
ALTER TABLE `user_login_log_table`
  ADD CONSTRAINT `login_log_user_fk1` FOREIGN KEY (`_user_id_`) REFERENCES `user_table` (`_user_id_`);

--
-- Constraints for table `water_table`
--
ALTER TABLE `water_table`
  ADD CONSTRAINT `water_utility_fk1` FOREIGN KEY (`_water_id_`) REFERENCES `utility_table` (`_utility_id_`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
