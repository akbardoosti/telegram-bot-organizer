<?php

class RNOMPA_Admin_Settings_Page {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_settings_page']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_action('wp_ajax_rnompa_save_commerceyar_token', [__CLASS__, 'ajax_save_commerceyar_token']);
    }

    public static function register_settings_page() {
        add_options_page(
            'تنظیمات کامرس یار', 'تنظیمات کامرس یار',
            'manage_options',
            'commerce-yar-setting-page',
            [__CLASS__, 'render_settings_page']
        );
    }

    public static function enqueue_assets($hook) {
        if ($hook !== 'settings_page_commerce-yar-setting-page') {
            return;
        }
        wp_enqueue_style('rnompa-admin-settings-style', plugin_dir_url(__FILE__) . 'css/admin-settings-style.css');
        wp_enqueue_script('rnompa-admin-settings-script', plugin_dir_url(__FILE__) . 'js/admin-settings-script.js', ['jquery'], null, true);
        wp_localize_script('rnompa-admin-settings-script', 'rnompa_settings', [
            'ajax_url'   => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('rnompa_commerceyar_token'),
        ]);
    }

    public static function render_settings_page() {
        $encrypted_token = get_option('rnompa_commerceyar_token', '');
        $decrypted_token = '';
        if (!empty($encrypted_token)) {
            $decrypted_token = self::decryptData($encrypted_token, 'CommerceYar');
        }
        ?>
        <div class="rnompa-settings-wrapper">
            <h1 class="rnompa-settings-title">تنظیمات کامرس یار</h1>
            <div class="rnompa-desc">
                <p>در صورتی که توکن ربات تلگرامی کامرس یار را دارید وارد کنید. این توکن برای اتصال سایت شما به سرویس کامرس یار استفاده می‌شود.</p>
            </div>
            <form id="rnompa-commerceyar-form" class="rnompa-settings-form" onsubmit="return false;">
                <div class="rnompa-form-group">
                    <label for="rnompa-commerceyar-token">توکن کامرس یار</label>
                    <input
                            type="text"
                            id="rnompa-commerceyar-token"
                            name="rnompa_commerceyar_token"
                            value=""
                            autocomplete="off"
                            dir="ltr"
                    />
                </div>
                <button type="button" id="rnompa-commerceyar-send" class="rnompa-btn-primary">
                    ارسال توکن به سرور
                </button>
                <div id="rnompa-token-result" class="rnompa-token-result" style="<?php echo empty($decrypted_token) ? 'display:none;' : ''; ?>">
                    <p>توکن دریافتی از سرور:</p>
                    <div class="rnompa-copy-container">
                        <input type="text" id="rnompa-server-token" readonly value="<?php echo esc_attr($decrypted_token); ?>" dir="ltr" />
                        <button type="button" id="rnompa-copy-token" class="rnompa-btn-secondary">کپی</button>
                    </div>
                </div>
                <div id="rnompa-message" class="rnompa-message"></div>
            </form>
            <div class="rnompa-footer-box">
                <h3>درباره ما</h3>
                <p>
                    کامرس یار ارائه‌دهنده راهکارهای یکپارچه‌سازی فروشگاه‌های اینترنتی با شبکه‌های پیام‌رسان است.<br>
                    برای دریافت راهنمایی یا پشتیبانی با ما در تماس باشید.<br>
                    <strong>وبسایت:</strong>
                    <a href="http://www.commerceyar.ir/" target="_blank" style="color:#6f42c1;direction:ltr;">commerceyar.ir</a>
                </p>
                <p>
                    <strong>تماس با پشتیبانی:</strong>
                    <br>
                    ایمیل: <a href="mailto:support@commerceyar.ir">support@commerceyar.ir</a>
                    <br>
                    تلگرام: <a href="https://t.me/commerceyar" target="_blank">@commerceyar</a>
                </p>
            </div>
        </div>
        <?php
    }

    public static function ajax_save_commerceyar_token() {
        check_ajax_referer('rnompa_commerceyar_token', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز.']);
        }

        $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';

        if (empty($token)) {
            wp_send_json_error(['message' => 'توکن وارد نشده است.']);
        }

        $server_token = self::send_token_to_commerceyar($token);
        if (!$server_token) {
            wp_send_json_error(['message' => 'دریافت پاسخ از سرور با خطا مواجه شد.']);
        } else if (empty($server_token['token'])) {
            wp_send_json_error(['message' => $server_token['message']]);
        }

        $encrypted = self::encryptData($server_token['token'], 'CommerceYar');
        update_option('rnompa_commerceyar_token', $encrypted);

        wp_send_json_success([
            'token'   => $server_token['token'],
            'message' => 'توکن با موفقیت ذخیره شد.'
        ]);
    }

    /**
     * Sends token to CommerceYar server like the logic in class-ajax.php.
     * @param string $token
     * @return string The received token from server or false on failure
     */
    public static function send_token_to_commerceyar(string $token) {
        // Gather data similar to RNOMPA_Ajax::handle_ajax_action
        $woo_key = function_exists('wc_rand_hash') ? (new RNOMPA_Ajax)->create_woo_key() : ['consumer_key' => '', 'consumer_secret' => ''];
        $base_url = get_site_url();
        $title = get_bloginfo('name');
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo = get_site_icon_url();
        $logo_base64 = class_exists('RNOMPA_Site_Info') ? RNOMPA_Site_Info::imageToBase64($logo) : '';

        // Prepare data
        $post_data = [
            'BotApiKey'      => $token,
            'WPSiteLogo'     => $logo_base64,
            'ConsumerSecret' => $woo_key['consumer_secret'],
            'ConsumerKey'    => $woo_key['consumer_key'],
            'WPSiteTitle'    => $title,
            'WPSiteUri'      => $base_url
        ];

        // Prepare the request arguments
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body'    => json_encode($post_data),
            'timeout' => 15,
            'method'  => 'POST',
        );

        // Make the request using WordPress HTTP API
        $response = wp_remote_post('http://www.commerceyar.ir/wp-json/commerceyar/v1/register', $args);

        if (is_wp_error($response)) {
            // Handle error
            $response_body = false;
            $http_code = 0;
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $http_code = wp_remote_retrieve_response_code($response);
        }

        $body = json_decode($response_body, true);
        if (empty($body['Token'])) {
            return ['token'=>null, 'message'=>$body['ErrorDescription']];
        }

        if (isset($body['Token'])) {
            return ['token'=>$body['Token'], 'message'=>null];
        }
        // For fallback, return raw body if looks like a token
        elseif (is_string($body) && strlen($body) > 10) {
            return $body;
        }

        return false;
    }

    // Simple encryption (replace with secure encryption in real use)
    public static function encryptData($data, $key) {
        $encryption_key = hash('sha256', $key);
        $iv = substr(hash('sha256', $key . 'iv'), 0, 16);
        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $encryption_key, 0, $iv));
    }

    // Simple decryption (for displaying the token)
    public static function decryptData($data, $key) {
        $encryption_key = hash('sha256', $key);
        $iv = substr(hash('sha256', $key . 'iv'), 0, 16);
        return openssl_decrypt(base64_decode($data), 'AES-256-CBC', $encryption_key, 0, $iv);
    }
}

RNOMPA_Admin_Settings_Page::init();