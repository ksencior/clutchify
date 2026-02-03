<?php
class Config {
    private static $settings = [];
    private static $instance = null;

    public static function init($pdo) {
        if (self::$instance === null) {
            try {
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    self::$settings[$row['setting_key']] = $row['setting_value'];
                }
                self::$instance = true;
            } catch (PDOException $e) {
                error_log("Błąd ładowania ustawień: " . $e->getMessage());
            }
        }
    }

    public static function get($key, $default = null) {
        return self::$settings[$key] ?? $default;
    }
}