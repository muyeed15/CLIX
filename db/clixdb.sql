CREATE DATABASE clixdb;
USE clixdb;

CREATE TABLE user_t (
	_nid_ BIGINT NOT NULL,
	_first_name_ VARCHAR(255) NOT NULL,
	_last_name_ VARCHAR(255) NOT NULL,
	_email_ VARCHAR(255) NOT NULL,
	_phone_ VARCHAR(20) NOT NULL,
	_address_ VARCHAR(255) NOT NULL,
	_password_ VARCHAR(255) NOT NULL,
	_picture_ BLOB,
	PRIMARY KEY (_nid_),
	UNIQUE KEY _email_ (_email_),
	UNIQUE KEY _phone_ (_phone_)
);

CREATE TABLE iot_utility_t (
	_iot_id_ BIGINT NOT NULL,
	_label_ VARCHAR(255),
	_latitude_ DECIMAL(9,6) NOT NULL,
	_longitude_ DECIMAL(9,6) NOT NULL,
	_cost_per_unit_ DECIMAL(10,2) NOT NULL,
	_status_ VARCHAR(50),
    _type_ VARCHAR(50) NOT NULL,
	PRIMARY KEY (_iot_id_)
);

CREATE TABLE usage_t (
	_usage_id_ BIGINT NOT NULL AUTO_INCREMENT,
	_nid_ BIGINT NOT NULL,
	_iot_id_ BIGINT NOT NULL,
	_time_ TIME NOT NULL,
	_date_ DATE NOT NULL,
	_usage_amount_ DECIMAL(10,2) NOT NULL,
	PRIMARY KEY (_usage_id_),
	KEY _iot_id_ (_iot_id_),
	CONSTRAINT usage_t_ibfk_1 FOREIGN KEY (_nid_) REFERENCES user_t (_nid_),
	CONSTRAINT usage_t_ibfk_2 FOREIGN KEY (_iot_id_) REFERENCES iot_utility_t (_iot_id_)
);

CREATE TABLE balance_t (
	_balance_id_ BIGINT NOT NULL AUTO_INCREMENT,
	_nid_ BIGINT NOT NULL,
	_iot_id_ BIGINT NOT NULL,
	_balance_ DECIMAL(10,2) NOT NULL,
	PRIMARY KEY (_balance_id_),
	KEY _iot_id_ (_iot_id_),
	CONSTRAINT balance_t_ibfk_1 FOREIGN KEY (_nid_) REFERENCES user_t (_nid_),
	CONSTRAINT balance_t_ibfk_2 FOREIGN KEY (_iot_id_) REFERENCES iot_utility_t (_iot_id_)
);

CREATE TABLE recharge_t (
	_recharge_id_ BIGINT NOT NULL AUTO_INCREMENT,
	_nid_ BIGINT NOT NULL,
	_iot_id_ BIGINT NOT NULL,
	_recharge_amount_ DECIMAL(10,2) NOT NULL,
	_time_ TIME NOT NULL,
	_date_ DATE NOT NULL,
	PRIMARY KEY (_recharge_id_),
	KEY _nid_ (_nid_),
	KEY _iot_id_ (_iot_id_),
	CONSTRAINT recharge_t_ibfk_1 FOREIGN KEY (_nid_) REFERENCES user_t (_nid_),
	CONSTRAINT recharge_t_ibfk_2 FOREIGN KEY (_iot_id_) REFERENCES iot_utility_t (_iot_id_)
);

CREATE TABLE outage_t (
	_outage_id_ BIGINT NOT NULL AUTO_INCREMENT,
	_time_start_ TIME NOT NULL,
	_time_end_ TIME NOT NULL,
	_date_start_ DATE NOT NULL,
	_date_end_ DATE NOT NULL,
	_area_ VARCHAR(255) NOT NULL,
	_latitude_ DECIMAL(9,6) NOT NULL,
	_longitude_ DECIMAL(9,6) NOT NULL,
	_range_ BIGINT NOT NULL,
    _type_ VARCHAR(50) NOT NULL,
	PRIMARY KEY (_outage_id_)
);

CREATE TABLE notification_t (
	_notification_id_ BIGINT NOT NULL AUTO_INCREMENT,
	_nid_ BIGINT,
	_outage_id_ BIGINT,
	_time_ TIME NOT NULL,
	_date_ DATE NOT NULL,
	_header_ VARCHAR(255) NOT NULL,
	_message_ TEXT NOT NULL,
	_type_ VARCHAR(50) NOT NULL,
	PRIMARY KEY (_notification_id_),
	KEY _nid_ (_nid_),
	KEY _outage_id_ (_outage_id_),
	CONSTRAINT notification_t_ibfk_1 FOREIGN KEY (_nid_) REFERENCES user_t (_nid_),
	CONSTRAINT notification_t_ibfk_2 FOREIGN KEY (_outage_id_) REFERENCES outage_t (_outage_id_)
);

INSERT INTO user_t (_nid_, _first_name_, _last_name_, _email_, _phone_, _address_, _password_, _picture_) VALUES 
(1748123456, 'Nafisa', 'Anzum Dipra', 'nafisa.dipra@example.com', '01710000001', 'House 12, Road 7, Dhanmondi, Dhaka', 'nafisa123', NULL),
(2889123457, 'A.H.M.', 'Imtiaj', 'imtiaj@example.com', '01710000002', 'House 34, Road 12, Banani, Dhaka', 'imtiaj456', NULL),
(3123459876, 'Ishrak', 'Alam', 'ishrak.alam@example.com', '01710000003', 'House 56, Road 8, Gulshan, Dhaka', 'ishrak789', NULL),
(4547890123, 'Shrabon', 'Das', 'shrabon.das@example.com', '01710000004', 'House 78, Road 15, Mirpur, Dhaka', 'shrabon012', NULL);

