<?php

class RNOMPA_Notification {
    public static function init() {}

    public static function retrieve_telegram_assistant_data() {
        global $wpdb;
        $output = [];
        $table_name = $wpdb->prefix . 'telba_status';

        // Try to use cache first
        $cache_key = 'rnompa_telba_status_unchecked';
        $results = wp_cache_get($cache_key, 'rnompa');
        if (false === $results) {
            // Use prepare for safe table name and value, though table name is safe via $wpdb->prefix
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE `is_checked` = %d",
                0
            );
            $results = $wpdb->get_results($query);
            wp_cache_set($cache_key, $results, 'rnompa', 30); // cache for 30 seconds
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
                        'ProductName' => $product ? $product->get_name() : '',
                        'AdminPanelLink' => admin_url('comment.php?action=editcomment&c=' . $value->post_id),
                        'Comment' => $comment->comment_content
                    ],
                    'date' => wc_rest_prepare_date_response($value->created_at)
                ];
            }
            // Update statement is allowed here, but consider cache invalidation if needed
            $wpdb->update($table_name, ['is_checked' => 1], ['post_id' => $value->post_id]);
            wp_cache_delete($cache_key, 'rnompa'); // Invalidate cache after update
        }
        return $output;
    }
}