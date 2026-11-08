<?php
/**
 * Module: Tickets, Gate Validation & Terminal Pass System
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_init', 'ifs_pms_handle_ticket_submissions' );

function ifs_pms_handle_ticket_submissions() {
    if ( ! isset( $_POST['ifs_pms_action'] ) ) {
        return;
    }

    $action = sanitize_key( wp_unslash( $_POST['ifs_pms_action'] ) );
    if ( ! in_array( $action, array( 'issue_ticket', 'edit_ticket', 'delete_ticket' ), true ) ) {
        return;
    }

    check_admin_referer( 'ifs_pms_secure_action', 'ifs_pms_action_nonce' );

    global $wpdb;
    $t_cust   = $wpdb->prefix . 'ifs_pms_customers';
    $t_tick   = $wpdb->prefix . 'ifs_pms_tickets';
    $base_url = admin_url( 'admin.php?page=ifs-pms-tickets' );

    // Issue Single / Multi Pass
    if ( 'issue_ticket' === $action ) {
        if ( ! current_user_can( 'ozone_sell_tickets' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Forbidden: Insufficient privileges.', 'swimming-pool-manager' ), 403 );
        }

        $name           = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $phone          = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $guest_type     = isset( $_POST['guest_type'] ) ? sanitize_key( wp_unslash( $_POST['guest_type'] ) ) : 'customer';
        $package_name   = isset( $_POST['package_name'] ) ? sanitize_text_field( wp_unslash( $_POST['package_name'] ) ) : '';
        $duration_hours = isset( $_POST['duration_hours'] ) ? max( 1, absint( wp_unslash( $_POST['duration_hours'] ) ) ) : 1;
        $payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : 'Cash';
        $room_no        = isset( $_POST['room_no'] ) ? sanitize_text_field( wp_unslash( $_POST['room_no'] ) ) : '';

        $is_free = ( 'room_guest' === $guest_type || 'Room Guest' === $payment_method || 'Complementary' === $payment_method );
        $amount  = $is_free ? 0.00 : ( isset( $_POST['amount'] ) ? max( 0.00, floatval( wp_unslash( $_POST['amount'] ) ) ) : 0.00 );
        $staff   = wp_get_current_user()->display_name;

        if ( ! empty( $name ) && ! empty( $phone ) ) {
            $customer = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$t_cust} WHERE phone = %s", $phone ) );
            if ( ! $customer ) {
                $wpdb->insert( $t_cust, array( 'name' => $name, 'phone' => $phone ), array( '%s', '%s' ) );
                $cust_id = $wpdb->insert_id;
            } else {
                $cust_id = $customer->id;
                $wpdb->update( $t_cust, array( 'name' => $name ), array( 'id' => $cust_id ), array( '%s', '%d' ) );
            }

            $today_start = current_time( 'Y-m-d 00:00:00' );
            $today_end   = current_time( 'Y-m-d 23:59:59' );
            $today_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$t_tick} WHERE sold_at >= %s AND sold_at <= %s", $today_start, $today_end ) );

            $code      = 'OZ-' . strtoupper( current_time( 'M' ) ) . '-' . current_time( 'd' ) . '-' . str_pad( (string) ( $today_count + 1 ), 4, '0', STR_PAD_LEFT );
            $now_mysql = current_time( 'mysql' );

            $inserted = $wpdb->insert( $t_tick, array(
                'ticket_code'     => $code,
                'customer_id'     => $cust_id,
                'guest_type'      => $guest_type,
                'package_details' => $package_name,
                'duration_hours'  => $duration_hours,
                'payment_method'  => $payment_method,
                'room_no'         => $room_no,
                'amount'          => $amount,
                'sold_by'         => $staff,
                'status'          => 'Valid',
                'sold_at'         => $now_mysql,
            ), array( '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%f', '%s', '%s', '%s' ) );

            if ( $inserted ) {
                $ticket_id      = $wpdb->insert_id;
                $check_out_time = gmdate( 'Y-m-d H:i:s', strtotime( "+{$duration_hours} hours", strtotime( $now_mysql ) ) );

                set_transient( 'ifs_pms_last_ticket_' . get_current_user_id(), array(
                    'code'           => $code,
                    'name'           => $name,
                    'phone'          => $phone,
                    'guest_type'     => $guest_type,
                    'package'        => $package_name,
                    'duration_hours' => $duration_hours,
                    'payment_method' => $payment_method,
                    'room'           => $room_no,
                    'amount'         => number_format( $amount, 2 ),
                    'staff'          => $staff,
                    'check_in'       => $now_mysql,
                    'check_out'      => $check_out_time,
                ), 120 );

                wp_safe_redirect( add_query_arg( array( 'msg' => 'ticket_created', 'tab' => 'add', 'issued_ticket_id' => $ticket_id ), $base_url ) );
                exit;
            }
        }
    }

    // Edit Ticket
    if ( 'edit_ticket' === $action ) {
        if ( ! current_user_can( 'ozone_sell_tickets' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Forbidden: Insufficient privileges.', 'swimming-pool-manager' ), 403 );
        }

        $ticket_id = isset( $_POST['ticket_id'] ) ? absint( wp_unslash( $_POST['ticket_id'] ) ) : 0;
        $name      = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $phone     = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $room_no   = isset( $_POST['room_no'] ) ? sanitize_text_field( wp_unslash( $_POST['room_no'] ) ) : '';
        $amount    = isset( $_POST['amount'] ) ? max( 0.00, floatval( wp_unslash( $_POST['amount'] ) ) ) : 0.00;

        $requested_status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'Valid';
        $status           = in_array( $requested_status, array( 'Valid', 'Used', 'Cancelled', 'Completed' ), true ) ? $requested_status : 'Valid';

        if ( $ticket_id > 0 && ! empty( $name ) && ! empty( $phone ) ) {
            $ticket = $wpdb->get_row( $wpdb->prepare( "SELECT customer_id FROM {$t_tick} WHERE id = %d", $ticket_id ) );
            if ( $ticket ) {
                $wpdb->update( $t_cust, array( 'name' => $name, 'phone' => $phone ), array( 'id' => $ticket->customer_id ), array( '%s', '%s' ), array( '%d' ) );
                $wpdb->update( $t_tick, array(
                    'room_no' => $room_no,
                    'amount'  => $amount,
                    'status'  => $status,
                ), array( 'id' => $ticket_id ), array( '%s', '%f', '%s' ), array( '%d' ) );
            }
            wp_safe_redirect( add_query_arg( array( 'msg' => 'ticket_updated', 'tab' => 'list' ), $base_url ) );
            exit;
        }
    }

    // Delete Ticket
    if ( 'delete_ticket' === $action ) {
        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'ozone_manage_settings' ) ) {
            wp_die( esc_html__( 'Forbidden: Only managers can delete ticket records.', 'swimming-pool-manager' ), 403 );
        }

        $ticket_id = isset( $_POST['ticket_id'] ) ? absint( wp_unslash( $_POST['ticket_id'] ) ) : 0;
        if ( $ticket_id > 0 ) {
            $wpdb->delete( $t_tick, array( 'id' => $ticket_id ), array( '%d' ) );
            wp_safe_redirect( add_query_arg( array( 'msg' => 'ticket_deleted', 'tab' => 'list' ), $base_url ) );
            exit;
        }
    }
}

/**
 * QR/Turnstile Pass Verification AJAX
 */