INSERT INTO iot_utility_t (_iot_id_, _label_, _type_, _latitude_, _longitude_, _cost_per_unit_, _status_) VALUES 
(300000000001, 'Electricity Meter - 4th Floor', 'Electricity', 23.746466, 90.376015, 7.00, 'Active'),
(100000000001, 'Gas Meter - 1st Floor', 'Gas', 23.780887, 90.279237, 12.50, 'Inactive'),
(200000000001, 'Water Meter - 3rd Floor', 'Water', 23.792496, 90.407806, 3.50, 'Active'),
(300000000002, 'Electricity Meter - 7th Floor', 'Electricity', 23.812528, 90.422827, 6.75, 'Inactive'),
(100000000002, 'Gas Meter - 6th Floor', 'Gas', 23.874876, 90.379645, 10.50, 'Active');

INSERT INTO usage_t (_nid_, _iot_id_, _time_, _date_, _usage_amount_) VALUES 
(1748123456, 300000000001, '08:30:00', '2024-01-01', 5.50),
(1748123456, 100000000001, '10:00:00', '2024-01-01', 3.25),
(1748123456, 200000000001, '15:00:00', '2024-01-01', 2.75),
(1748123456, 300000000001, '20:30:00', '2024-01-02', 6.50),
(1748123456, 100000000001, '09:15:00', '2024-01-03', 3.00),
(2889123457, 100000000001, '11:45:00', '2024-01-01', 4.25),
(2889123457, 200000000001, '12:00:00', '2024-01-01', 1.75),
(2889123457, 200000000001, '18:30:00', '2024-01-02', 2.00),
(3123459876, 300000000001, '07:30:00', '2024-01-01', 6.00),
(3123459876, 200000000001, '10:00:00', '2024-01-02', 1.50),
(3123459876, 300000000001, '09:00:00', '2024-01-03', 4.00),
(4547890123, 100000000001, '13:30:00', '2024-01-01', 3.75),
(4547890123, 300000000002, '14:30:00', '2024-01-02', 4.25),
(4547890123, 100000000002, '17:00:00', '2024-01-03', 5.00);

INSERT INTO balance_t (_nid_, _iot_id_, _balance_) VALUES 
(1748123456, 300000000001, 1500.00),
(1748123456, 100000000001, 1150.00),
(1748123456, 200000000001, 150.00),
(2889123457, 100000000001, 1300.00),
(2889123457, 200000000001, 180.00),
(3123459876, 300000000001, 1400.00),
(3123459876, 200000000001, 460.00),
(4547890123, 100000000001, 1250.00),
(4547890123, 300000000002, 2100.00),
(4547890123, 100000000002, 190.00);

INSERT INTO recharge_t (_nid_, _iot_id_, _recharge_amount_, _time_, _date_) VALUES 
(1748123456, 300000000001, 1200.00, '10:00:00', '2024-01-01'),
(4547890123, 300000000002, 1300.00, '11:00:00', '2024-01-03'),
(1748123456, 300000000001, 1150.00, '09:30:00', '2024-01-03'),
(2889123457, 200000000001, 1100.00, '12:00:00', '2024-01-01'),
(4547890123, 300000000002, 2250.00, '08:30:00', '2024-01-01'),
(2889123457, 200000000001, 1300.00, '16:30:00', '2024-01-02'),
(3123459876, 100000000001, 1180.00, '18:45:00', '2024-01-01'),
(3123459876, 100000000001, 1220.00, '10:15:00', '2024-01-02'),
(1748123456, 100000000002, 1250.00, '15:00:00', '2024-01-02');

INSERT INTO outage_t (_time_start_, _time_end_, _date_start_, _date_end_, _area_, _latitude_, _longitude_, _range_, _type_) VALUES
('12:00:00', '14:00:00', '2024-01-02', '2024-01-02', 'Dhanmondi, Dhaka', 23.746466, 90.376015, 100, 'Gas'),
('10:00:00', '13:00:00', '2024-01-02', '2024-01-02', 'Banani, Dhaka', 23.780887, 90.279237, 200, 'Gas'),
('15:00:00', '16:00:00', '2024-01-03', '2024-01-03', 'Gulshan, Dhaka', 23.792496, 90.407806, 150, 'Water'),
('09:00:00', '11:00:00', '2024-01-03', '2024-01-03', 'Mirpur, Dhaka', 23.812528, 90.422827, 250, 'Electricity'),
('13:00:00', '15:00:00', '2024-01-04', '2024-01-04', 'Uttara, Dhaka', 23.874876, 90.379645, 300, 'Water');

INSERT INTO notification_t (_nid_, _outage_id_, _time_, _date_, _type_, _header_, _message_) VALUES 
(1748123456, NULL, '08:30:00', '2024-01-01', 'Billing Reminder', 'Electricity Bill Due', 'Your electricity bill is due tomorrow.'),
(1748123456, 2, '09:45:00', '2024-01-02', 'Outage Alert', 'Scheduled Gas Outage', 'Gas service will be disrupted from 10:00 AM to 1:00 PM.'),
(1748123456, NULL, '10:30:00', '2024-01-03', 'Resource Alert', 'High Water Usage', 'Your water usage is higher than usual today.'),
(2889123457, NULL, '11:00:00', '2024-01-01', 'Maintenance Tip', 'Gas Maintenance', 'Check your gas connections for leaks.'),
(2889123457, 4, '14:00:00', '2024-01-03', 'Outage Alert', 'Electricity Maintenance', 'Electricity service will be interrupted from 9:00 AM to 11:00 AM.');