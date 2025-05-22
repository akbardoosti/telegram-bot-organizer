<?php

class RNOMPA_Site_Info {
    public static function init() {}

    public static function get_website_information() {
        // ... (copy logic from get_website_information, adjust for static context)
        // Use static function
        // (Implementation omitted for brevity)

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
                'Authorization' => isset($_SERVER['HTTP_AUTHORIZATION']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_AUTHORIZATION'])) : ''
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

    public static function imageToBase64($imageUrl) {
        $imageData = file_get_contents($imageUrl);
        $base64 = base64_encode($imageData);
        $mimeType = mime_content_type($imageUrl);
        return 'data:' . $mimeType . ';base64,' . $base64;
    }
}