<?php
/**
 * Module: Customers CRM & Directory
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_init', 'ifs_pms_handle_customer_submissions' );

function ifs_pms_handle_customer_submissions() {
    if ( ! isset( $_POST['ifs_pms_action'] ) ) {
        return;
    }

    $action = sanitize_key( wp_unslash( $_POST['ifs_pms_action'] ) );
    if ( ! in_array( $action, array( 'edit_customer', 'delete_customer' ), true ) ) {
        return;
    }

    check_admin_referer( 'ifs_pms_secure_action', 'ifs_pms_action_nonce' );

    global $wpdb;
    $t_cust   = $wpdb->prefix . 'ifs_pms_customers';
    $base_url = admin_url( 'admin.php?page=ifs-pms-customers' );

    // Edit Patron
    if ( 'edit_customer' === $action ) {
        if ( ! current_user_can( 'ozone_manage_patrons' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Forbidden: Insufficient privileges.', 'swimming-pool-manager' ), 403 );
        }

        $cust_id = isset( $_POST['customer_id'] ) ? absint( wp_unslash( $_POST['customer_id'] ) ) : 0;
        $name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';

        if ( $cust_id > 0 && ! empty( $name ) && ! empty( $phone ) ) {
            $wpdb->update(
                $t_cust,
                array( 'name' => $name, 'phone' => $phone ),
                array( 'id' => $cust_id ),
                array( '%s', '%s' ),
                array( '%d' )
            );
            wp_safe_redirect( add_query_arg( array( 'msg' => 'customer_updated' ), $base_url ) );
            exit;
        }
    }

    // Delete Patron Profile
    if ( 'delete_customer' === $action ) {
        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'ozone_manage_settings' ) ) {
            wp_die( esc_html__( 'Forbidden: Only managers can delete customer profiles.', 'swimming-pool-manager' ), 403 );
        }

        $cust_id = isset( $_POST['customer_id'] ) ? absint( wp_unslash( $_POST['customer_id'] ) ) : 0;
        if ( $cust_id > 0 ) {
            $wpdb->delete( $t_cust, array( 'id' => $cust_id ), array( '%d' ) );
            wp_safe_redirect( $base_url );
            exit;
        }
    }
}