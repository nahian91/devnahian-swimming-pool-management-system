<?php 
/** 
 * Plugin Name: IFS Pool Management System
 * Plugin URI:        https://ozoneskypool.com/management-system 
 * Description:       Enterprise Aquatic POS, QR Gate Turnstile Control, RFID/Pass Ledger & Financial Operating System with Role-Based Access Control and Native SVG UI. 
 * Version:           1.1.0
 * Author:            Abdullah Nahian 
 * Author URI:        https://ozoneskypool.com 
 * License:           GPL-2.0-or-later 
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html 
 * Text Domain:       swimming-pool-manager 
 * Domain Path:       /languages 
 * Requires at least: 6.0 
 * Requires PHP:      7.4 
 * 
 * @package SwimmingPoolManager 
 */ 

if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
} 

define( 'IFS_PMS_VERSION', '1.1.0' ); 
define( 'IFS_PMS_PATH', plugin_dir_path( __FILE__ ) ); 
define( 'IFS_PMS_URL', plugin_dir_url( __FILE__ ) ); 

/**
 * Require core system files
 */
require_once IFS_PMS_PATH . 'inc/database.php';
require_once IFS_PMS_PATH . 'inc/menus.php';
require_once IFS_PMS_PATH . 'inc/login.php';

/**
 * Require Modular Logic Handlers
 */
require_once IFS_PMS_PATH . 'modules/staff.php';
require_once IFS_PMS_PATH . 'modules/tickets.php';
require_once IFS_PMS_PATH . 'modules/customers.php';
require_once IFS_PMS_PATH . 'modules/membership.php';
require_once IFS_PMS_PATH . 'modules/expenses.php';
require_once IFS_PMS_PATH . 'modules/settings.php';

// Register Database Schema creation on activation
register_activation_hook( __FILE__, 'ifs_pms_install' );

/** 
 * Ensure proper viewport rendering across mobile browsers in WP Admin.
 */
add_action( 'admin_head', 'ifs_pms_ensure_admin_viewport_meta' );
function ifs_pms_ensure_admin_viewport_meta() {
    $hook = $_GET['page'] ?? '';
    if ( false !== strpos( $hook, 'ifs-pms' ) ) {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
    }
}

/** 
 * Load plugin text domain. 
 */ 
