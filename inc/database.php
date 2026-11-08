<?php
/**
 * Database Installation & Migrations
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Database Installation & Capability Registration
 * Runs on plugin activation.
 */
function ifs_pms_install() { 
    global $wpdb; 
    require_once ABSPATH . 'wp-admin/includes/upgrade.php'; 

    $charset_collate = $wpdb->get_charset_collate(); 

    // Core Tables
    $t_cust    = $wpdb->prefix . 'ifs_pms_customers'; 
    $t_tick    = $wpdb->prefix . 'ifs_pms_tickets'; 
    $t_members = $wpdb->prefix . 'ifs_pms_memberships'; 
    $t_expense = $wpdb->prefix . 'ifs_pms_expenses'; 
    $t_salary  = $wpdb->prefix . 'ifs_pms_salaries'; 

    // New Enterprise Tables
    $t_water   = $wpdb->prefix . 'ifs_pms_water_logs'; 
    $t_maint   = $wpdb->prefix . 'ifs_pms_maintenance'; 
    $t_events  = $wpdb->prefix . 'ifs_pms_events'; 
    $t_waivers = $wpdb->prefix . 'ifs_pms_waivers'; 
    $t_lockers = $wpdb->prefix . 'ifs_pms_lockers'; 

    $sql = "CREATE TABLE {$t_cust} ( 
        id mediumint(9) NOT NULL AUTO_INCREMENT, 
        name varchar(100) NOT NULL, 
        phone varchar(20) NOT NULL, 
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL, 
        PRIMARY KEY  (id), 
        UNIQUE KEY phone (phone) 
    ) {$charset_collate}; 

    CREATE TABLE {$t_tick} ( 
        id mediumint(9) NOT NULL AUTO_INCREMENT, 
        ticket_code varchar(50) NOT NULL, 
        customer_id mediumint(9) NOT NULL, 
        guest_type varchar(20) DEFAULT 'customer' NOT NULL, 
        package_details text DEFAULT NULL, 
        duration_hours int(3) DEFAULT 1 NOT NULL, 
        payment_method varchar(30) DEFAULT 'Cash' NOT NULL, 
        room_no varchar(20) DEFAULT NULL, 
        amount decimal(10,2) NOT NULL DEFAULT 0.00, 
        sold_by varchar(100) NOT NULL, 
        scanned_by varchar(100) DEFAULT NULL, 
        status varchar(20) DEFAULT 'Valid' NOT NULL, 
        sold_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL, 
        scanned_at datetime NULL, 
        PRIMARY KEY  (id), 
        UNIQUE KEY ticket_code (ticket_code), 
        KEY idx_customer (customer_id), 
        KEY idx_status (status)
    ) {$charset_collate}; 

    CREATE TABLE {$t_members} ( 
        id mediumint(9) NOT NULL AUTO_INCREMENT, 
        member_code varchar(50) NOT NULL, 
        name varchar(100) NOT NULL, 
        phone varchar(20) NOT NULL, 
        plan_type varchar(50) NOT NULL, 
        amount decimal(10,2) NOT NULL DEFAULT 0.00, 
        start_date date NOT NULL, 
        expiry_date date NOT NULL, 
        status varchar(20) DEFAULT 'Active' NOT NULL, 
        profile_image varchar(255) DEFAULT NULL, 
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL, 
        PRIMARY KEY  (id), 
        UNIQUE KEY member_code (member_code)
    ) {$charset_collate}; 

    CREATE TABLE {$t_expense} ( 
        id mediumint(9) NOT NULL AUTO_INCREMENT, 
        title varchar(200) NOT NULL, 
        category varchar(100) NOT NULL, 
        amount decimal(10,2) NOT NULL DEFAULT 0.00, 
        added_by varchar(100) NOT NULL, 
        expense_date date NOT NULL, 
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL, 
        PRIMARY KEY  (id)
    ) {$charset_collate}; 

    CREATE TABLE {$t_salary} ( 
        id mediumint(9) NOT NULL AUTO_INCREMENT, 
        user_id bigint(20) NOT NULL, 
        base_salary decimal(10,2) NOT NULL DEFAULT 0.00, 
        allowance decimal(10,2) NOT NULL DEFAULT 0.00, 
        pay_frequency varchar(20) DEFAULT 'Monthly' NOT NULL, 
        effective_date date NOT NULL, 
        status varchar(20) DEFAULT 'Active' NOT NULL, 
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL, 
        PRIMARY KEY  (id)
    ) {$charset_collate};
    
    CREATE TABLE {$t_water} ( 
        id mediumint(9) NOT NULL AUTO_INCREMENT, 
        ph_level decimal(4,2) NOT NULL, 
        chlorine_level decimal(4,2) NOT NULL, 
        temperature decimal(5,2) NOT NULL, 
        notes text DEFAULT NULL, 
        logged_by varchar(100) NOT NULL, 
        logged_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL, 
        PRIMARY KEY  (id)
    ) {$charset_collate};

    CREATE TABLE {$t_maint} ( 
        id mediumint(9) NOT NULL AUTO_INCREMENT, 
        task_title varchar(200) NOT NULL, 
        status varchar(20) DEFAULT 'Pending' NOT NULL, 
        reported_by varchar(100) NOT NULL, 
        resolved_by varchar(100) DEFAULT NULL, 
        reported_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL, 
        resolved_at datetime NULL, 
        PRIMARY KEY  (id)
    ) {$charset_collate};

    CREATE TABLE {$t_events} ( 
        id mediumint(9) NOT NULL AUTO_INCREMENT, 
        title varchar(200) NOT NULL, 
        event_date datetime NOT NULL, 
        customer_id mediumint(9) NOT NULL, 
        amount decimal(10,2) NOT NULL DEFAULT 0.00, 
        status varchar(20) DEFAULT 'Scheduled' NOT NULL, 
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL, 
        PRIMARY KEY  (id)
    ) {$charset_collate};

    CREATE TABLE {$t_waivers} ( 
        id mediumint(9) NOT NULL AUTO_INCREMENT, 
        customer_id mediumint(9) NOT NULL, 
        status varchar(20) DEFAULT 'Valid' NOT NULL, 
        signed_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL, 
        expires_at datetime NOT NULL, 
        PRIMARY KEY  (id)
    ) {$charset_collate};

    CREATE TABLE {$t_lockers} ( 
        id mediumint(9) NOT NULL AUTO_INCREMENT, 
        locker_number varchar(20) NOT NULL, 
        status varchar(20) DEFAULT 'Available' NOT NULL, 
        customer_id mediumint(9) DEFAULT NULL, 
        PRIMARY KEY  (id),
        UNIQUE KEY locker_number (locker_number)
    ) {$charset_collate};"; 

    dbDelta( $sql ); 

    $default_options = array( 
        'ifs_pms_business_name'       => '', 
        'ifs_pms_ticket_price'        => '0.00', 
        'ifs_pms_currency'            => '', 
        'ifs_pms_phone'               => '', 
        'ifs_pms_address'             => '', 
        'ifs_pms_receipt_note'        => '', 
        'ifs_pms_max_capacity'        => '', 
        'ifs_pms_logo_url'            => '', 
        'ifs_pms_pool_status'         => '', 
        'ifs_pms_enable_amenities'    => '0', 
        'ifs_pms_pricing_tiers'       => array(), 
        'ifs_pms_amenity_addons'      => array(), 
        'ifs_pms_weekly_schedule'     => array(), 
        'ifs_pms_enabled_tenders'     => array( 'Cash' ), 
        'ifs_pms_quick_cash_presets'  => '100, 200, 500, 1000', 
        'ifs_pms_thermal_paper_width' => '80mm', 
        'ifs_pms_enable_strict_scan'  => '1', 
        'ifs_pms_auto_expire_passes'  => '1', 
        'ifs_pms_enable_audio_buzzer' => '1', 
        'ifs_pms_version'             => IFS_PMS_VERSION, 
    ); 

    foreach ( $default_options as $key => $val ) { 
        if ( false === get_option( $key ) ) { 
            add_option( $key, $val ); 
        } 
    } 

    // Setup Roles on Activation
    $admin_role = get_role( 'administrator' ); 
    if ( $admin_role ) { 
        $caps = array( 
            'ozone_access_terminal', 'ozone_sell_tickets', 'ozone_scan_passes', 
            'ozone_manage_patrons', 'ozone_view_history', 'ozone_view_finances', 
            'ozone_manage_expenses', 'ozone_manage_settings', 'ozone_manage_facilities'
        ); 
        foreach ( $caps as $cap ) { 
            if ( ! $admin_role->has_cap( $cap ) ) { 
                $admin_role->add_cap( $cap ); 
            } 
        } 
    } 

    if ( ! get_role( 'ozone_cashier' ) ) { 
        add_role( 'ozone_cashier', __( 'Ozone Cashier', 'swimming-pool-manager' ), array( 
            'read'                  => true, 
            'ozone_access_terminal' => true, 
            'ozone_sell_tickets'    => true, 
            'ozone_scan_passes'     => true, 
            'ozone_manage_patrons'  => true, 
            'ozone_view_history'    => true, 
        ) ); 
    } 
} 

/** 
 * Migration Runner 
 */ 
add_action( 'admin_init', 'ifs_pms_maybe_upgrade_db' ); 

function ifs_pms_maybe_upgrade_db() { 
    $installed_ver = get_option( 'ifs_pms_version', '0.0.0' ); 
    if ( version_compare( $installed_ver, IFS_PMS_VERSION, '<' ) ) { 
        // Run any necessary table alters here
        update_option( 'ifs_pms_version', IFS_PMS_VERSION ); 
    } 
}