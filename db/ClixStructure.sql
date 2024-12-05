-- MySQL dump 10.13  Distrib 8.0.40, for macos14 (arm64)
--
-- Host: localhost    Database: clix_database
-- ------------------------------------------------------
-- Server version	8.0.40

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `active_iot_table`
--

DROP TABLE IF EXISTS `active_iot_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `active_iot_table` (
  `_active_iot_id_` bigint NOT NULL,
  PRIMARY KEY (`_active_iot_id_`),
  UNIQUE KEY `_active_iot_id__UNIQUE` (`_active_iot_id_`),
  CONSTRAINT `active_iot_fk1` FOREIGN KEY (`_active_iot_id_`) REFERENCES `iot_table` (`_iot_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `active_outage_table`
--

DROP TABLE IF EXISTS `active_outage_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `active_outage_table` (
  `_active_outage_id_` bigint NOT NULL,
  PRIMARY KEY (`_active_outage_id_`),
  UNIQUE KEY `_active_outage_id__UNIQUE` (`_active_outage_id_`),
  CONSTRAINT `outage_active_fk1` FOREIGN KEY (`_active_outage_id_`) REFERENCES `outage_table` (`_outage_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_log_table`
--

DROP TABLE IF EXISTS `admin_log_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_log_table` (
  `_admin_log_id_` bigint NOT NULL AUTO_INCREMENT,
  `_admin_id_` bigint NOT NULL,
  `_action_performed_` text NOT NULL,
  `_log_time_` datetime NOT NULL,
  `_ip_address_` varchar(255) NOT NULL,
  PRIMARY KEY (`_admin_log_id_`),
  UNIQUE KEY `admin_log_id_UNIQUE` (`_admin_log_id_`),
  KEY `log_admin_fk1_idx` (`_admin_id_`),
  CONSTRAINT `log_admin_fk1` FOREIGN KEY (`_admin_id_`) REFERENCES `admin_table` (`_admin_id_`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_table`
--

