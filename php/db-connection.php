<?php
class DatabaseConnection {
    private $connections = [
        [
            'servername' => 'localhost:3306',
            'username'   => 'root',
            'password'   => '',
            'dbname'     => 'clix_database'
        ],
        [
            'servername' => 'localhost:3306',
            'username'   => 'if0_38156219',
            'password'   => 'VRfZfQjS4u0m',
            'dbname'     => 'clix_database'
        ],
        [
            'servername' => 'localhost:3306',
            'username'   => 'clix_user',
            'password'   => 'F9sqtmJx9kqj9FP',
            'dbname'     => 'clix_database'
        ]
    ];

    private $connection = null;

    private function tryConnection($config) {
        try {
            return mysqli_connect(
                $config['servername'],
                $config['username'],
                $config['password'],
                $config['dbname']
            );
        } catch (Exception $e) {
            return false;
        }
    }

    public function connect() {
        foreach ($this->connections as $config) {
            $this->connection = $this->tryConnection($config);
            if ($this->connection) {
                return true;
            }
        }
        return false;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function __destruct() {
        if ($this->connection) {
            mysqli_close($this->connection);
        }
    }
}

$db = new DatabaseConnection();
if (!$db->connect()) {
    die();
}
$conn = $db->getConnection();
