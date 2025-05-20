<?php

class RNOMPA_Statistics {
    public static function init() {
        // Nothing to hook directly, endpoints are registered in REST class
    }

    public static function get_daily_statistics() {
        global $wpdb;
        // For brevity, logic is same as original, but called as static
        // Return the same associative array as before
        // (Implementation omitted for brevity)


        // Get today's sales
        $today_sales = $wpdb->get_var("
            SELECT SUM(meta_value)
            FROM {$wpdb->posts} AS posts
            INNER JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
            AND meta.meta_key = '_order_total'
            AND DATE(posts.post_date) = CURDATE()
        ");
    
        // Get total sales
        $total_sales = $wpdb->get_var("
            SELECT SUM(meta_value)
            FROM {$wpdb->posts} AS posts
            INNER JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
            AND meta.meta_key = '_order_total'
        ");

        // Get today's order count
        $today_order_count = count(get_posts(array(
            'post_type'      => 'shop_order',
            'post_status'    => array('wc-completed', 'wc-processing', 'wc-on-hold'),
            'date_query'     => array(
                array(
                    'year'  => date('Y'),
                    'month' => date('m'),
                    'day'   => date('d'),
                ),
            ),
            'posts_per_page' => -1, // Get all orders
            'fields'         => 'ids', // Optimize query by only fetching IDs
        )));

        // Get total order count
        $total_order_count = count(get_posts(array(
            'post_type'      => 'shop_order',
            'post_status'    => array('wc-completed', 'wc-processing', 'wc-on-hold'),
            'posts_per_page' => -1, // Get all orders
            'fields'         => 'ids', // Optimize query by only fetching IDs
        )));


        // Get today's comment count
        $today_comment_count = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->comments}
            WHERE DATE(comment_date) = CURDATE()
        ");

        // Get total comment count
        $total_comment_count = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->comments}
        ");
    
        // Get today's product count
        $today_product_count = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_type = 'product'
            AND DATE(post_date) = CURDATE()
        ");

        // Get total product count
        $total_product_count = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_type = 'product'
        ");

         // Get today's customer count (for 'customer' role)
        $today_customer_count = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->users} AS users
            INNER JOIN {$wpdb->usermeta} AS meta ON users.ID = meta.user_id
            WHERE meta.meta_key = '{$wpdb->prefix}capabilities'
            AND meta.meta_value LIKE '%customer%'
            AND DATE(users.user_registered) = CURDATE()
        ");

        // Get total customer count (for 'customer' role)
        $total_customer_count = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->users} AS users
            INNER JOIN {$wpdb->usermeta} AS meta ON users.ID = meta.user_id
            WHERE meta.meta_key = '{$wpdb->prefix}capabilities'
            AND meta.meta_value LIKE '%customer%'
        ");

        $today_visit_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM `{$wpdb->prefix}website_inputs` WHERE DATE({$wpdb->prefix}website_inputs.created_at) = CURDATE()"
        );
        $total_visit_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM `{$wpdb->prefix}website_inputs`"
        );
        return array(
            'TodayOrderAmount' => $today_sales ? floatval($today_sales) : 0,
            'TotalOrderAmount' => $total_sales ? floatval($total_sales) : 0,
            'TodayOrderCount' => $today_order_count,
            'TotalOrderCount' => $total_order_count,
            'TodayCommentCount' => $today_comment_count ? intval($today_comment_count) : 0,
            'TotalCommentCount' => $total_comment_count ? intval($total_comment_count) : 0,
            'TodayProductCount' => $today_product_count ? intval($today_product_count) : 0,
            'TotalProductCount' => $total_product_count ? intval($total_product_count) : 0,
            'TodayCustomerCount' => $today_customer_count ? intval($today_customer_count) : 0,
            'TotalCustomerCount' => $total_customer_count ? intval($total_customer_count) : 0,
            'TodayVisitCount' => intval($today_visit_count),
            'TotalVisitCount' => intval($total_visit_count)
        );
    }

    public static function get_statistics($request) {
        // ... (copy logic from get_statistics method, adjust for static context)
        // Use $params as input
        // Return the same associative array as before
        // (Implementation omitted for brevity)
        global $wpdb;
        $params = $request->get_params();
        $types = ['visit', 'order', 'google_input'];
        $date_min = 0;
        $minDateTime = '';
        $date_max = time();
        $maxDateTime = '';
        if ( ! empty( $params['date_min'] ) ) {
            $date_min = $params['date_min'];
            // Create a DateTime object from the time string
            try {
                $minDateTime = new DateTime($date_min);
            } catch (Exception $e) {
                // Handle invalid time string
                return [
                    'error' => "Invalid date_min: " . $e->getMessage()
                ];
            }
        } else {
            return [
                'error' => "date_min is invalid "
            ];
        }
        if ( ! empty( $params['date_max'] ) ) {
            $date_max = $params['date_max'];
            // Create a DateTime object from the time string
            try {
                $maxDateTime = new DateTime($date_max);
            } catch (Exception $e) {
                // Handle invalid time string
                return [
                    'error' => "Invalid date_max: " . $e->getMessage()
                ];
            }
        } else {
            return [
                'error' => "date_max is invalid: "
            ];
        }

        $table_name = $wpdb->prefix . "website_inputs";
        $google_sql = "SELECT COUNT(*) FROM `$table_name` WHERE `input_type` ='google_input' AND ( LEFT(created_at, 10) >= '$date_min' AND LEFT(created_at, 10) < '$date_max')";
        $torob_sql = "SELECT COUNT(*) FROM `$table_name` WHERE `input_type` ='torob' AND ( LEFT(created_at, 10) >= '$date_min' AND LEFT(created_at, 10) < '$date_max')";
        $telegram_bot_sql = "SELECT COUNT(*) FROM `$table_name` WHERE `input_type` ='telegram_bot' AND ( LEFT(created_at, 10) >= '$date_min' AND LEFT(created_at, 10) < '$date_max')";

        $google_result = 0;
        $torob_result = 0;
        $telebram_bot_result = 0;

        try {
            $google_result = $wpdb -> get_col($google_sql)[0];
            $torob_result = $wpdb -> get_col($torob_sql)[0];
            $telebram_bot_result = $wpdb -> get_col($telegram_bot_sql)[0];
        } catch (Exception $th) {}
        return [
            [
                'ViewSourceType' => 'google',
                'ViewSourceTypeTitle' => 'گوگل',
                'Count' => intval($google_result),
            ],
            [
                'ViewSourceType' => 'Torob',
                'ViewSourceTypeTitle' => 'ترب',
                'Count' => intval($torob_result)
            ],
            [
                'ViewSourceType' => 'telegram_bot',
                'ViewSourceTypeTitle' => 'ربات تلگرام',
                'Count' => intval($telebram_bot_result)
            ]
        ];
    }
}