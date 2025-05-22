<?php

class RNOMPA_Coupon {
    public static function init() {
        // No hooks, REST endpoint registered elsewhere
    }

    public static function create_coupon($request) {
        if(class_exists('WC_Coupon_Data_Store_CPT')) {
            $result = '';
            $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
            $length = 8;
            for ($i = 0; $i < $length; $i++) {
                $result .= $chars[wp_rand(0, strlen($chars) - 1)];
            }
            $cp = new WC_Coupon($result);
            $cp->set_date_expires(time() + 3000000);
            $cp->set_amount(10);
            $cp->set_usage_limit(1);
            $coupon = new WC_Coupon_Data_Store_CPT();
            $coupon->create($cp);
            return $result;
        }
        return;
    }
}