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
        $saved_token = get_option('rnompa_commerceyar_token', '');
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
                            value="<?php echo esc_attr($saved_token); ?>"
                            autocomplete="off"
                            dir="ltr"
                    />
                </div>
                <button type="button" id="rnompa-commerceyar-send" class="rnompa-btn-primary">
                    ارسال توکن به سرور
                </button>
                <div id="rnompa-token-result" class="rnompa-token-result" style="<?php echo empty($saved_token) ? 'display:none;' : ''; ?>">
                    <p>توکن دریافتی از سرور:</p>
                    <div class="rnompa-copy-container">
                        <input type="text" id="rnompa-server-token" readonly value="<?php echo esc_attr($saved_token); ?>" dir="ltr" />
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
                    <a href="https://commerceyar.ir" target="_blank" style="color:#6f42c1;direction:ltr;">commerceyar.ir</a>
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

        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';

        $response_data = [];

        if (!empty($token)) {
            // Simulate sending the token to CommerceYar server and receiving a response
            $server_response_token = self::send_token_to_commerceyar($token);
            if (!$server_response_token) {
                wp_send_json_error(['message' => 'دریافت پاسخ از سرور با خطا مواجه شد.']);
            }
            // Encrypt and save to options
            $encrypted = self::encryptData($server_response_token, 'CommerceYar');
            update_option('rnompa_commerceyar_token', $encrypted);
            $response_data['token']   = $encrypted;
            $response_data['message'] = 'توکن با موفقیت ذخیره شد.';
        }

        wp_send_json_success($response_data);
    }

    // Simulate server call (replace this logic with real API call as necessary)
    private static function send_token_to_commerceyar($token) {
        // Here you should implement the real API call to CommerceYar server.
        // For demo, let’s assume the server returns the same token with a prefix.
        return 'CY-' . $token;
    }

    // Simple encryption (replace with secure encryption in real use)
    private static function encryptData($data, $key) {
        $encryption_key = hash('sha256', $key);
        $iv = substr(hash('sha256', $key . 'iv'), 0, 16);
        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $encryption_key, 0, $iv));
    }

    // Simple decryption (for displaying the token if you wish)
    public static function decryptData($data, $key) {
        $encryption_key = hash('sha256', $key);
        $iv = substr(hash('sha256', $key . 'iv'), 0, 16);
        return openssl_decrypt(base64_decode($data), 'AES-256-CBC', $encryption_key, 0, $iv);
    }
}

RNOMPA_Admin_Settings_Page::init();