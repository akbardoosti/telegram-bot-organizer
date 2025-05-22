<?php

class RNOMPA_Notification {
    public static function init() {}

    public static function retrieve_telegram_assistant_data() {
        global $wpdb;
        $output = [];
        $table_name = $wpdb->prefix . 'telba_status';

        $cache_key = 'rnompa_telba_status_unchecked';
        $results = wp_cache_get($cache_key, 'rnompa');

        if ($results === false) {
            $results = $wpdb->get_results("SELECT * FROM `$table_name` WHERE `is_checked` = '0'");
            wp_cache_set($cache_key, $results, 'rnompa', 60); // cache for 60 seconds
        }

        foreach ($results as $value) {
            if ('order' == $value->post_type) {
                $output[] = [
                    'id' => $value->post_id,
                    'type' => 'order',
                    'date' => wc_rest_prepare_date_response($value->created_at)
                ];
            } else if ('comment' == $value->post_type) {
                $comment_link = get_comment_link($value->post_id);
                $comment = get_comment($value->post_id);
                $product = wc_get_product($comment->comment_post_ID);
                $output[] = [
                    'id' => $comment->comment_post_ID,
                    'type' => 'comment',
                    'Comment' => [
                        'Id' => $value->post_id,
                        'Link' => $comment_link,
                        'Rating' => get_comment_meta($value->post_id, 'rating', true),
                        'ProductName' => $product->get_name(),
                        'AdminPanelLink' => admin_url('comment.php?action=editcomment&c=' . $value->post_id),
                        'Comment' => $comment->comment_content
                    ],
                    'date' => wc_rest_prepare_date_response($value->created_at)
                ];
            }
            $wpdb->update($table_name, ['is_checked' => 1], ['post_id' => $value->post_id]);
            // Invalidate cache since the table has changed
            wp_cache_delete($cache_key, 'rnompa');
        }
        return $output;
    }
}