DROP TABLE IF EXISTS `admin_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_table` (
  `_admin_id_` bigint NOT NULL,
  PRIMARY KEY (`_admin_id_`),
  UNIQUE KEY `_admin_id__UNIQUE` (`_admin_id_`),
  CONSTRAINT `admin_user_fk1` FOREIGN KEY (`_admin_id_`) REFERENCES `user_table` (`_user_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alert_notifiaction_table`
--

DROP TABLE IF EXISTS `alert_notifiaction_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_notifiaction_table` (
  `_alt_not_id_` bigint NOT NULL,
  PRIMARY KEY (`_alt_not_id_`),
  UNIQUE KEY `_alt_not_id__UNIQUE` (`_alt_not_id_`),
  CONSTRAINT `alt_not_fk1` FOREIGN KEY (`_alt_not_id_`) REFERENCES `notification_table` (`_notification_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `balance_table`
--

DROP TABLE IF EXISTS `balance_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `balance_table` (
  `_balance_id_` bigint NOT NULL AUTO_INCREMENT,
  `_user_id_` bigint NOT NULL,
  `_iot_id_` bigint NOT NULL,
  `_current_balance_` decimal(10,2) NOT NULL,
  PRIMARY KEY (`_balance_id_`),
  UNIQUE KEY `_balance_id__UNIQUE` (`_balance_id_`),
  KEY `balance_user_fk1_idx` (`_user_id_`),
  KEY `balance_iot_fk1_idx` (`_iot_id_`),
  CONSTRAINT `balance_iot_fk1` FOREIGN KEY (`_iot_id_`) REFERENCES `iot_table` (`_iot_id_`),
  CONSTRAINT `balance_user_fk1` FOREIGN KEY (`_user_id_`) REFERENCES `user_table` (`_user_id_`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `client_table`
--

DROP TABLE IF EXISTS `client_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_table` (
  `_client_id_` bigint NOT NULL,
  PRIMARY KEY (`_client_id_`),
  UNIQUE KEY `_client_id_UNIQUE` (`_client_id_`),
  CONSTRAINT `client_user_fk1` FOREIGN KEY (`_client_id_`) REFERENCES `user_table` (`_user_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `electricity_admin_table`
--

DROP TABLE IF EXISTS `electricity_admin_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `electricity_admin_table` (
  `_electricity_admin_id_` bigint NOT NULL,
  PRIMARY KEY (`_electricity_admin_id_`),
  UNIQUE KEY `_electricity_admin_id__UNIQUE` (`_electricity_admin_id_`),
  CONSTRAINT `electricity_admin_fk1` FOREIGN KEY (`_electricity_admin_id_`) REFERENCES `utility_admin_table` (`_utility_admin_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `electricity_table`
--

DROP TABLE IF EXISTS `electricity_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `electricity_table` (
  `_electricity_id_` bigint NOT NULL,
  PRIMARY KEY (`_electricity_id_`),
  UNIQUE KEY `_electricity_id__UNIQUE` (`_electricity_id_`),
  CONSTRAINT `electricity_utility_fk1` FOREIGN KEY (`_electricity_id_`) REFERENCES `utility_table` (`_utility_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gas_admin_table`
--

DROP TABLE IF EXISTS `gas_admin_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gas_admin_table` (
  `_gas_admin_id_` bigint NOT NULL,
  PRIMARY KEY (`_gas_admin_id_`),
  UNIQUE KEY `_gas_admin_id__UNIQUE` (`_gas_admin_id_`),
  CONSTRAINT `gas_admin_fk1` FOREIGN KEY (`_gas_admin_id_`) REFERENCES `utility_admin_table` (`_utility_admin_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gas_table`
--

DROP TABLE IF EXISTS `gas_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gas_table` (
  `_gas_id_` bigint NOT NULL,
  PRIMARY KEY (`_gas_id_`),
  UNIQUE KEY `_gas_id__UNIQUE` (`_gas_id_`),
  CONSTRAINT `gas_utility_fk1` FOREIGN KEY (`_gas_id_`) REFERENCES `utility_table` (`_utility_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `high_impact_table`
--

DROP TABLE IF EXISTS `high_impact_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `high_impact_table` (
  `_high_impact_id_` bigint NOT NULL,
  PRIMARY KEY (`_high_impact_id_`),
  UNIQUE KEY `_high_impact_id__UNIQUE` (`_high_impact_id_`),
  CONSTRAINT `high_impact_fk1` FOREIGN KEY (`_high_impact_id_`) REFERENCES `outage_mapping_table` (`_outage_map_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inactive_iot_table`
--

DROP TABLE IF EXISTS `inactive_iot_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inactive_iot_table` (
  `_inactive_iot_id_` bigint NOT NULL,
  PRIMARY KEY (`_inactive_iot_id_`),
  UNIQUE KEY `_inactive_iot_id__UNIQUE` (`_inactive_iot_id_`),
  CONSTRAINT `inactive_iot_fk1` FOREIGN KEY (`_inactive_iot_id_`) REFERENCES `iot_table` (`_iot_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `information_notification_table`
--

DROP TABLE IF EXISTS `information_notification_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `information_notification_table` (
  `_info_not_id_` bigint NOT NULL,
  PRIMARY KEY (`_info_not_id_`),
  UNIQUE KEY `_info_not_id__UNIQUE` (`_info_not_id_`),
  CONSTRAINT `info_not_fk1` FOREIGN KEY (`_info_not_id_`) REFERENCES `notification_table` (`_notification_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `iot_table`
--

DROP TABLE IF EXISTS `iot_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iot_table` (
  `_iot_id_` bigint NOT NULL AUTO_INCREMENT,
  `_utility_id_` bigint NOT NULL,
  `_iot_label_` varchar(255) NOT NULL,
  `_iot_latitude_` decimal(9,6) NOT NULL,
  `_iot_longitude_` decimal(9,6) NOT NULL,
  `_last_reported_time_` datetime NOT NULL,
  PRIMARY KEY (`_iot_id_`),
  UNIQUE KEY `_iot_id__UNIQUE` (`_iot_id_`),
  KEY `iot_utility_fk1_idx` (`_utility_id_`),
  CONSTRAINT `iot_utility_fk1` FOREIGN KEY (`_utility_id_`) REFERENCES `utility_table` (`_utility_id_`)
) ENGINE=InnoDB AUTO_INCREMENT=300000000019 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `low_impact_table`
--

DROP TABLE IF EXISTS `low_impact_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `low_impact_table` (
  `_low_impact_id_` bigint NOT NULL,
  PRIMARY KEY (`_low_impact_id_`),
  UNIQUE KEY `_low_impact_id__UNIQUE` (`_low_impact_id_`),
  CONSTRAINT `low_impact_fk1` FOREIGN KEY (`_low_impact_id_`) REFERENCES `outage_mapping_table` (`_outage_map_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `medium_impact_table`
--

DROP TABLE IF EXISTS `medium_impact_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medium_impact_table` (
  `_medium_impact_id_` bigint NOT NULL,
  PRIMARY KEY (`_medium_impact_id_`),
  UNIQUE KEY `_medium_impact_id__UNIQUE` (`_medium_impact_id_`),
  CONSTRAINT `medium_impact_fk1` FOREIGN KEY (`_medium_impact_id_`) REFERENCES `outage_mapping_table` (`_outage_map_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notification_table`
--

DROP TABLE IF EXISTS `notification_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_table` (
  `_notification_id_` bigint NOT NULL AUTO_INCREMENT,
  `_user_id_` bigint DEFAULT NULL,
  `_notification_time_` datetime NOT NULL,
  `_notification_title_` varchar(255) NOT NULL,
  `_notification_message_` text NOT NULL,
  PRIMARY KEY (`_notification_id_`),
  UNIQUE KEY `_notification_id__UNIQUE` (`_notification_id_`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `other_alert_notification_table`
--

DROP TABLE IF EXISTS `other_alert_notification_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `other_alert_notification_table` (
  `_other_alt_not_id_` bigint NOT NULL,
  PRIMARY KEY (`_other_alt_not_id_`),
  UNIQUE KEY `_other_not_id__UNIQUE` (`_other_alt_not_id_`),
  CONSTRAINT `other_alt_not_fk1` FOREIGN KEY (`_other_alt_not_id_`) REFERENCES `alert_notifiaction_table` (`_alt_not_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `outage_alert_notification_table`
--

DROP TABLE IF EXISTS `outage_alert_notification_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `outage_alert_notification_table` (
  `_other_alt_not_id_` bigint NOT NULL,
  `_outage_id_` bigint NOT NULL,
  PRIMARY KEY (`_other_alt_not_id_`),
  UNIQUE KEY `_other_alt_not_id__UNIQUE` (`_other_alt_not_id_`),
  KEY `outage_alt_not_fk2_idx` (`_outage_id_`),
  CONSTRAINT `outage_alt_not_fk1` FOREIGN KEY (`_other_alt_not_id_`) REFERENCES `alert_notifiaction_table` (`_alt_not_id_`),
  CONSTRAINT `outage_alt_not_fk2` FOREIGN KEY (`_outage_id_`) REFERENCES `outage_table` (`_outage_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `outage_mapping_table`
--

DROP TABLE IF EXISTS `outage_mapping_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `outage_mapping_table` (
  `_outage_map_id_` bigint NOT NULL AUTO_INCREMENT,
  `_outage_id_` bigint NOT NULL,
  `_iot_id_` bigint NOT NULL,
  PRIMARY KEY (`_outage_map_id_`),
  UNIQUE KEY `_outage_map_id__UNIQUE` (`_outage_map_id_`),
  KEY `outage_mapping_fk1_idx` (`_outage_id_`),
  KEY `outage_mapping_fk2_idx` (`_iot_id_`),
  CONSTRAINT `outage_mapping_fk1` FOREIGN KEY (`_outage_id_`) REFERENCES `outage_table` (`_outage_id_`),
  CONSTRAINT `outage_mapping_fk2` FOREIGN KEY (`_iot_id_`) REFERENCES `iot_table` (`_iot_id_`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `outage_table`
--

DROP TABLE IF EXISTS `outage_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `outage_table` (
  `_outage_id_` bigint NOT NULL AUTO_INCREMENT,
  `_utility_id_` bigint NOT NULL,
  `_start_time_` datetime NOT NULL,
  `_end_time_` datetime NOT NULL,
  `_affected_area_` varchar(255) NOT NULL,
  `_latitude_` decimal(9,6) NOT NULL,
  `_longitude_` decimal(9,6) NOT NULL,
  `_range_km_` decimal(10,2) NOT NULL,
  PRIMARY KEY (`_outage_id_`),
  UNIQUE KEY `_outage_id__UNIQUE` (`_outage_id_`),
  KEY `outage_utility_fk1_idx` (`_utility_id_`),
  CONSTRAINT `outage_utility_fk1` FOREIGN KEY (`_utility_id_`) REFERENCES `utility_table` (`_utility_id_`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recharge_table`
--

DROP TABLE IF EXISTS `recharge_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recharge_table` (
  `_recharge_id_` bigint NOT NULL AUTO_INCREMENT,
  `_user_id_` bigint NOT NULL,
  `_iot_id_` bigint NOT NULL,
  `_recharge_time_` datetime NOT NULL,
  `_recharge_amount_` decimal(10,2) NOT NULL,
  PRIMARY KEY (`_recharge_id_`),
  UNIQUE KEY `_recharge_id__UNIQUE` (`_recharge_id_`),
  KEY `recharge_user_fk1_idx` (`_user_id_`),
  KEY `recharge_iot_fk1_idx` (`_iot_id_`),
  CONSTRAINT `recharge_iot_fk1` FOREIGN KEY (`_iot_id_`) REFERENCES `iot_table` (`_iot_id_`),
  CONSTRAINT `recharge_user_fk1` FOREIGN KEY (`_user_id_`) REFERENCES `user_table` (`_user_id_`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reminder_notification_table`
--

DROP TABLE IF EXISTS `reminder_notification_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reminder_notification_table` (
  `_rem_not_id_` bigint NOT NULL,
  PRIMARY KEY (`_rem_not_id_`),
  UNIQUE KEY `_rem_not_id__UNIQUE` (`_rem_not_id_`),
  CONSTRAINT `rem_not_fk1` FOREIGN KEY (`_rem_not_id_`) REFERENCES `notification_table` (`_notification_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resolved_outage_table`
--

DROP TABLE IF EXISTS `resolved_outage_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resolved_outage_table` (
  `_resolved_outage_id_` bigint NOT NULL,
  PRIMARY KEY (`_resolved_outage_id_`),
  UNIQUE KEY `_inactive_outage_id__UNIQUE` (`_resolved_outage_id_`),
  CONSTRAINT `resolved_outage_fk1` FOREIGN KEY (`_resolved_outage_id_`) REFERENCES `outage_table` (`_outage_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `super_admin_table`
--

DROP TABLE IF EXISTS `super_admin_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `super_admin_table` (
  `_super_admin_id_` bigint NOT NULL,
  PRIMARY KEY (`_super_admin_id_`),
  UNIQUE KEY `_super_admin_id__UNIQUE` (`_super_admin_id_`),
  CONSTRAINT `super_admin_fk1` FOREIGN KEY (`_super_admin_id_`) REFERENCES `admin_table` (`_admin_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `unpaid_iot_table`
--

DROP TABLE IF EXISTS `unpaid_iot_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unpaid_iot_table` (
  `_unpaid_iot_id_` bigint NOT NULL,
  PRIMARY KEY (`_unpaid_iot_id_`),
  UNIQUE KEY `_unpaid_iot_id__UNIQUE` (`_unpaid_iot_id_`),
  CONSTRAINT `unpaid_iot_fk1` FOREIGN KEY (`_unpaid_iot_id_`) REFERENCES `iot_table` (`_iot_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usage_table`
--

DROP TABLE IF EXISTS `usage_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usage_table` (
  `_usage_id_` bigint NOT NULL AUTO_INCREMENT,
  `_user_id_` bigint NOT NULL,
  `_iot_id_` bigint NOT NULL,
  `_usage_time_` datetime NOT NULL,
  `_usage_amount_` decimal(10,2) NOT NULL,
  PRIMARY KEY (`_usage_id_`),
  UNIQUE KEY `_usage_id__UNIQUE` (`_usage_id_`),
  KEY `usage_user_fk1_idx` (`_user_id_`),
  KEY `usage_iot_fk1_idx` (`_iot_id_`),
  CONSTRAINT `usage_iot_fk1` FOREIGN KEY (`_iot_id_`) REFERENCES `iot_table` (`_iot_id_`),
  CONSTRAINT `usage_user_fk1` FOREIGN KEY (`_user_id_`) REFERENCES `user_table` (`_user_id_`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_login_log_table`
--

DROP TABLE IF EXISTS `user_login_log_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_login_log_table` (
  `_user_log_id_` bigint NOT NULL AUTO_INCREMENT,
  `_user_id_` bigint NOT NULL,
  `_log_time_` datetime NOT NULL,
  `_ip_address_` varchar(255) NOT NULL,
  `_device_latitude_` decimal(9,6) NOT NULL,
  `_device_longitude_` decimal(9,6) NOT NULL,
  `_device_name_` varchar(255) NOT NULL,
  PRIMARY KEY (`_user_log_id_`),
  UNIQUE KEY `_user_log_id__UNIQUE` (`_user_log_id_`),
  KEY `login_log_user_fk1_idx` (`_user_id_`),
  CONSTRAINT `login_log_user_fk1` FOREIGN KEY (`_user_id_`) REFERENCES `user_table` (`_user_id_`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_table`
--

DROP TABLE IF EXISTS `user_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_table` (
  `_user_id_` bigint NOT NULL AUTO_INCREMENT,
  `_first_name_` varchar(255) NOT NULL,
  `_last_name_` varchar(255) NOT NULL,
  `_date_of_birth_` date NOT NULL,
  `_nid_` bigint DEFAULT NULL,
  `_email_` varchar(255) NOT NULL,
  `_phone_` varchar(255) NOT NULL,
  `_current_address_` varchar(255) NOT NULL,
  `_password_` varchar(255) NOT NULL,
  `_profile_picture_` longblob,
  PRIMARY KEY (`_user_id_`),
  UNIQUE KEY `idnew_table_UNIQUE` (`_user_id_`),
  UNIQUE KEY `_email__UNIQUE` (`_email_`),
  UNIQUE KEY `_phone__UNIQUE` (`_phone_`),
  UNIQUE KEY `_nid__UNIQUE` (`_nid_`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `utility_admin_table`
--

DROP TABLE IF EXISTS `utility_admin_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utility_admin_table` (
  `_utility_admin_id_` bigint NOT NULL,
  PRIMARY KEY (`_utility_admin_id_`),
  UNIQUE KEY `_utility_admin_id__UNIQUE` (`_utility_admin_id_`),
  CONSTRAINT `utility_admin_fk1` FOREIGN KEY (`_utility_admin_id_`) REFERENCES `admin_table` (`_admin_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `utility_table`
--

DROP TABLE IF EXISTS `utility_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utility_table` (
  `_utility_id_` bigint NOT NULL AUTO_INCREMENT,
  `_cost_per_unit_` decimal(9,6) NOT NULL,
  PRIMARY KEY (`_utility_id_`),
  UNIQUE KEY `_utility_id__UNIQUE` (`_utility_id_`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `water_admin_table`
--

DROP TABLE IF EXISTS `water_admin_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `water_admin_table` (
  `_water_admin_id_` bigint NOT NULL,
  PRIMARY KEY (`_water_admin_id_`),
  UNIQUE KEY `_water_admin_id__UNIQUE` (`_water_admin_id_`),
  CONSTRAINT `water_admin_fk1` FOREIGN KEY (`_water_admin_id_`) REFERENCES `utility_admin_table` (`_utility_admin_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `water_table`
--

DROP TABLE IF EXISTS `water_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `water_table` (
  `_water_id_` bigint NOT NULL,
  PRIMARY KEY (`_water_id_`),
  UNIQUE KEY `_water_id__UNIQUE` (`_water_id_`),
  CONSTRAINT `water_utility_fk1` FOREIGN KEY (`_water_id_`) REFERENCES `utility_table` (`_utility_id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-12-06  2:11:06
