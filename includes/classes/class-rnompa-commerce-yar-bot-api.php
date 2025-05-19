<?php

class RNOMPA_CommerceYarBotAPI {
    private $table_name = 'website_inputs';
    private $status_table = 'telba_status'; 
    public function __construct() {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'wc/v3', '/rnompa-statistics', array(
                'methods' => 'GET',
                // 'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_statistics'],
                // 'permission_callback' => function () {
                //     return current_user_can( 'manage_woocommerce' ); // Only allow logged-in users
                // },
            ) );
        } );

        add_action( 'rest_api_init', function () {
            register_rest_route( 'wc/v3', 'rnompa-unseen-notifications', array(
                'methods' => 'GET',
                'callback' => [$this, 'retrive_telegram_assistant_data'],
                'permission_callback' => function ($request) {
                    return current_user_can( 'manage_woocommerce' );
                },
            ) );
        } );
        add_action( 'rest_api_init', function () {
            register_rest_route( 'wc/v3', 'rnompa-site-information', array(
                'methods' => 'GET',
                'callback' => [$this, 'get_website_information'],
                'permission_callback' => function ($request) {
                    return current_user_can( 'manage_woocommerce' );
                },
            ) );
        } );
        add_action( 'rest_api_init', function () {
            register_rest_route( 'wc/v3', '/rnompa-create-coupon', array(
                'methods' => 'POST',
                'callback' => [$this, 'create_coupon'],
                'permission_callback' => function ($request) {
                    return current_user_can( 'manage_woocommerce' );
                },
            ) );
        } );
        add_action( 'rest_api_init', function () {
            register_rest_route( 'wc/v3', '/rnompa-daily-statistics', array(
                'methods' => 'GET',
                'callback' => [$this, 'get_daily_statistics'],
                'permission_callback' => function ($request) {
                    return current_user_can( 'manage_woocommerce' );
                },
            ) );
        } );
        add_action( 'init', [$this, 'set_visit'] );
        add_action('init', function() {
            $ip_addr = '';
        
            if ( !empty( $_SERVER['REMOTE_ADDR'] ) ) {
                $ip_addr = $_SERVER['REMOTE_ADDR'];
            }
           setcookie('sadri', $ip_addr,time() + 86400, '/') ;
        });
        add_action('admin_enqueue_scripts', [$this, 'my_theme_scripts']);
        // Handle AJAX for logged-in users
        add_action('wp_ajax_rnompa_generate_telegram_bot_string', [$this, 'handle_my_ajax_action']);

        // Handle AJAX for non-logged-in users
        add_action('wp_ajax_nopriv_rnompa_generate_telegram_bot_string', [$this, 'handle_my_ajax_action']);
        // add_action( 'admin_init', function() {
        //     // $request = new WP_REST_Request('GET', '/wc/v3/orders/2637');

        //     // $response = rest_do_request($request);
        //     // $server = rest_get_server();
        //     // $order = $server->response_to_data($response, false);
        //     echo "<pre>";
        //     // print_r( $order );
        //     // print_r( $order['date_created'] );
        //     // print_r( $order['status'] );


        //     $args = array('status' => array(),'id'=>[2367], 'limit' => -1, 'type' => 'shop_order');
        //     $orders = wc_get_orders($args);
        //     foreach ($orders as $order) {
        //         // Access order details here
        //         echo json_encode($order->data);
        //     }
            
        //     die();
        // } );
        add_action('admin_menu', [$this,'custom_settings_menu_item']);

        add_action( 'woocommerce_new_order', [$this, 'add_to_status_table'], 10, 1);
        add_action( 'comment_post', [$this, 'add_comment_to_status_table'], 10, 3 );

        //Service for getting shop data, store name, addres, logo(binary), users count, posts count, product counts, wordpress version, php version, short description, 
    }
    static function activate() {
        self::create_website_inputs_table();
        self::create_status_table();
    }
    function get_daily_statistics() {
        global $wpdb;
        global $wpdb;

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
    function get_website_information() {
        $user_count = count_users();
        $total_users = $user_count['total_users'];

        // Get the website title
        $website_title = get_bloginfo('name');

        // Get the number of posts
        $posts_count = wp_count_posts()->publish;

        // Get the number of WooCommerce products (if WooCommerce is active)
        if ( class_exists( 'WooCommerce' ) ) {
            $products_count = wp_count_posts('product')->publish;
        } else {
            $products_count = 'WooCommerce not active';
        }

        // Get the site's short description (tagline)
        $short_description = get_bloginfo('description');

        // Get the logo attachment ID
        $logo_id = get_theme_mod( 'custom_logo' );
        $logo_binary = '';
        if ( $logo_id ) {
            // Get the logo file path
            $logo_path = get_attached_file( $logo_id );

            if ( $logo_path && file_exists( $logo_path ) ) {
                // Read the logo file and convert it to binary data
                $logo_binary = file_get_contents( $logo_path );

                // Output the binary data (or use it as needed)
                header( 'Content-Type: application/octet-stream' );
            }
        }
        $logo = get_custom_logo();

        $logo_id = get_theme_mod( 'custom_logo' ); // Get the logo ID
        $logo_url = wp_get_attachment_image_src( $logo_id, 'full' ); // Get the logo URL
       
        
        // API endpoint (e.g., to fetch products)
        $endpoint = '/wp-json/wc/v3/system_status';

        // Site URL
        $site_url = get_site_url(); // Get the site URL dynamically

        // Full API URL
        $api_url = $site_url . $endpoint;

        // Basic Authentication for WooCommerce REST API
        $args = array(
            'headers' => array(
                'Authorization' => $_SERVER['HTTP_AUTHORIZATION']
            ),
        );

        // Make the API request
        $response = wp_remote_get($api_url, $args);
        $status_result = json_decode($response['body'], true);
        
        $store_address     = get_option( 'woocommerce_store_address' );
        $store_address_2   = get_option( 'woocommerce_store_address_2' );
        $store_city        = get_option( 'woocommerce_store_city' );
        $store_postcode    = get_option( 'woocommerce_store_postcode' );
        $store_country     = get_option( 'woocommerce_default_country' );

        return [
            'TotalUsers' => $total_users,
            'WebsiteTitle' => $website_title,
            'PostsCount' => $posts_count,
            'WordpressVersion' => $status_result['environment']['wp_version'],
            'PhpVersion' => $status_result['environment']['php_version'],
            'ShortDescription' => $short_description,
            'ProductsCount' => $products_count,
            'Logo' => $logo_url,
            'SiteUrl' => $status_result['environment']['home_url'],
            'WpMemoryLimit' => $status_result['environment']['wp_memory_limit'],
            'WpCron' => $status_result['environment']['wp_cron'],
            'PhpPostMaxSize' => $status_result['environment']['php_post_max_size'],
            "PhpMaxExecutionTime"=> $status_result['environment']['php_max_execution_time'],
            "PhpMaxInputVars"=> $status_result['environment']['php_max_input_vars'],
            "CurlVersion"=> $status_result['environment']['curl_version'],
            "MaxUploadSize" => $status_result['environment']['max_upload_size'],
            "MysqlVersion" => $status_result['environment']['mysql_version'],
            "DefaultTimezone" => $status_result['environment']['default_timezone'],
            "FsockopenOrCurlEnabled" => $status_result['environment']['fsockopen_or_curl_enabled'],
            "SoapclientEnabled" => $status_result['environment']['soapclient_enabled'],
            "DomdocumentEnabled" => $status_result['environment']['domdocument_enabled'],
            "GzipEnabled" => $status_result['environment']['gzip_enabled'],
            "WcDatabaseVersion" => $status_result['database']['wc_database_version'],
            "ActivePlugins" => $status_result['active_plugins'],
            "InactivePlugins" => $status_result['active_plugins'],
            'StoreAddress' => [
                'Address'   => $store_address,
                'Address2'  => $store_address_2,
                'City'      => $store_city,
                'PostCode'  => $store_postcode,
                'Country'   => $store_country,
            ],
        ];
    }
    function add_comment_to_status_table($comment_id, $comment_approved, $commentdata ) {
        global $wpdb;
        if ( 'product' === get_post_type( $commentdata['comment_post_ID'] ) ) {
            $sql = $wpdb->prepare(
                "INSERT INTO `{$wpdb->prefix}telba_status`(`post_id`, `post_type`) VALUES ('$comment_id','comment')"
            );
            $wpdb->query($sql);
        }
    }
    function handle_my_ajax_action() {
        // Verify nonce for security
        check_ajax_referer('rnompa_commerce_yar_bot', 'security');
    
        // Get data from the AJAX request
        $some_data = sanitize_text_field($_POST['telegram_bot_token']);
    
        // Process the data
        $response = 'Received: ' . $some_data;
        $woo_key = $this->create_woo_key();
        $base_url = get_site_url();
        $title = get_bloginfo('name');
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo = get_site_icon_url();
        // $string = $woo_key['consumer_key'].'&&'.$woo_key['consumer_secret'].'&&'.$base_url;
        // Send response
        wp_send_json( $this->imageToBase64($logo) );
       
        $response = wp_remote_post('https://www.commerceyar.ir/wp-json/commerceyar/v1/register', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'BotApiKey' => $some_data,
                'WPSiteLogo' => $this->imageToBase64($logo),
                'ConsumerSecret' => $woo_key['consumer_secret'],
                'ConsumerKey' => $woo_key['consumer_key'],
                'WPSiteTitle' => $title,
                'WPSiteUri' => $base_url
            ]),
        ]);

        // Always exit to avoid extra output
        wp_die();
    }
    // Function to convert image to Base64
    function imageToBase64($imageUrl) {
        // Check if the file exists
        // if (file_exists($imageUrl)) {
            // Get the image content
            $imageData = file_get_contents($imageUrl);
            // Encode the image data to Base64
            $base64 = base64_encode($imageData);
            // Get the image mime type
            $mimeType = mime_content_type($imageUrl);
            // Return the Base64 string with the appropriate data URI
            return 'data:' . $mimeType . ';base64,' . $base64;
        // } else {
            // return false; // Return false if the file does not exist
        // }
    }
    function encryptData($data, $key) {
        // Generate a random initialization vector (IV)
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($iv_length);
    
        // Encrypt the data
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    
        // Combine the IV and encrypted data for storage
        return base64_encode($iv . $encrypted);
    }
    function create_coupon($request) {
        if(class_exists('WC_Coupon_Data_Store_CPT')) {
            $result = '';
            $woocommerce_admin_meta_boxes_coupon = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
            $length = 8;
            
            for ($i = 0; $i < $length; $i++) {
                $result .= $woocommerce_admin_meta_boxes_coupon[rand(0, $length - 1)];
            }
            $cp = new WC_Coupon($result);
            $cp -> set_date_expires(time() + 3000000);
            $cp -> set_amount(10);
            $cp -> set_usage_limit(1);
            $coupon = new WC_Coupon_Data_Store_CPT() ;
            $coupon->create($cp);
            return $result;//
        }
        
        return ;
    }
    function custom_settings_menu_item() {
        add_options_page(
            'دستیار تلگرام', // Page title
            'دستیار تلگرام',      // Menu title
            'manage_options',       // Capability required to access
            'custom-settings-slug', // Menu slug
            [$this, 'custom_settings_page'] // Callback function to render the page
        );
    }

    function my_theme_scripts() {
        wp_enqueue_script('rnompa-ajax-script', get_template_directory_uri() . '/js/ajax-script.js', array('jquery'), null, true);
    
        // Localize the script with data
        wp_localize_script('rnompa-ajax-script', 'rnompa_rnompa_commerce_yar_bot', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('rnompa_commerce_yar_bot')
        ));
    }
    function custom_settings_page() {
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
                    // copyText.setSelectionRange(0, 99999); // For mobile devices
                    document.execCommand("copy");
                    alert("Copied: " + copyText.value);
                }
                function rnompa_generate_token() {
                    let token = jQuery('#rnompa-telegram-assistan-token').val();
                    if (!token) {
                        alert(__('Token can not be empty', 'rnompa-tg-bot'));
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
                            jQuery('#rnompa-telegram-assistant-hashed-data').val(response);
                        }
                    });
                }
            </script>

            <style>
            .copy-form {
                margin: 20px 0;
            }

            .copy-form input {
                padding: 10px;
                width: 100%;
                /* margin-right: 10px; */
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            @media only screen and (min-width:768px) {
                .copy-form input {
                    width: 66%;
                }
            }

            .copy-form button {
                padding: 10px 15px;
                background-color: #0073aa;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }

            .copy-form button:hover {
                background-color: #005177;
            }
            </style>
        <?php
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
    function retrive_telegram_assistant_data() {
        global $wpdb;
        $output = [];
        $table_name = $wpdb->prefix . $this->status_table;
        
        try{  
            // Output the product
            $sql = $wpdb -> prepare( "SELECT * FROM `$table_name` WHERE `is_checked` = '0'" );
            $results = $wpdb->get_results($sql);
            foreach ($results as $key => $value) {
                $args = array('id'=>[$value->id], 'limit' => -1, 'type' => 'shop_order');
                if( 'order' == $value->post_type ) {
                    $output[] = [
                        'id'    => $value -> post_id,
                        'type'  => 'order',
                        'date'  => wc_rest_prepare_date_response($value->created_at)// gmdate( 'Y-m-d H:i:s', strtotime($value->created_at) )//
                    ];
                } else if ( 'comment' == $value->post_type ) {
                    $comment_link = get_comment_link( $value -> post_id );
                    $comment = get_comment( $value -> post_id );
                    $product = wc_get_product($comment->comment_post_ID);
                    $output[] = [
                        'id'    => $comment->comment_post_ID,
                        'type'  => 'comment',
                        'Comment' => [
                            'Id' => $value -> post_id,
                            'Link' => $comment_link,
                            'Rating' => get_comment_meta($value -> post_id, 'rating', true),
                            'ProductName' => $product -> get_name(),
                            'AdminPanelLink' =>  admin_url('comment.php?action=editcomment&c=' . $value -> post_id),
                            'Comment' => $comment->comment_content
                        ],
                        'date'  => wc_rest_prepare_date_response($value->created_at)// gmdate( 'Y-m-d H:i:s', strtotime($value->created_at) )//
                    ];
                }
                try {
                    $update_sql = $wpdb -> prepare( "UPDATE `$table_name` SET `is_checked`='1' WHERE `post_id` = '{$value -> post_id}'" );
                    $wpdb -> query( $update_sql );
                } catch (Exception $ex) {}
            }
        } catch(Exception $ex){
            return $ex->getMessage();
        }
        
        return $output;
    }

    static function create_website_inputs_table() {
        global $wpdb;
        $instance = new self();
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . $instance->table_name;
        $sql = "CREATE TABLE `$table_name` (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            input_type VARCHAR(255) NOT NULL,
            ip_addr VARCHAR(20) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    static function create_status_table() {
        global $wpdb;
        $instance = new self();
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . $instance->status_table;
        $sql = "CREATE TABLE `$table_name` (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id bigint UNSIGNED NOT NULL DEFAULT '0',
            post_type VARCHAR(20) NOT NULL,
            is_checked TINYINT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    function add_to_status_table($order_id) {
        global $wpdb;
        $sql = $wpdb->prepare(
            "INSERT INTO `{$wpdb->prefix}telba_status`(`post_id`, `post_type`) VALUES ('$order_id','order')"
        );
        $wpdb->query($sql);
    }
    
    function get_statistics($request) {
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

    function set_visit() {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $ip_addr = '';
        
        if ( !empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip_addr = $_SERVER['REMOTE_ADDR'];
        }
      
        $string = json_encode($_SERVER);
        $sql = "SELECT count(*) from `$table_name` WHERE `ip_addr` = '$ip_addr' AND LEFT(`created_at`, 10) = LEFT(NOW(), 10) ";

        $result = $wpdb->get_col($sql)[0] ;
        if ($result > 0) {
            return;
        }
        if ( ! empty( $_SERVER['HTTP_BOT_VERSION'] ) ) {
            $sql = "INSERT INTO `$table_name`(`input_type`, `ip_addr`) VALUES ('telegram_bot', '$ip_addr')";
            try {
                $wpdb->query( $sql );
            } catch( Exception $ex ) {}
            return;
        } else if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
            $google_re = '/google.com/m';
            $torob_re = '/torob.com/m';
            $str = $_SERVER['HTTP_REFERER'];

            preg_match_all($google_re, $str, $google_matches, PREG_SET_ORDER, 0);
            preg_match_all($torob_re, $str, $torob_matches, PREG_SET_ORDER, 0);
            if ( count( $google_matches ) > 0 ) {
                $sql = "INSERT INTO `$table_name`(`input_type`, `ip_addr`) VALUES ('google_input', '$ip_addr')";
                try {
                    $wpdb->query( $sql );
                } catch( Exception $ex ) {}
                return;
            } else if ( count( $torob_matches ) > 0 ) {
                $sql = "INSERT INTO `$table_name`(`input_type`, `ip_addr`) VALUES ('torob', '$ip_addr')";
                try {
                    $wpdb->query( $sql );
                } catch( Exception $ex ) {}
                return;
            } else {
                $sql = "INSERT INTO `$table_name`(`input_type`, `ip_addr`) VALUES ('$str', '$ip_addr')";
                try {
                    $wpdb->query( $sql );
                } catch( Exception $ex ) {}
                return;
            }
        } else {
            $sql = "INSERT INTO `$table_name`(`input_type`, `ip_addr`) VALUES ('visit', '$ip_addr')";
            try {
                $wpdb->query( $sql );
            } catch( Exception $ex ) {}
        }
    }

    function get_woocommerce_orders_count($time_period) {
        $args = array(
            'status' => array('wc-completed', 'wc-processing', 'wc-on-hold'), // Include the order statuses you want to count
            'date_created' => $time_period,
            'limit' => -1, // No limit on the number of orders to retrieve
        );
    
        $orders = wc_get_orders($args);

        $args = array(
            'status' => array('wc-completed', 'wc-processing', 'wc-on-hold'), // Include the order statuses you want to count
            'date_created' => $time_period,
            'limit' => -1, // No limit on the number of orders to retrieve
        );
        
        $orders = wc_get_orders($args);
        
        $order_count = 0;
        $total_sold = 0;
        
        foreach ($orders as $order) {
            $order_count++;
            $total_sold += $order->get_total(); // Get the total amount for each order
        }
        return ['count' => $order_count, 'total' => $total_sold];
    }

}