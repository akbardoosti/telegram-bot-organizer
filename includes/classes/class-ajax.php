<?php

class RNOMPA_Ajax {
    public static function init() {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_scripts']);
        add_action('wp_ajax_rnompa_generate_telegram_bot_string', [__CLASS__, 'handle_ajax_action']);
        add_action('wp_ajax_nopriv_rnompa_generate_telegram_bot_string', [__CLASS__, 'handle_ajax_action']);
    }

    public static function enqueue_scripts() {
        wp_enqueue_script('rnompa-ajax-script', get_template_directory_uri() . '/js/ajax-script.js', array('jquery'), null, true);
        wp_localize_script('rnompa-ajax-script', 'rnompa_rnompa_commerce_yar_bot', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('rnompa_commerce_yar_bot')
        ));
    }

    public static function handle_ajax_action() {
        // You can move the logic for WooCommerce key creation and image to base64 here
        // For brevity, just return a placeholder


         // Verify nonce for security
        check_ajax_referer('rnompa_commerce_yar_bot', 'security');
    
        // Get data from the AJAX request
        $some_data = isset($_POST['telegram_bot_token']) ? sanitize_text_field(wp_unslash($_POST['telegram_bot_token'])) : '';
    

        $woo_key = (new RNOMPA_Ajax)->create_woo_key();
        $base_url = get_site_url();
        $title = get_bloginfo('name');
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo = get_site_icon_url();
        // $string = $woo_key['consumer_key'].'&&'.$woo_key['consumer_secret'].'&&'.$base_url;
        // Send response
        wp_send_json( RNOMPA_Site_Info::imageToBase64($logo) );
       
        $response = wp_remote_post('https://www.commerceyar.ir/wp-json/commerceyar/v1/register', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'BotApiKey' => $some_data,
                'WPSiteLogo' => RNOMPA_Site_Info::imageToBase64($logo),
                'ConsumerSecret' => $woo_key['consumer_secret'],
                'ConsumerKey' => $woo_key['consumer_key'],
                'WPSiteTitle' => $title,
                'WPSiteUri' => $base_url
            ]),
        ]);

        // Always exit to avoid extra output
        wp_die();
    }
    function create_woo_key() {
        global $wpdb;
        $permissions = 'read_write';
        
       
        $user = wp_get_current_user();
        $description = 'Woocommerce api key for '.$user->data->display_name;
       
        $consumer_key    = 'ck_' . wc_rand_hash();
        $consumer_secret = 'cs_' . wc_rand_hash();
        $data = array(
            'user_id'         => $user->data->ID,
            'description'     => $description,
            'permissions'     => $permissions,
            'consumer_key'    => wc_api_hash( $consumer_key ),
            'consumer_secret' => $consumer_secret,
            'truncated_key'   => substr( $consumer_key, -7 ),
        );

        try {
            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_api_keys',
                $data,
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                )
            );
        }catch(Exception $ex) {
            return $ex->getMessage();
        }
        
        return [
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret
        ] ;
    }
}