add_action( 'plugins_loaded', 'ifs_pms_load_textdomain' ); 
function ifs_pms_load_textdomain() { 
    load_plugin_textdomain( 'swimming-pool-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
} 

/** 
 * Native SVG Icon Helper Engine (Zero External Dependencies) 
 */ 
function ifs_pms_get_svg( $icon_name, $class = '', $size = 16 ) { 
    $icons = array( 
        'overview'  => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>', 
        'ticket'    => '<path d="M20 6h-2.18c.11-.31.18-.65.18-1a2.996 2.996 0 0 0-3-3c-1.11 0-2.08.6-2.58 1.5C11.92 2.6 10.95 2 9.84 2a2.996 2.996 0 0 0-3 1c0 .35.07.69.18 1H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-1 12H5V8h14v10z"/>', 
        'qrcode'    => '<path d="M2 2h8v8H2V2zm2 4h4V4H4v2zm8-4h8v8h-8V2zm2 4h4V4h-4v2zM2 12h8v8H2v-8zm2 4h4v-4H4v4zm10-4h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z"/>', 
        'tower'     => '<path d="M12 2L2 7v2h20V7L12 2zm0 3.25L18.5 8H5.5L12 5.25zM6 10v10h2V10H6zm4 0v10h4V10h-4zm6 0v10h2V10h-2zm-8 12h8v2H8v-2z"/>', 
        'users'     => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>', 
        'shield'    => '<path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>', 
        'receipt'   => '<path d="M18 17h-6v-2h6v2zm0-4h-6v-2h6v2zm0-4h-6V7h6v2zM16 2H6c-1.1 0-2 .9-2 2v16l3-1.5L10 20l3-1.5L16 20V4c0-1.1-.9-2-2-2zm0 15.5l-3-1.5-3 1.5V4h6v13.5z"/>', 
        'print'     => '<path d="M19 8h-1V3H6v5H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zM8 5h8v3H8V5zm8 14H8v-4h8v4zm4-4h-2v-2H6v2H4v-4c0-.55.45-1 1-1h14c.55 0 1 .45 1 1v4z"/>', 
        'check'     => '<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>', 
        'clipboard' => '<path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>',
        'calendar'  => '<path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>',
        'lock'      => '<path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>',
        'water'     => '<path d="M12 20a6 6 0 0 1-6-6c0-4 6-10.75 6-10.75S18 10 18 14a6 6 0 0 1-6 6z"/>',
        'tool'      => '<path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/>',
    ); 

    $path       = $icons[ $icon_name ] ?? '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>'; 
    $class_attr = ! empty( $class ) ? ' class="' . esc_attr( $class ) . '"' : ''; 

    return sprintf( 
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="%d" height="%d" fill="currentColor"%s>%s</svg>', 
        intval( $size ), intval( $size ), $class_attr, $path 
    ); 
} 

/** 
 * Admin Asset Queue & Script Localization 
 */ 
add_action( 'admin_enqueue_scripts', 'ifs_pms_enqueue_assets' ); 

function ifs_pms_enqueue_assets( $hook ) { 
    if ( false === strpos( $hook, 'ifs-pms' ) ) { 
        return; 
    } 

    wp_enqueue_media(); 
    wp_enqueue_style( 'ozone-skypool-admin-css', IFS_PMS_URL . 'assets/css/style.css', array(), IFS_PMS_VERSION ); 
    wp_enqueue_script( 'ozone-skypool-admin-js', IFS_PMS_URL . 'assets/js/main.js', array( 'jquery' ), IFS_PMS_VERSION, true ); 

    $transient_key = 'ifs_pms_last_ticket_' . get_current_user_id(); 
    $last_ticket   = get_transient( $transient_key ); 
    if ( $last_ticket ) { 
        delete_transient( $transient_key ); 
    } 

    $token = wp_create_nonce( 'ifs_pms_security_token' ); 

    wp_localize_script( 
        'ozone-skypool-admin-js', 
        'ifsPmsConfig', 
        array( 
            'ajax_url'            => admin_url( 'admin-ajax.php' ), 
            'security_token'      => $token, 
            'nonce'               => $token, 
            'currency'            => esc_html( (string) get_option( 'ifs_pms_currency', 'BDT' ) ), 
            'current_mon_prefix'  => 'OZ-' . strtoupper( current_time( 'M' ) ) . '-' . current_time( 'd' ) . '-', 
            'current_operator'    => wp_get_current_user()->display_name, 
            'last_ticket'         => $last_ticket ? $last_ticket : null, 
        ) 
    ); 
} 

/** 
 * Auto-redirect website root visitors to the Ozone Skypool Admin Terminal. 
 */ 
add_action( 'template_redirect', 'ifs_pms_home_redirect_to_admin' ); 
function ifs_pms_home_redirect_to_admin() { 
    if ( is_front_page() || is_home() ) { 
        wp_safe_redirect( admin_url( 'admin.php?page=ifs-pms' ) ); 
        exit; 
    } 
} 

/** 
 * Main Terminal Canvas Shell 
 */ 
function ifs_pms_render_application() { 
    if ( ! current_user_can( 'ozone_access_terminal' ) && ! current_user_can( 'manage_options' ) ) { 
        wp_die( esc_html__( 'Unauthorized access.', 'swimming-pool-manager' ), 403 ); 
    } 

    $is_admin         = current_user_can( 'manage_options' ) || current_user_can( 'ozone_manage_settings' ); 
    $admin_only_views = array( 'staff', 'expenses', 'reports', 'settings', 'water-logs', 'maintenance' ); 

    $plugin_page = $_GET['page'] ?? 'ifs-pms'; 
    
    $view_map = array(
        'ifs-pms'             => 'dashboard',
        'ifs-pms-tickets'     => 'tickets',
        'ifs-pms-scanner'     => 'scanner',
        'ifs-pms-live-status' => 'live-status',
        'ifs-pms-customers'   => 'customers',
        'ifs-pms-membership'  => 'membership',
        'ifs-pms-waivers'     => 'waivers',
        'ifs-pms-events'      => 'events',
        'ifs-pms-lockers'     => 'lockers',
        'ifs-pms-staff'       => 'staff',
        'ifs-pms-expenses'    => 'expenses',
        'ifs-pms-reports'     => 'reports',
        'ifs-pms-water-logs'  => 'water-logs',
        'ifs-pms-maintenance' => 'maintenance',
        'ifs-pms-settings'    => 'settings',
    );

    $current_view = $view_map[$plugin_page] ?? 'dashboard';

    if ( 'ifs-pms' === $plugin_page && isset( $_GET['view'] ) ) {
        $current_view = sanitize_key( wp_unslash( $_GET['view'] ) );
    }

    if ( ! $is_admin && in_array( $current_view, $admin_only_views, true ) ) { 
        $current_view = 'dashboard'; 
    } 

    $msg_code  = isset( $_GET['msg'] ) ? sanitize_key( wp_unslash( $_GET['msg'] ) ) : ''; 
    $flash_msg = ''; 
    $flash_map = array( 
        'ticket_created'      => __( 'Single Admission Pass Created Successfully.', 'swimming-pool-manager' ), 
        'ticket_updated'      => __( 'Ticket Record Updated.', 'swimming-pool-manager' ), 
        'ticket_deleted'      => __( 'Ticket Removed Permanently.', 'swimming-pool-manager' ), 
        'customer_updated'    => __( 'Patron Profile Updated Successfully.', 'swimming-pool-manager' ), 
        'membership_enrolled' => __( 'Member Subscription Record Synchronized.', 'swimming-pool-manager' ), 
        'expense_logged'      => __( 'Operational Outflow Logged.', 'swimming-pool-manager' ), 
        'staff_created'       => __( 'Operator Account & Compensation Provisioned.', 'swimming-pool-manager' ), 
        'staff_updated'       => __( 'Operator Profile Updated.', 'swimming-pool-manager' ), 
        'settings_saved'      => __( 'Master Configuration Saved.', 'swimming-pool-manager' ), 
    ); 
    if ( isset( $flash_map[ $msg_code ] ) ) { 
        $flash_msg = $flash_map[ $msg_code ]; 
    } 

    $b_name       = (string) get_option( 'ifs_pms_business_name', '' ); 
    $logo_url     = (string) get_option( 'ifs_pms_logo_url', '' ); 
    ?> 

    <div class="ifs-pms-shell" id="ifsPmsAppShell"> 
        <aside class="ifs-pms-aside" id="ifsPmsSidebarDrawer" style="width: 260px; min-width: 260px; height: 100vh; max-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; box-sizing: border-box; padding: 22px 14px; background: var(--ifs-sidebar); border-right: 1px solid var(--ifs-border-subtle);"> 
            <div class="ifs-pms-nav-scroll-wrap" style="flex: 1 1 auto; overflow-y: auto; overflow-x: hidden; min-height: 0; padding-right: 4px; margin-bottom: 12px;"> 
                <div class="ifs-pms-brand" style="display: flex; align-items: center; justify-content: space-between; padding: 4px 8px 18px 8px; border-bottom: 1px solid var(--ifs-border-subtle); margin-bottom: 16px;"> 
                    <div style="overflow: hidden;"> 
                        <div style="font-size: 15px; font-weight: 800; color: var(--ifs-text-primary); white-space: nowrap; text-overflow: ellipsis; overflow: hidden;"><?php echo $b_name ? esc_html( $b_name ) : esc_html__( 'Ozone Skypool', 'swimming-pool-manager' ); ?></div> 
                        <div style="font-size: 10px; color: var(--ifs-accent); font-weight: 700; letter-spacing: 0.8px;"> 
                            <?php echo $is_admin ? esc_html__( 'MANAGER TERMINAL', 'swimming-pool-manager' ) : esc_html__( 'CASHIER DESK', 'swimming-pool-manager' ); ?> 
                        </div> 
                    </div> 
                    <button type="button" onclick="ifsPmsToggleMobileMenu(false);" aria-label="<?php esc_attr_e( 'Close Navigation', 'swimming-pool-manager' ); ?>" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; border: none; background: var(--ifs-surface-hover); color: var(--ifs-text-secondary); cursor: pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div> 

                <div class="ifs-pms-nav-group-title"><?php esc_html_e( 'Front Desk', 'swimming-pool-manager' ); ?></div> 
                <ul class="ifs-pms-nav"> 
                    <li class="ifs-pms-nav-item <?php echo ( 'dashboard' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                        <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms' ) ); ?>"> 
                            <span class="dashicons dashicons-dashboard"></span> <?php esc_html_e( 'Overview', 'swimming-pool-manager' ); ?> 
                        </a> 
                    </li> 
                    <li class="ifs-pms-nav-item <?php echo ( 'tickets' === $current_view || 'ticket_detail' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                        <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-tickets' ) ); ?>"> 
                            <span class="dashicons dashicons-tickets-alt"></span> <?php esc_html_e( 'Tickets', 'swimming-pool-manager' ); ?> 
                        </a> 
                    </li> 
                    <li class="ifs-pms-nav-item <?php echo ( 'scanner' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                        <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-scanner' ) ); ?>"> 
                            <span class="dashicons dashicons-fullscreen-alt"></span> <?php esc_html_e( 'Scan Pass', 'swimming-pool-manager' ); ?> 
                        </a> 
                    </li> 
                    <li class="ifs-pms-nav-item <?php echo ( 'live-status' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                        <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-live-status' ) ); ?>"> 
                            <span class="dashicons dashicons-building"></span> <?php esc_html_e( 'Live Status', 'swimming-pool-manager' ); ?> 
                        </a> 
                    </li> 
                    <li class="ifs-pms-nav-item <?php echo ( 'customers' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                        <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-customers' ) ); ?>"> 
                            <span class="dashicons dashicons-groups"></span> <?php esc_html_e( 'Customers', 'swimming-pool-manager' ); ?> 
                        </a> 
                    </li> 
                    <li class="ifs-pms-nav-item <?php echo ( 'membership' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                        <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-membership' ) ); ?>"> 
                            <span class="dashicons dashicons-id-alt"></span> <?php esc_html_e( 'Members', 'swimming-pool-manager' ); ?> 
                        </a> 
                    </li> 
                    <li class="ifs-pms-nav-item <?php echo ( 'waivers' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                        <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-waivers' ) ); ?>"> 
                            <?php echo ifs_pms_get_svg('clipboard', '', 16); ?> <?php esc_html_e( 'Waivers', 'swimming-pool-manager' ); ?> 
                        </a> 
                    </li> 
                </ul> 

                <div class="ifs-pms-nav-group-title"><?php esc_html_e( 'Operations', 'swimming-pool-manager' ); ?></div> 
                <ul class="ifs-pms-nav"> 
                    <li class="ifs-pms-nav-item <?php echo ( 'events' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                        <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-events' ) ); ?>"> 
                            <?php echo ifs_pms_get_svg('calendar', '', 16); ?> <?php esc_html_e( 'Bookings & Events', 'swimming-pool-manager' ); ?> 
                        </a> 
                    </li> 
                    <li class="ifs-pms-nav-item <?php echo ( 'lockers' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                        <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-lockers' ) ); ?>"> 
                            <?php echo ifs_pms_get_svg('lock', '', 16); ?> <?php esc_html_e( 'Locker Control', 'swimming-pool-manager' ); ?> 
                        </a> 
                    </li> 
                </ul> 

                <?php if ( $is_admin ) : ?> 
                    <div class="ifs-pms-nav-group-title"><?php esc_html_e( 'Management & Ledger', 'swimming-pool-manager' ); ?></div> 
                    <ul class="ifs-pms-nav"> 
                        <li class="ifs-pms-nav-item <?php echo ( 'staff' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                            <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-staff' ) ); ?>"> 
                                <span class="dashicons dashicons-shield"></span> <?php esc_html_e( 'Staffs', 'swimming-pool-manager' ); ?> 
                            </a> 
                        </li> 
                        <li class="ifs-pms-nav-item <?php echo ( 'expenses' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                            <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-expenses' ) ); ?>"> 
                                <span class="dashicons dashicons-media-text"></span> <?php esc_html_e( 'Expenses', 'swimming-pool-manager' ); ?> 
                            </a> 
                        </li> 
                        <li class="ifs-pms-nav-item <?php echo ( 'reports' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                            <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-reports' ) ); ?>"> 
                                <span class="dashicons dashicons-chart-area"></span> <?php esc_html_e( 'Reports', 'swimming-pool-manager' ); ?> 
                            </a> 
                        </li> 
                        <li class="ifs-pms-nav-item <?php echo ( 'water-logs' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                            <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-water-logs' ) ); ?>"> 
                                <?php echo ifs_pms_get_svg('water', '', 16); ?> <?php esc_html_e( 'Water Logs', 'swimming-pool-manager' ); ?> 
                            </a> 
                        </li> 
                        <li class="ifs-pms-nav-item <?php echo ( 'maintenance' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                            <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-maintenance' ) ); ?>"> 
                                <?php echo ifs_pms_get_svg('tool', '', 16); ?> <?php esc_html_e( 'Maintenance', 'swimming-pool-manager' ); ?> 
                            </a> 
                        </li> 
                    </ul> 

                    <div class="ifs-pms-nav-group-title"><?php esc_html_e( 'Administration', 'swimming-pool-manager' ); ?></div> 
                    <ul class="ifs-pms-nav"> 
                        <li class="ifs-pms-nav-item <?php echo ( 'settings' === $current_view ) ? 'ifs-pms-active' : ''; ?>"> 
                            <a class="ifs-pms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ifs-pms-settings' ) ); ?>"> 
                                <span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Settings', 'swimming-pool-manager' ); ?> 
                            </a> 
                        </li> 
                    </ul> 
                <?php endif; ?> 

                <div class="ifs-pms-nav-group-title"><?php esc_html_e( 'Session', 'swimming-pool-manager' ); ?></div> 
                <ul class="ifs-pms-nav"> 
                    <li class="ifs-pms-nav-item"> 
                        <a class="ifs-pms-nav-link" href="<?php echo esc_url( wp_logout_url( admin_url( 'index.php' ) ) ); ?>"> 
                            <span class="dashicons dashicons-migrate"></span> <?php esc_html_e( 'Logout', 'swimming-pool-manager' ); ?> 
                        </a> 
                    </li> 
                </ul> 
            </div> 

            <div style="flex-shrink: 0; border-top: 1px solid var(--ifs-border-subtle); padding-top: 12px;"> 
                <div class="ifs-pms-theme-toggle-wrap" style="display: flex; align-items: center; justify-content: space-between; background: var(--ifs-surface-hover); border: 1px solid var(--ifs-border-subtle); border-radius: 10px; padding: 8px 12px; margin-bottom: 12px;"> 
                    <span style="font-size: 11px; font-weight: 700; color: var(--ifs-text-tertiary);"><?php esc_html_e( 'Mode', 'swimming-pool-manager' ); ?></span> 
                    <div style="display: flex; gap: 4px;"> 
                        <button type="button" class="ifs-pms-theme-btn" id="ifsThemeLightBtn" onclick="if(window.ifsPms && window.ifsPms.applyTheme){ window.ifsPms.applyTheme('light'); }"> 
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; flex-shrink: 0;">
                                <circle cx="12" cy="12" r="4"></circle>
                                <line x1="12" y1="2" x2="12" y2="4"></line>
                                <line x1="12" y1="20" x2="12" y2="22"></line>
                                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                                <line x1="2" y1="12" x2="4" y2="12"></line>
                                <line x1="20" y1="12" x2="22" y2="12"></line>
                                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                            </svg>
                            <span><?php esc_html_e( 'Light', 'swimming-pool-manager' ); ?></span>
                        </button> 

                        <button type="button" class="ifs-pms-theme-btn" id="ifsThemeDarkBtn" onclick="if(window.ifsPms && window.ifsPms.applyTheme){ window.ifsPms.applyTheme('dark'); }"> 
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; flex-shrink: 0;">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                            </svg>
                            <span><?php esc_html_e( 'Dark', 'swimming-pool-manager' ); ?></span>
                        </button> 
                    </div>
                </div> 

                <div class="ifs-pms-operator-card" style="background: var(--ifs-surface-hover); border: 1px solid var(--ifs-border-subtle); border-radius: 10px; padding: 10px 12px; display: flex; align-items: center; gap: 10px;"> 
                    <div class="ifs-pms-operator-avatar" style="width: 32px; height: 32px; background: var(--ifs-surface); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; color: var(--ifs-accent); border: 1px solid var(--ifs-border-strong);"><?php echo esc_html( strtoupper( substr( wp_get_current_user()->display_name, 0, 1 ) ) ); ?></div> 
                    <div style="overflow: hidden;"> 
                        <div style="font-size: 13px; font-weight: 700; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; color: var(--ifs-text-primary);"> 
                            <?php echo esc_html( wp_get_current_user()->display_name ); ?> 
                        </div> 
                        <div style="font-size: 11px; color: var(--ifs-text-tertiary);"> 
                            <?php echo $is_admin ? esc_html__( 'Facility Manager', 'swimming-pool-manager' ) : esc_html__( 'Active Cashier', 'swimming-pool-manager' ); ?> 
                        </div> 
                    </div> 
                </div> 
            </div> 
        </aside>

        <!-- Main Admin Canvas Body -->
        <main class="ifs-pms-canvas"> 
            <header class="ifs-pms-header"> 
                <div style="display: flex; align-items: center; width: 100%;"> 
                    <button type="button" class="ifs-pms-mobile-trigger" onclick="ifsPmsToggleMobileMenu(true);" aria-label="<?php esc_attr_e( 'Open Navigation Menu', 'swimming-pool-manager' ); ?>">
                        <span></span>
                    </button>

                    <?php if ( ! empty( $logo_url ) ) : ?> 
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" style="max-height: 44px; width: auto; border-radius: 10px; border: 1px solid var(--ifs-border-subtle); margin-right: 12px;"> 
                    <?php endif; ?> 
                    <div> 
                        <h1><?php echo $b_name ? esc_html( $b_name ) : esc_html__( 'Ozone Skypool', 'swimming-pool-manager' ); ?></h1> 
                        <p><span class="ifs-pms-pulse-dot"></span> <?php esc_html_e( 'Terminal Online', 'swimming-pool-manager' ); ?> &bull; <?php echo esc_html( current_time( 'l, F j, Y' ) ); ?></p> 
                    </div> 
                </div> 
            </header> 

            <?php if ( ! empty( $flash_msg ) ) : ?> 
                <div style="background: var(--ifs-success-soft); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--ifs-success); padding: 12px 18px; border-radius: 8px; margin-bottom: 22px; font-weight: 600; font-size: 13.5px; display: flex; align-items: center; gap: 10px;"> 
                    <?php echo wp_kses( ifs_pms_get_svg( 'check', '', 16 ), array( 'svg' => array( 'xmlns' => true, 'viewBox' => true, 'width' => true, 'height' => true, 'fill' => true, 'class' => true ), 'path' => array( 'd' => true ) ) ); ?> <?php echo esc_html( $flash_msg ); ?> 
                </div> 
            <?php endif; ?> 

            <?php 
            $view_to_include = ( 'ticket_detail' === $current_view ) ? 'tickets' : $current_view; 
            $view_path       = IFS_PMS_PATH . 'views/' . $view_to_include . '.php'; 

            if ( file_exists( $view_path ) ) { 
                include $view_path; 
            } else { 
                echo '<div class="ifs-pms-panel" style="padding: 40px; text-align: center;"><p style="font-size: 15px; font-weight: 700; color: #ef4444;">' . esc_html__( 'Selected module UI was not found:', 'swimming-pool-manager' ) . ' <code>' . esc_html( $view_to_include ) . '.php</code></p></div>'; 
            } 
            ?> 
        </main> 

        <!-- Screen Dimmer Backdrop -->
        <div class="ifs-pms-sidebar-backdrop" id="ifsPmsSidebarBackdrop" onclick="ifsPmsToggleMobileMenu(false);"></div>
    </div> 

    <script>
        function ifsPmsToggleMobileMenu(open) {
            var drawer = document.getElementById('ifsPmsSidebarDrawer');
            var backdrop = document.getElementById('ifsPmsSidebarBackdrop');
            if (!drawer || !backdrop) return;
            if (open) {
                drawer.classList.add('mobile-open');
                backdrop.classList.add('active');
            } else {
                drawer.classList.remove('mobile-open');
                backdrop.classList.remove('active');
            }
        }
    </script>
    <?php 
}