<?php
/**
 * Custom Login Flow & Styling
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** 
 * Clean Login Flow (Zero CAPTCHA Logic)
 */ 
add_filter( 'login_redirect', 'ifs_pms_login_redirect_dashboard', 10, 3 ); 

function ifs_pms_login_redirect_dashboard( $redirect_to, $requested_redirect_to, $user ) { 
    if ( isset( $user->ID ) ) { 
        if ( user_can( $user, 'ozone_access_terminal' ) || user_can( $user, 'manage_options' ) ) { 
            return admin_url( 'admin.php?page=ifs-pms' ); 
        } 
    } 
    return $redirect_to; 
} 

add_action( 'login_enqueue_scripts', 'ifs_pms_custom_login_style' ); 

function ifs_pms_custom_login_style() { 
    $logo_url = get_option( 'ifs_pms_logo_url', '' ); 
    ?> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style> 
        body.login { 
            background: linear-gradient(135deg, #090d16 0%, #0c1c2e 50%, #0284c7 100%) !important; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            min-height: 100dvh;
            padding: 16px 10px;
            position: relative; 
            overflow-x: hidden; 
            box-sizing: border-box;
        } 
        body.login::before { 
            content: ''; 
            position: absolute; 
            width: 500px; 
            height: 500px; 
            background: radial-gradient(circle, rgba(2,132,199,0.2) 0%, rgba(0,0,0,0) 70%); 
            top: -150px; 
            left: -150px; 
            border-radius: 50%; 
            z-index: 0; 
            pointer-events: none;
        } 
        #login { 
            width: 100% !important; 
            max-width: 420px !important; 
            padding: 12px !important; 
            z-index: 10; 
            position: relative; 
            box-sizing: border-box;
        } 
        .login form { 
            background: rgba(255, 255, 255, 0.95) !important; 
            backdrop-filter: blur(12px); 
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3) !important; 
            border-radius: 24px !important; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1) inset !important; 
            padding: 30px 24px !important; 
            box-sizing: border-box;
        } 
        .login h1 a { 
            <?php if ( ! empty( $logo_url ) ) : ?> 
                background-image: url('<?php echo esc_url( $logo_url ); ?>') !important; 
                background-size: contain !important; 
                background-repeat: no-repeat !important; 
                background-position: center !important; 
                width: 100% !important; 
                height: 70px !important; 
                margin-bottom: 16px !important; 
            <?php else : ?> 
                background-image: none !important; 
                text-indent: 0 !important; 
                color: #0f172a !important; 
                font-size: 24px !important; 
                font-weight: 800 !important; 
                width: 100% !important; 
                height: auto !important; 
                margin-bottom: 20px !important; 
                text-align: center; 
                letter-spacing: -0.5px; 
            <?php endif; ?> 
        } 
        <?php if ( empty( $logo_url ) ) : ?> 
        .login h1 a::after { 
            content: 'Ozone Skypool OS'; 
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        } 
        <?php endif; ?> 
        .login label { 
            color: #334155 !important; 
            font-weight: 700 !important; 
            font-size: 13px !important; 
            margin-bottom: 6px; 
            display: block; 
        } 
        .login input.input { 
            border-radius: 12px !important; 
            border: 1.5px solid #cbd5e1 !important; 
            padding: 10px 14px !important; 
            height: 48px !important; 
            font-size: 14.5px !important; 
            background: #f8fafc !important; 
            color: #0f172a !important; 
            transition: all 0.25s ease; 
            box-sizing: border-box !important; 
        } 
        .login input.input:focus { 
            border-color: #0284c7 !important; 
            background: #ffffff !important; 
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.15) !important; 
        } 
        .login .button.wp-submit { 
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important; 
            border: none !important; 
            height: 48px !important; 
            border-radius: 14px !important; 
            font-weight: 800 !important; 
            font-size: 15px !important; 
            width: 100% !important; 
            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.4) !important; 
            cursor: pointer; 
            transition: all 0.25s ease; 
            color: #ffffff !important; 
            letter-spacing: 0.3px; 
        } 
        .login .button.wp-submit:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 14px 30px rgba(2, 132, 199, 0.5) !important; 
        } 
        #nav, #backtoblog { 
            text-align: center; 
            margin-top: 16px !important; 
        } 
        #nav a, #backtoblog a { 
            color: rgba(255, 255, 255, 0.85) !important; 
            font-weight: 600 !important; 
            font-size: 13px; 
            text-decoration: none; 
            transition: color 0.2s ease; 
        } 
        #nav a:hover, #backtoblog a:hover { 
            color: #ffffff !important; 
            text-decoration: underline; 
        } 
    </style> 
    <?php 
} 

/** 
 * Replace Powered by WordPress Footer Text 
 */ 
add_filter( 'login_footertext', 'ifs_pms_custom_login_footer' ); 

function ifs_pms_custom_login_footer() { 
    $b_name = get_option( 'ifs_pms_business_name', '' ); 
    return sprintf( 
        esc_html__( '&copy; %1$d \%2$s &bull; Enterprise Operating System', 'swimming-pool-manager' ), 
        (int) gmdate( 'Y' ), 
        esc_html( $b_name ? $b_name : 'Ozone Skypool' ) 
    ); 
}