add_action( 'wp_ajax_ifs_pms_verify_pass_action', 'ifs_pms_verify_pass_callback' );

function ifs_pms_verify_pass_callback() {
    check_ajax_referer( 'ifs_pms_security_token', 'security' );

    if ( ! current_user_can( 'ozone_scan_passes' ) && ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Forbidden: Insufficient privileges.', 'swimming-pool-manager' ) ), 403 );
    }

    global $wpdb;
    $t_tick = $wpdb->prefix . 'ifs_pms_tickets';
    $t_cust = $wpdb->prefix . 'ifs_pms_customers';
    $code   = isset( $_POST['ticket_code'] ) ? sanitize_text_field( wp_unslash( $_POST['ticket_code'] ) ) : '';

    if ( empty( $code ) ) {
        wp_send_json_error( array( 'message' => __( 'Missing pass identification parameter.', 'swimming-pool-manager' ) ) );
    }

    $now   = current_time( 'mysql' );
    $staff = wp_get_current_user()->display_name;

    $ticket = $wpdb->get_row( $wpdb->prepare(
        "SELECT t.*, c.name as customer_name, c.phone as customer_phone 
         FROM {$t_tick} t 
         LEFT JOIN {$t_cust} c ON t.customer_id = c.id 
         WHERE t.ticket_code = %s",
        $code
    ) );

    if ( ! $ticket ) {
        wp_send_json_error( array( 'message' => __( 'Unrecognized / Forged Pass ID.', 'swimming-pool-manager' ) ) );
    }

    $duration_hours = max( 1, (int) $ticket->duration_hours );
    $sold_epoch     = strtotime( $ticket->sold_at );
    $checkout_epoch = $sold_epoch + ( $duration_hours * 3600 );
    $now_epoch      = current_time( 'timestamp' );
    $is_overstay    = ( $now_epoch > $checkout_epoch );

    if ( 'Valid' === $ticket->status ) {
        $wpdb->update(
            $t_tick,
            array(
                'status'     => 'Used',
                'scanned_at' => $now,
                'scanned_by' => $staff,
            ),
            array( 'id' => $ticket->id )
        );

        wp_send_json_success( array(
            'message'        => __( 'ACCESS GRANTED • TURNSTILE UNLOCKED', 'swimming-pool-manager' ),
            'ticket_code'    => $ticket->ticket_code,
            'customer_name'  => ! empty( $ticket->customer_name ) ? esc_html( $ticket->customer_name ) : __( 'Walk-in Guest', 'swimming-pool-manager' ),
            'customer_phone' => ! empty( $ticket->customer_phone ) ? esc_html( $ticket->customer_phone ) : '-',
            'guest_type'     => ! empty( $ticket->guest_type ) ? $ticket->guest_type : 'customer',
            'room_no'        => ! empty( $ticket->room_no ) ? $ticket->room_no : '',
            'duration_hours' => $duration_hours,
            'check_in'       => gmdate( 'h:i A', $sold_epoch ),
            'check_out'      => gmdate( 'h:i A', $checkout_epoch ),
            'overstay'       => false,
        ) );
    } elseif ( 'Used' === $ticket->status ) {
        wp_send_json_error( array(
            'message'        => $is_overstay 
                ? sprintf( __( 'OVERSTAY DETECTED: Pass session expired at %s', 'swimming-pool-manager' ), gmdate( 'h:i A', $checkout_epoch ) ) 
                : sprintf( __( 'Pass already redeemed at %s', 'swimming-pool-manager' ), gmdate( 'h:i A', strtotime( $ticket->scanned_at ) ) ),
            'ticket_code'    => $ticket->ticket_code,
            'customer_name'  => ! empty( $ticket->customer_name ) ? esc_html( $ticket->customer_name ) : __( 'Walk-in Guest', 'swimming-pool-manager' ),
            'customer_phone' => ! empty( $ticket->customer_phone ) ? esc_html( $ticket->customer_phone ) : '-',
            'guest_type'     => ! empty( $ticket->guest_type ) ? $ticket->guest_type : 'customer',
            'room_no'        => ! empty( $ticket->room_no ) ? $ticket->room_no : '',
            'duration_hours' => $duration_hours,
            'check_in'       => gmdate( 'h:i A', $sold_epoch ),
            'check_out'      => gmdate( 'h:i A', $checkout_epoch ),
            'overstay'       => $is_overstay,
        ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Pass has been voided or refunded.', 'swimming-pool-manager' ) ) );
    }
}

/**
 * Live Floor Checkout AJAX
 */
add_action( 'wp_ajax_ifs_pms_checkout_swimmer_ajax', 'ifs_pms_checkout_swimmer_callback' );

function ifs_pms_checkout_swimmer_callback() {
    $nonce = $_POST['security'] ?? $_POST['_ajax_nonce'] ?? '';
    if ( ! wp_verify_nonce( $nonce, 'ifs_pms_security_token' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'swimming-pool-manager' ) ), 403 );
    }

    if ( ! current_user_can( 'ozone_scan_passes' ) && ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Forbidden: Insufficient privileges.', 'swimming-pool-manager' ) ), 403 );
    }

    global $wpdb;
    $t_tick    = $wpdb->prefix . 'ifs_pms_tickets';
    $ticket_id = isset( $_POST['ticket_id'] ) ? absint( $_POST['ticket_id'] ) : 0;

    if ( $ticket_id <= 0 ) {
        wp_send_json_error( array( 'message' => __( 'Invalid pass identification parameter.', 'swimming-pool-manager' ) ) );
    }

    $updated = $wpdb->update(
        $t_tick,
        array( 'status' => 'Completed' ),
        array( 'id' => $ticket_id ),
        array( '%s' ),
        array( '%d' )
    );

    if ( false === $updated ) {
        wp_send_json_error( array( 'message' => __( 'Database error during checkout processing.', 'swimming-pool-manager' ) ) );
    }

    wp_send_json_success( array( 'message' => __( 'Patron checked out successfully.', 'swimming-pool-manager' ) ) );
}