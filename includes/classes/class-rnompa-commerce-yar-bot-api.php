<?php

class RNOMPA_CommerceYarBotAPI {
    public function __construct() {
        //Service for getting shop data, store name, addres, logo(binary), users count, posts count, product counts, wordpress version, php version, short description, 
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
}