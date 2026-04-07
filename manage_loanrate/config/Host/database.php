<?php
/**
 * Database Configuration
 * ระบบจัดการอัตราเบี้ยประกัน
 */

define("DB_TYPE", "mysql");
define("DB_SERVER", "localhost");
define("DB_PORT", "3307");
define("DB_USERNAME", "zp12865_zp12865");
define("DB_PASSWORD", "sfcLF7SfUYeNgPRwWb7W");
define("DB_DATABASE_NAME", "zp12865_db_mida_leasing");

/**
 * Get database connection
 * @return PDO|null
 */
function getConnection() {
    try {
        $dsn = DB_TYPE . ":host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_DATABASE_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Get insurance company name by ID
 * @param string $id
 * @return string
 */
function getInsuranceCompanyName($id) {
    $companies = [
        '00' => 'ไม่มี (NO)',
        '01' => 'AIA',
        '02' => 'CHUBB',
        '03' => 'TLIFE'
    ];
    return $companies[$id] ?? 'Unknown';
}

/**
 * Get sex name by ID
 * @param string $id
 * @return string
 */
function getSexName($id) {
    return $id === '1' ? 'ชาย' : 'หญิง';
}

/**
 * Get status name
 * @param string $status
 * @return string
 */
function getStatusName($status) {
    return $status === 'A' ? 'Active' : 'Inactive';
}
