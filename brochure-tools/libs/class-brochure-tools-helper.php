<?php

/**
 * Helper functions used throughout plugin.
 * */
class Brochure_Tools_Helper {

    /**
     * Returns true if current Wordpress version supports wp_enqueue_script in HTML body (4 and above); false otherwise.
     * */
    static function wp_supports_enqueue_script_in_body() {
        global $wp_version;
        $version = explode('.', $wp_version);
        if (intval($version[0] < 4) || ( intval($version[0]) == 4 && intval($version[1]) < 4 )) {
            return false;
        }
        return true;
    }

    /**
     * Show generic notification message.
     * */
    static function show_notification($msg) {
        echo '<div class="updated"><p>' . $msg . '</p></div>';
    }

    /**
     * Show generic error message.
     * */
    static function show_error($msg) {
        echo '<div class="error"><p>' . $msg . '</p></div>';
    }

    /**
     * In case of API error (e.g. invalid API keys), display error message.
     * */
    static function show_api_error($ex, $prefix_msg = '') {
        $error_msg = $ex->getMessage();
        $msg = '<strong>' . __('Narnoo API error:', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN) . '</strong> ' . $prefix_msg . ' ' . $error_msg;
        if (false !== strchr(strtolower($error_msg), ' authentication fail')) {
            $msg .= '<br />' . sprintf(
                            __('Please ensure your API settings in the <strong><a href="%1$s">Settings->Narnoo API</a></strong> page are correct and try again.', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN), NARNOO_BROCHURE_TOOLS_SETTINGS_PAGE
            );
        }
        self::show_error($msg);
    }

    /**
     * Inits and returns distributor request object with user's access and secret keys.
     * If either app or secret key is empty, returns null.
     * */
    static function init_api($type = 'depga') {

        //Different types = Depga --> Distributor --> Operator Connect

        // update this to include the access_key secret_key and access_token
        $options = get_option('narnoo_distributor_settings');

        if (empty($options['access_key']) || empty($options['secret_key']) || empty($options['token_key']) ) {
            return null;
        }


        //Requests go here for DEPGA Tools
        if($type == 'depga'){

            $request = new Depgaconnect(
                    array(
                        "API-KEY: ".$options['access_key']."",
                        "API-SECRET-KEY: ".$options['secret_key']."",
                        "Authorization: ".$options['token_key'].""
                    ));
        
        } elseif( $type == 'operator') {
            
            $request = new Operatorconnect(
                array(
                        "API-KEY: ".$options['access_key']."",
                        "API-SECRET-KEY: ".$options['secret_key']."",
                        "Authorization: ".$options['token_key'].""
                    ));

        } elseif($type == 'distributor'){

            $request = new Distributor(
                array(
                        "API-KEY: ".$options['access_key']."",
                        "API-SECRET-KEY: ".$options['secret_key']."",
                        "Authorization: ".$options['token_key'].""
                    ));
        }
        



        return $request;
    }

    /**
    * Set up PHPFastCache attribute
    **/
    static function init_cache() {

        $cache = PHPFastCache( NARNOO_BROCHURE_TOOLS_PLUGIN_CACHE );
        $cache->setup("path",NARNOO_BROCHURE_TOOLS_PLUGIN_PATH.'/libs/cache/');
        return $cache;
    }

}