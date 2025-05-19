<?php

class RNOMPA_Admin_Settings_Page {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_settings_page']);
    }

    public static function register_settings_page() {
        add_options_page(
            'دستیار تلگرام', 'دستیار تلگرام', 
            'manage_options',
            'commerce-yar-setting-page', 
            [__CLASS__, 'render_settings_page']
        );
    }

    public static function render_settings_page() {
        ?>
        <h1>دستیار تلگرام</h1>
        <div class="wrap" style='background-color: #d8c8f5;padding: 10px;border-radius: 4px;'>
            <div class="copy-form" style="display:flex;flex-direction: column;">
                <label for="rnompa-telegram-assistan-token" style='font-weight: 900;'>
                    توکن کامرس یار
                </label>
                <input type="text" id="rnompa-telegram-assistan-token" name='rnompa_telegram_assistan_token'/>
                <button onClick='rnompa_generate_token()' style='margin-top:10px; align-self: baseline;'>
                    تایید توکن
                </button>
            </div>
            <input type='hidden' id="rnompa-telegram-assistant-hashed-data" name='rnompa_telegram_assistan_hashed_data' />
            <div class="copy-form">
                <button onclick="copyToClipboard()">کپی</button>
            </div>
        </div>
        <script>
            function copyToClipboard() {
                var copyText = document.getElementById("rnompa-telegram-assistant-hashed-data");
                copyText.select();
                document.execCommand("copy");
                alert("Copied: " + copyText.value);
            }
            function rnompa_generate_token() {
                let token = jQuery('#rnompa-telegram-assistan-token').val();
                if (!token) {
                    alert('Token can not be empty');
                    return;
                }
                jQuery.ajax({
                    type: "post",
                    url: rnompa_rnompa_commerce_yar_bot.ajax_url,
                    data: {
                        action: 'rnompa_generate_telegram_bot_string',
                        security: rnompa_rnompa_commerce_yar_bot.ajax_nonce,
                        telegram_bot_token: token
                    },
                    dataType: "json",
                    success: function (response) {
                        jQuery('#rnompa-telegram-assistant-hashed-data').val(response.result);
                    }
                });
            }
        </script>
        <style>
        .copy-form { margin: 20px 0; }
        .copy-form input { padding: 10px; width: 100%; border: 1px solid #ccc; border-radius: 4px; }
        @media only screen and (min-width:768px) { .copy-form input { width: 66%; } }
        .copy-form button { padding: 10px 15px; background-color: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .copy-form button:hover { background-color: #005177; }
        </style>
        <?php
    }
}