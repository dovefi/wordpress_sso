<?php

//$sso_from="http://wp.dev.gdy.io/wordpress";
$sso_from="http://192.168.3.12/wordpress/wp-login.php";
$sso_login="http://sso.kuxiao.cn/sso";
$sso_info="http://sso.kuxiao.cn/sso/api/uinfo";
$sso_logout="http://sso.kuxiao.cn/sso/api/logout";

/**
 * redirect to sso login web
 */
function s_redirect_sso() {
    global $sso_from;
    global $sso_login; 
    header("Location:". $sso_login . "?url=".urlencode($sso_from));
}

/**
 * logout sso.....
 */
function s_sso_logout(){
    global $sso_logout;
    $token = $_COOKIE['sso_token'];
    error_log("excute sso logout ..token:." . $token);
    for ($i=0;$i<3;$i++) {
        try {
            file_get_contents($sso_logout."?token=".$token);
            break;
        } catch (Exception $e) {
        }
    }
    if (empty($token)) {
        echo "excute logout ... token empty";
    }
    return;
}

/**
 * get user info with sso token 
 * @param string $token
 * @return number[]|string[]|string[]|NULL[]|number[]|NULL[]
 */
function s_get_uInfo($token = ''){
    global $sso_info;
    error_log("excute get_uinfo ... token : " . $token);
    $res=nil;
    $err=nil;
    for ($i=0;$i<3;$i++) {
        try {
            $data=file_get_contents($sso_info."?token=".$token);
            $res=json_decode($data);
            $err=nil;
            break;
        } catch (Exception $e) {
            $err=$e->getMessage();
        }
    }
    if ($err!=nil) {
        return array(
            'code'             => -1,
            'error_msg'        => $err,
        );
    }
    if (!isset($res->code)) {
        return array(
            'code'             => -1,
            'error_msg'        => "not code",
        );
    }
    if ($res->code!=0) {
        error_log("code = ".$res->code);
        return array(
            'code'          => $res->code,
            'error_msg'     => "not code",
        );
    }
    if (!(isset($res->data)&&isset($res->data->usr)&&isset($res->data->usr->usr))) {
        return array(
            'code'             => -1,
            'error_msg'        => "not user",
        );
    }
    return array(
        'code'       => 0,
        #'usr'        => $res->data->usr->attrs->basic->nickName,
        #'usr'        => $res->data->usr->usr[0],
        'usr'        => $res->data->usr->account,
    );
}

/**
 * singn on user
 * @param object $u_exist
 * @param string $secure_cookie
 * @return WP_Error|WP_User|false|WP_Error
 */
function s_wp_signon( $u_exist = null, $secure_cookie = '' ) {
    if ( empty($credentials) ) {
        $credentials = array(); // Back-compat for plugins passing an empty string
        $credentials['user_login'] = $u_exist->user_login;
        $credentials['user_password'] = "";
        $credentials['remember'] = false;
    }
    if ( !empty($credentials['remember']) )
        $credentials['remember'] = true;
        else
            $credentials['remember'] = false;

            /**
             * Fires before the user is authenticated.
             *
             * The variables passed to the callbacks are passed by reference,
             * and can be modified by callback functions.
             *
             * @since 1.5.1
             *
             * @todo Decide whether to deprecate the wp_authenticate action.
             *
             * @param string $user_login    Username, passed by reference.
             * @param string $user_password User password, passed by reference.
             */
            do_action_ref_array( 'wp_authenticate', array( &$credentials['user_login'], &$credentials['user_password'] ) );

            if ( '' === $secure_cookie )
                $secure_cookie = is_ssl();

                /**
                 * Filters whether to use a secure sign-on cookie.
                 *
                 * @since 3.1.0
                 *
                 * @param bool  $secure_cookie Whether to use a secure sign-on cookie.
                 * @param array $credentials {
                 *     Array of entered sign-on data.
                 *
                 *     @type string $user_login    Username.
                 *     @type string $user_password Password entered.
                 *     @type bool   $remember      Whether to 'remember' the user. Increases the time
                 *                                 that the cookie will be kept. Default false.
                 * }
                 */
                $secure_cookie = apply_filters( 'secure_signon_cookie', $secure_cookie, $credentials );

                global $auth_secure_cookie; // XXX ugly hack to pass this to wp_authenticate_cookie
                $auth_secure_cookie = $secure_cookie;

                add_filter('authenticate', 'wp_authenticate_cookie', 30, 3);


                /* 用户登录验证 */
                //$user = wp_authenticate($credentials['user_login'], $credentials['user_password']);
                $user = get_user_by('login', $u_exist->user_login); 
                if ( is_wp_error($user) ) {
                    if ( $user->get_error_codes() == array('empty_username', 'empty_password') ) {
                        $user = new WP_Error('', '');
                    }
                    return $user;
                }

                wp_set_auth_cookie($user->ID, $credentials['remember'], $secure_cookie);
                /**
                 * Fires after the user has successfully logged in.
                 *
                 * @since 1.5.0
                 *
                 * @param string  $user_login Username.
                 * @param WP_User $user       WP_User object of the logged-in user.
                 */
                do_action( 'wp_login', $user->user_login, $user );
                return $user;
}

/**
 * check whether user log sso or not
 */
