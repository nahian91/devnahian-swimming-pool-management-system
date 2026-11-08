<?php
/**
 * Menu Registration
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'ifs_pms_register_menu' ); 

function ifs_pms_register_menu() { 
    $cap_basic   = current_user_can( 'manage_options' ) ? 'manage_options' : 'ozone_access_terminal'; 
    $cap_patrons = current_user_can( 'manage_options' ) ? 'manage_options' : 'ozone_manage_patrons';
    $cap_facil   = current_user_can( 'manage_options' ) ? 'manage_options' : 'ozone_manage_facilities';

    add_menu_page( __( 'Ozone Skypool', 'swimming-pool-manager' ), __( 'Ozone Skypool', 'swimming-pool-manager' ), $cap_basic, 'ifs-pms', 'ifs_pms_render_application', 'dashicons-cloud', 2 ); 

    // Front Desk Submenus
    add_submenu_page( 'ifs-pms', __( 'Overview', 'swimming-pool-manager' ), __( 'Overview', 'swimming-pool-manager' ), $cap_basic, 'ifs-pms', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Tickets', 'swimming-pool-manager' ), __( 'Tickets', 'swimming-pool-manager' ), $cap_basic, 'ifs-pms-tickets', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Scan Pass', 'swimming-pool-manager' ), __( 'Scan Pass', 'swimming-pool-manager' ), $cap_basic, 'ifs-pms-scanner', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Live Status', 'swimming-pool-manager' ), __( 'Live Status', 'swimming-pool-manager' ), $cap_basic, 'ifs-pms-live-status', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Customers', 'swimming-pool-manager' ), __( 'Customers', 'swimming-pool-manager' ), $cap_patrons, 'ifs-pms-customers', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Members', 'swimming-pool-manager' ), __( 'Members', 'swimming-pool-manager' ), $cap_patrons, 'ifs-pms-membership', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Waivers', 'swimming-pool-manager' ), __( 'Waivers', 'swimming-pool-manager' ), $cap_patrons, 'ifs-pms-waivers', 'ifs_pms_render_application' ); 

    // Operations Submenus
    add_submenu_page( 'ifs-pms', __( 'Bookings & Events', 'swimming-pool-manager' ), __( 'Bookings & Events', 'swimming-pool-manager' ), $cap_patrons, 'ifs-pms-events', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Locker Control', 'swimming-pool-manager' ), __( 'Locker Control', 'swimming-pool-manager' ), $cap_basic, 'ifs-pms-lockers', 'ifs_pms_render_application' ); 

    // Management Submenus
    add_submenu_page( 'ifs-pms', __( 'Staffs', 'swimming-pool-manager' ), __( 'Staffs', 'swimming-pool-manager' ), 'manage_options', 'ifs-pms-staff', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Expenses', 'swimming-pool-manager' ), __( 'Expenses', 'swimming-pool-manager' ), 'ozone_manage_expenses', 'ifs-pms-expenses', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Reports', 'swimming-pool-manager' ), __( 'Reports', 'swimming-pool-manager' ), 'ozone_view_finances', 'ifs-pms-reports', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Water Logs', 'swimming-pool-manager' ), __( 'Water Logs', 'swimming-pool-manager' ), $cap_facil, 'ifs-pms-water-logs', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Maintenance', 'swimming-pool-manager' ), __( 'Maintenance', 'swimming-pool-manager' ), $cap_facil, 'ifs-pms-maintenance', 'ifs_pms_render_application' ); 
    add_submenu_page( 'ifs-pms', __( 'Settings', 'swimming-pool-manager' ), __( 'Settings', 'swimming-pool-manager' ), 'ozone_manage_settings', 'ifs-pms-settings', 'ifs_pms_render_application' ); 
}