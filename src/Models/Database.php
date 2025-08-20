<?php
namespace App\Models;

use Medoo\Medoo;

/**
 * Class Database
 * Quản lý kết nối CSDL bằng mẫu thiết kế Singleton.
 */
class Database {
    /**
     * @var Medoo|null Thể hiện (instance) của kết nối Medoo.
     */
    private static $instance = null;

    /**
     * Hàm khởi tạo private để ngăn việc tạo đối tượng từ bên ngoài.
     */
    private function __construct() {}

    /**
     * Ngăn việc clone đối tượng.
     */
    private function __clone() {}

    /**
     * Lấy về thể hiện duy nhất của kết nối CSDL.
     *
     * @return Medoo
     */
    public static function getInstance(): Medoo {
        if (self::$instance === null) {
            // File config.php cần được đặt ở thư mục gốc của dự án (cùng cấp với vendor)
            // require_once __DIR__ . '/../../config.php';

            self::$instance = new Medoo([
                'type'      => 'mysql',
                'host'      => \DB_HOST,      // Sử dụng hằng số từ config.php
                'database'  => \DB_NAME,
                'username'  => \DB_USER,
                'password'  => \DB_PASS,
                'charset'   => \DB_CHARSET,
                'collation' => \DB_COLLATION,
                'port'      => \DB_PORT,
                // 'prefix'    => DB_PREFIX,    // Thêm prefix nếu có
                // Tùy chọn để log lỗi
                'error' => \PDO::ERRMODE_EXCEPTION,
            ]);
        }
        return self::$instance;
    }
}