function s_check_login($ctoken = "") { 
    $token = $ctoken;
    error_log("token : " . $token);
    $result = s_get_uInfo($token);
    if ($result['code'] != 0) {
        error_log('get user info code is :' . $result['code'] . 'redirect to sso login and clear cookie');
        s_redirect_sso();
        //wp_clear_auth_cookie(); 
        exit();
    } else {
        error_log("user account : " . $result['usr']);
        global $wpdb;
        $query_name = 'kx' . $result['usr'];
        $u_exist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_login = %s", $query_name));
        //$u_exist = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_login = %s", "dovefi") );
        if ($u_exist != null){
            error_log("user is exist,user_email : " . $u_exist->user_email); 
        } else {
            error_log("user is no exist");
            // because login name must include character , so add 'kx' prefix
            $u_create_name = 'kx' . $result['usr'];
            $u_create_pwd = '';
            $u_create_email = $u_create_name . '@unset.com';
            $u_create_id = wpmu_create_user($u_create_name, $u_create_pwd, $u_create_email);
            if ( ! $u_create_id ) {
                error_log("add user fail .....");
                //$add_user_errors = new WP_Error( 'add_user_fail', __( 'Cannot add user.' ) );
                return null;
            } else {
                // Fires after a new user has been created
                $u_exist = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_login = %s", $u_create_name));
                do_action( 'network_user_new_created_user', $u_create_id );
    
                // create site for new user
                $blog = array(
                    'domain' => $u_create_name,
                    'title' => $u_create_name . "blog",
                    'email' => $u_create_email,
                );
                s_create_site($blog);
            }
    
        }
        return $u_exist;
    }
}

/**
 * auto create site when user add success
 * @param array $s_blog
 */
function s_create_site($s_blog = array()) {
    global $wpdb;
    $blog = $s_blog;
    $domain = '';
    if ( preg_match( '|^([a-zA-Z0-9-])+$|', $blog['domain'] ) )
        $domain = strtolower( $blog['domain'] );
        error_log("site-new.php : domain name $domain");
        // If not a subdomain install, make sure the domain isn't a reserved word
        if ( ! is_subdomain_install() ) {
            $subdirectory_reserved_names = get_subdirectory_reserved_names();
            error_log("site-new.php : subdirectory_reserved_names $subdirectory_reserved_names");
            if ( in_array( $domain, $subdirectory_reserved_names ) ) {
                wp_die(
                    /* translators: %s: reserved names list */
                    sprintf( __( 'The following words are reserved for use by WordPress functions and cannot be used as blog names: %s' ),
                        '<code>' . implode( '</code>, <code>', $subdirectory_reserved_names ) . '</code>'
                        )
                    );
            }
        }
    
        $title = $blog['title'];
    
        $meta = array(
            'public' => 1
        );
    
        // Handle translation install for the new site.
        if ( isset( $_POST['WPLANG'] ) ) {
            if ( '' === $_POST['WPLANG'] ) {
                $meta['WPLANG'] = ''; // en_US
            } elseif ( wp_can_install_language_pack() ) {
                $language = wp_download_language_pack( wp_unslash( $_POST['WPLANG'] ) );
                if ( $language ) {
                    $meta['WPLANG'] = $language;
                }
            }
        }
    
        if ( empty( $domain ) )
        wp_die( __( 'Missing or invalid site address.' ) );
    
        if ( isset( $blog['email'] ) && '' === trim( $blog['email'] ) ) {
            wp_die( __( 'Missing email address.' ) );
        }
    
        $email = sanitize_email( $blog['email'] );
        if ( ! is_email( $email ) ) {
            wp_die( __( 'Invalid email address.' ) );
        }
    
        if ( is_subdomain_install() ) {
            $newdomain = $domain . '.' . preg_replace( '|^www\.|', '', get_network()->domain );
            $path      = get_network()->path;
        } else {
            $newdomain = get_network()->domain;
            $path      = get_network()->path . $domain . '/';
        }
    
        $password = 'N/A';
        $user_id = email_exists($email);
        if ( !$user_id ) { // Create a new user with a random password
            /**
             * Fires immediately before a new user is created via the network site-new.php page.
             *
             * @since 4.5.0
             *
             * @param string $email Email of the non-existent user.
             */
            do_action( 'pre_network_site_new_created_user', $email );

            $user_id = username_exists( $domain );
            if ( $user_id ) {
                wp_die( __( 'The domain or path entered conflicts with an existing username.' ) );
            }
            $password = wp_generate_password( 12, false );
            $user_id = wpmu_create_user( $domain, $password, $email );
            if ( false === $user_id ) {
                wp_die( __( 'There was an error creating the user.' ) );
            }

            /**
             * Fires after a new user has been created via the network site-new.php page.
             *
             * @since 4.4.0
             *
             * @param int $user_id ID of the newly created user.
             */
            do_action( 'network_site_new_created_user', $user_id );
        }
    
        $wpdb->hide_errors();
        $id = wpmu_create_blog( $newdomain, $path, $title, $user_id, $meta, get_current_network_id() );
        $wpdb->show_errors();
        if ( ! is_wp_error( $id ) ) {
            if ( ! is_super_admin( $user_id ) && !get_user_option( 'primary_blog', $user_id ) ) {
                update_user_option( $user_id, 'primary_blog', $id, true );
            }

            wp_mail(
                get_site_option( 'admin_email' ),
                sprintf(
                    /* translators: %s: network name */
                    __( '[%s] New Site Created' ),
                    get_network()->site_name
                    ),
                sprintf(
                    /* translators: 1: user login, 2: site url, 3: site name/title */
                    __( 'New site created by %1$s Address: %2$s Name: %3$s' ),
                    $current_user->user_login,
                    get_site_url( $id ),
                    wp_unslash( $title )
                    ),
                sprintf(
                    'From: "%1$s" <%2$s>',
                    _x( 'Site Admin', 'email "From" field' ),
                    get_site_option( 'admin_email' )
                    )
                );
            //wpmu_welcome_notification( $id, $user_id, $password, $title, array( 'public' => 1 ) );
            //wp_redirect( add_query_arg( array( 'update' => 'added', 'id' => $id ), 'site-new.php' ) );
            //exit;
        } else {
            wp_die( $id->get_error_message() );
        }
}













?>