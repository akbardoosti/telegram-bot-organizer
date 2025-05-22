<?php

class RNOMPA_Table_Manager {
    private static $website_inputs_table = 'website_inputs';
    private static $status_table = 'telba_status';

    public static function activate() {
        self::create_website_inputs_table();
        self::create_status_table();
    }

    public static function init() {
        add_action('init', [__CLASS__, 'set_visit']);
        add_action('woocommerce_new_order', [__CLASS__, 'add_to_status_table'], 10, 1);
        add_action('comment_post', [__CLASS__, 'add_comment_to_status_table'], 10, 3);

        // Set cookie for IP
        add_action('init', function() {
            $ip_addr = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
            setcookie('sadri', $ip_addr, time() + 86400, '/');
        });
    }

    public static function create_website_inputs_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$website_inputs_table;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE `$table_name` (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            input_type VARCHAR(255) NOT NULL,
            ip_addr VARCHAR(20) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function create_status_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$status_table;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE `$table_name` (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT UNSIGNED NOT NULL DEFAULT '0',
            post_type VARCHAR(20) NOT NULL,
            is_checked TINYINT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function set_visit() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$website_inputs_table;
        $ip_addr = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';

        // Try cache for today's visit count
        $cache_key = 'rnompa_visit_' . md5($ip_addr . date('Y-m-d'));
        $result = wp_cache_get($cache_key, 'rnompa');
        if (false === $result) {
            $query = $wpdb->prepare(
                "SELECT count(*) FROM `$table_name` WHERE `ip_addr` = %s AND LEFT(`created_at`, 10) = LEFT(NOW(), 10)", $ip_addr
            );
            $result = $wpdb->get_var($query);
            wp_cache_set($cache_key, $result, 'rnompa', 60); // cache for 60 seconds
        }

        if ($result > 0) return;

        if (!empty($_SERVER['HTTP_BOT_VERSION'])) {
            $wpdb->insert($table_name, [
                'input_type' => 'telegram_bot',
                'ip_addr' => $ip_addr
            ]);
            return;
        } elseif (!empty($_SERVER['HTTP_REFERER'])) {
            $str = isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
            if (preg_match('/google\.com/', $str)) {
                $type = 'google_input';
            } elseif (preg_match('/torob\.com/', $str)) {
                $type = 'torob';
            } else {
                $type = $str;
            }
            $wpdb->insert($table_name, [
                'input_type' => $type,
                'ip_addr' => $ip_addr
            ]);
        } else {
            $wpdb->insert($table_name, [
                'input_type' => 'visit',
                'ip_addr' => $ip_addr
            ]);
        }
    }

    public static function add_to_status_table($order_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$status_table;
        // Caching is not practical for insert/update, so we leave this as is.
        $wpdb->insert($table_name, [
            'post_id' => $order_id,
            'post_type' => 'order'
        ]);
    }

    public static function add_comment_to_status_table($comment_id, $comment_approved, $commentdata) {
        global $wpdb;
        if ('product' === get_post_type($commentdata['comment_post_ID'])) {
            $table_name = $wpdb->prefix . self::$status_table;
            $wpdb->insert($table_name, [
                'post_id' => $comment_id,
                'post_type' => 'comment'
            ]);
        }
    }
}
