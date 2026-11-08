<?php
/**
 * Module: Staff & Operator Payroll Management
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_init', 'ifs_pms_handle_staff_submissions' );

function ifs_pms_handle_staff_submissions() {
    if ( ! isset( $_POST['ifs_pms_action'] ) ) {
        return;
    }

    $action = sanitize_key( wp_unslash( $_POST['ifs_pms_action'] ) );
    if ( ! in_array( $action, array( 'create_staff', 'edit_staff', 'delete_staff' ), true ) ) {
        return;
    }

    check_admin_referer( 'ifs_pms_secure_action', 'ifs_pms_action_nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Forbidden: Administrator privileges required.', 'swimming-pool-manager' ), 403 );
    }

    global $wpdb;
    $t_salary = $wpdb->prefix . 'ifs_pms_salaries';
    $base_url = admin_url( 'admin.php?page=ifs-pms-staff' );

    // Create Operator
    if ( 'create_staff' === $action ) {
        $username = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : '';
        $email    = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
        $password = isset( $_POST['user_pass'] ) ? trim( (string) wp_unslash( $_POST['user_pass'] ) ) : '';
        $fullname = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
        $avatar   = isset( $_POST['user_avatar_url'] ) ? esc_url_raw( wp_unslash( $_POST['user_avatar_url'] ) ) : '';

        $requested_role = isset( $_POST['user_role'] ) ? sanitize_key( wp_unslash( $_POST['user_role'] ) ) : 'ozone_cashier';
        $role           = in_array( $requested_role, array( 'ozone_cashier', 'administrator' ), true ) ? $requested_role : 'ozone_cashier';

        $base_sal = isset( $_POST['base_salary'] ) ? floatval( wp_unslash( $_POST['base_salary'] ) ) : 0.00;
        $allow    = isset( $_POST['allowance'] ) ? floatval( wp_unslash( $_POST['allowance'] ) ) : 0.00;
        $freq     = isset( $_POST['pay_frequency'] ) ? sanitize_text_field( wp_unslash( $_POST['pay_frequency'] ) ) : 'Monthly';
        $eff_date = isset( $_POST['effective_date'] ) ? sanitize_text_field( wp_unslash( $_POST['effective_date'] ) ) : current_time( 'Y-m-d' );

        if ( ! empty( $username ) && is_email( $email ) && ! empty( $password ) ) {
            $user_id = wp_create_user( $username, $password, $email );
            if ( ! is_wp_error( $user_id ) ) {
                wp_update_user( array(
                    'ID'           => $user_id,
                    'display_name' => $fullname ? $fullname : $username,
                    'role'         => $role,
                ) );

                if ( ! empty( $avatar ) ) {
                    update_user_meta( $user_id, 'ifs_pms_custom_avatar', $avatar );
                }

                $wpdb->insert( $t_salary, array(
                    'user_id'        => $user_id,
                    'base_salary'    => $base_sal,
                    'allowance'      => $allow,
                    'pay_frequency'  => $freq,
                    'effective_date' => $eff_date,
                    'status'         => 'Active',
                ), array( '%d', '%f', '%f', '%s', '%s', '%s' ) );

                wp_safe_redirect( add_query_arg( array( 'msg' => 'staff_created', 'tab' => 'list' ), $base_url ) );
                exit;
            }
        }
        wp_safe_redirect( add_query_arg( array( 'msg' => 'error', 'tab' => 'add' ), $base_url ) );
        exit;
    }

    // Edit Operator
    if ( 'edit_staff' === $action ) {
        $user_id  = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;
        $fullname = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
        $email    = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
        $avatar   = isset( $_POST['user_avatar_url'] ) ? esc_url_raw( wp_unslash( $_POST['user_avatar_url'] ) ) : '';

        $requested_role = isset( $_POST['user_role'] ) ? sanitize_key( wp_unslash( $_POST['user_role'] ) ) : 'ozone_cashier';
        $role           = in_array( $requested_role, array( 'ozone_cashier', 'administrator' ), true ) ? $requested_role : 'ozone_cashier';

        $new_pass = isset( $_POST['user_pass'] ) ? trim( (string) wp_unslash( $_POST['user_pass'] ) ) : '';
        $base_sal = isset( $_POST['base_salary'] ) ? floatval( wp_unslash( $_POST['base_salary'] ) ) : 0.00;
        $allow    = isset( $_POST['allowance'] ) ? floatval( wp_unslash( $_POST['allowance'] ) ) : 0.00;
        $freq     = isset( $_POST['pay_frequency'] ) ? sanitize_text_field( wp_unslash( $_POST['pay_frequency'] ) ) : 'Monthly';
        $eff_date = isset( $_POST['effective_date'] ) ? sanitize_text_field( wp_unslash( $_POST['effective_date'] ) ) : current_time( 'Y-m-d' );

        if ( $user_id > 0 && is_email( $email ) ) {
            $user_data = array(
                'ID'           => $user_id,
                'user_email'   => $email,
                'display_name' => $fullname,
            );
            if ( ! empty( $new_pass ) ) {
                $user_data['user_pass'] = $new_pass;
            }
            wp_update_user( $user_data );

            $user = get_userdata( $user_id );
            if ( $user ) {
                $user->set_role( $role );
            }

            update_user_meta( $user_id, 'ifs_pms_custom_avatar', $avatar );

            $existing_sal = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_salary} WHERE user_id = %d", $user_id ) );
            if ( $existing_sal ) {
                $wpdb->update( $t_salary, array(
                    'base_salary'    => $base_sal,
                    'allowance'      => $allow,
                    'pay_frequency'  => $freq,
                    'effective_date' => $eff_date,
                ), array( 'user_id' => $user_id ), array( '%f', '%f', '%s', '%s' ), array( '%d' ) );
            } else {
                $wpdb->insert( $t_salary, array(
                    'user_id'        => $user_id,
                    'base_salary'    => $base_sal,
                    'allowance'      => $allow,
                    'pay_frequency'  => $freq,
                    'effective_date' => $eff_date,
                    'status'         => 'Active',
                ), array( '%d', '%f', '%f', '%s', '%s', '%s' ) );
            }

            wp_safe_redirect( add_query_arg( array( 'msg' => 'staff_updated', 'tab' => 'list' ), $base_url ) );
            exit;
        }
    }

    // Delete Operator
    if ( 'delete_staff' === $action ) {
        $user_id = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;
        if ( $user_id > 0 && $user_id !== get_current_user_id() ) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
            wp_delete_user( $user_id );
            $wpdb->delete( $t_salary, array( 'user_id' => $user_id ), array( '%d' ) );
            wp_safe_redirect( add_query_arg( array( 'tab' => 'list' ), $base_url ) );
            exit;
        }
    }
}