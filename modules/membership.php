<?php
/**
 * Module: Membership & Subscriptions
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_init', 'ifs_pms_handle_membership_submissions' );

function ifs_pms_handle_membership_submissions() {
    if ( ! isset( $_POST['ifs_pms_action'] ) ) {
        return;
    }

    $action = sanitize_key( wp_unslash( $_POST['ifs_pms_action'] ) );
    if ( ! in_array( $action, array( 'create_membership', 'edit_membership', 'delete_membership' ), true ) ) {
        return;
    }

    check_admin_referer( 'ifs_pms_secure_action', 'ifs_pms_action_nonce' );

    global $wpdb;
    $t_members = $wpdb->prefix . 'ifs_pms_memberships';
    $base_url  = admin_url( 'admin.php?page=ifs-pms-membership' );

    // Enroll Member
    if ( 'create_membership' === $action ) {
        if ( ! current_user_can( 'ozone_manage_patrons' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Forbidden: Insufficient privileges.', 'swimming-pool-manager' ), 403 );
        }

        $name          = isset( $_POST['m_name'] ) ? sanitize_text_field( wp_unslash( $_POST['m_name'] ) ) : '';
        $phone         = isset( $_POST['m_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['m_phone'] ) ) : '';
        $plan          = isset( $_POST['plan_type'] ) ? sanitize_text_field( wp_unslash( $_POST['plan_type'] ) ) : '';
        $amount        = isset( $_POST['m_amount'] ) ? max( 0.00, floatval( wp_unslash( $_POST['m_amount'] ) ) ) : 0.00;
        $duration      = isset( $_POST['duration_months'] ) ? max( 1, absint( wp_unslash( $_POST['duration_months'] ) ) ) : 1;
        $profile_image = isset( $_POST['profile_image'] ) ? esc_url_raw( wp_unslash( $_POST['profile_image'] ) ) : '';

        $mem_code = 'OZONE-MEM-' . strtoupper( wp_generate_password( 6, false, false ) );
        $start    = current_time( 'Y-m-d' );
        $expiry   = gmdate( 'Y-m-d', strtotime( "+{$duration} months", strtotime( $start ) ) );

        $wpdb->insert(
            $t_members,
            array(
                'member_code'   => $mem_code,
                'name'          => $name,
                'phone'         => $phone,
                'plan_type'     => $plan,
                'amount'        => $amount,
                'start_date'    => $start,
                'expiry_date'   => $expiry,
                'status'        => 'Active',
                'profile_image' => $profile_image,
            ),
            array( '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s' )
        );

        wp_safe_redirect( add_query_arg( array( 'msg' => 'membership_enrolled', 'tab' => 'list' ), $base_url ) );
        exit;
    }

    // Edit Member Subscription
    if ( 'edit_membership' === $action ) {
        if ( ! current_user_can( 'ozone_manage_patrons' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Forbidden: Insufficient privileges.', 'swimming-pool-manager' ), 403 );
        }

        $member_id     = isset( $_POST['member_id'] ) ? absint( wp_unslash( $_POST['member_id'] ) ) : 0;
        $name          = isset( $_POST['m_name'] ) ? sanitize_text_field( wp_unslash( $_POST['m_name'] ) ) : '';
        $phone         = isset( $_POST['m_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['m_phone'] ) ) : '';
        $plan          = isset( $_POST['plan_type'] ) ? sanitize_text_field( wp_unslash( $_POST['plan_type'] ) ) : '';
        $amount        = isset( $_POST['m_amount'] ) ? max( 0.00, floatval( wp_unslash( $_POST['m_amount'] ) ) ) : 0.00;
        $expiry        = isset( $_POST['expiry_date'] ) ? sanitize_text_field( wp_unslash( $_POST['expiry_date'] ) ) : '';
        $status        = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'Active';
        $profile_image = isset( $_POST['profile_image'] ) ? esc_url_raw( wp_unslash( $_POST['profile_image'] ) ) : '';

        if ( $member_id > 0 && ! empty( $name ) && ! empty( $phone ) ) {
            $wpdb->update(
                $t_members,
                array(
                    'name'          => $name,
                    'phone'         => $phone,
                    'plan_type'     => $plan,
                    'amount'        => $amount,
                    'expiry_date'   => $expiry,
                    'status'        => $status,
                    'profile_image' => $profile_image,
                ),
                array( 'id' => $member_id ),
                array( '%s', '%s', '%s', '%f', '%s', '%s', '%s' ),
                array( '%d' )
            );

            wp_safe_redirect( add_query_arg( array( 'msg' => 'membership_enrolled', 'tab' => 'list' ), $base_url ) );
            exit;
        }
    }

    // Revoke Member
    if ( 'delete_membership' === $action ) {
        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'ozone_manage_settings' ) ) {
            wp_die( esc_html__( 'Forbidden: Only managers can delete member accounts.', 'swimming-pool-manager' ), 403 );
        }

        $member_id = isset( $_POST['member_id'] ) ? absint( wp_unslash( $_POST['member_id'] ) ) : 0;
        if ( $member_id > 0 ) {
            $wpdb->delete( $t_members, array( 'id' => $member_id ), array( '%d' ) );
            wp_safe_redirect( add_query_arg( array( 'tab' => 'list' ), $base_url ) );
            exit;
        }
    }
}