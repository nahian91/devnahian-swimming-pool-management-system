<?php
/**
 * Module: Expenses & Financial Outflows
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_init', 'ifs_pms_handle_expense_submissions' );

function ifs_pms_handle_expense_submissions() {
    if ( ! isset( $_POST['ifs_pms_action'] ) ) {
        return;
    }

    $action = sanitize_key( wp_unslash( $_POST['ifs_pms_action'] ) );
    if ( ! in_array( $action, array( 'add_expense', 'edit_expense', 'delete_expense' ), true ) ) {
        return;
    }

    check_admin_referer( 'ifs_pms_secure_action', 'ifs_pms_action_nonce' );

    global $wpdb;
    $t_expense = $wpdb->prefix . 'ifs_pms_expenses';
    $base_url  = admin_url( 'admin.php?page=ifs-pms-expenses' );

    // Add Expense
    if ( 'add_expense' === $action ) {
        if ( ! current_user_can( 'ozone_manage_expenses' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Forbidden: Insufficient privileges.', 'swimming-pool-manager' ), 403 );
        }

        $wpdb->insert(
            $t_expense,
            array(
                'title'        => isset( $_POST['expense_title'] ) ? sanitize_text_field( wp_unslash( $_POST['expense_title'] ) ) : '',
                'category'     => isset( $_POST['expense_cat'] ) ? sanitize_text_field( wp_unslash( $_POST['expense_cat'] ) ) : 'Operations',
                'amount'       => isset( $_POST['expense_amount'] ) ? max( 0.00, floatval( wp_unslash( $_POST['expense_amount'] ) ) ) : 0.00,
                'expense_date' => isset( $_POST['expense_date'] ) ? sanitize_text_field( wp_unslash( $_POST['expense_date'] ) ) : current_time( 'Y-m-d' ),
                'added_by'     => wp_get_current_user()->display_name,
            ),
            array( '%s', '%s', '%f', '%s', '%s' )
        );

        wp_safe_redirect( add_query_arg( array( 'msg' => 'expense_logged', 'tab' => 'list' ), $base_url ) );
        exit;
    }

    // Edit Expense
    if ( 'edit_expense' === $action ) {
        if ( ! current_user_can( 'ozone_manage_expenses' ) && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Forbidden: Insufficient privileges.', 'swimming-pool-manager' ), 403 );
        }

        $expense_id = isset( $_POST['expense_id'] ) ? absint( wp_unslash( $_POST['expense_id'] ) ) : 0;
        $title      = isset( $_POST['expense_title'] ) ? sanitize_text_field( wp_unslash( $_POST['expense_title'] ) ) : '';
        $category   = isset( $_POST['expense_cat'] ) ? sanitize_text_field( wp_unslash( $_POST['expense_cat'] ) ) : 'Operations';
        $amount     = isset( $_POST['expense_amount'] ) ? max( 0.00, floatval( wp_unslash( $_POST['expense_amount'] ) ) ) : 0.00;
        $date       = isset( $_POST['expense_date'] ) ? sanitize_text_field( wp_unslash( $_POST['expense_date'] ) ) : current_time( 'Y-m-d' );

        if ( $expense_id > 0 && ! empty( $title ) && $amount > 0 ) {
            $wpdb->update(
                $t_expense,
                array(
                    'title'        => $title,
                    'category'     => $category,
                    'amount'       => $amount,
                    'expense_date' => $date,
                ),
                array( 'id' => $expense_id ),
                array( '%s', '%s', '%f', '%s' ),
                array( '%d' )
            );

            wp_safe_redirect( add_query_arg( array( 'msg' => 'expense_logged', 'tab' => 'list' ), $base_url ) );
            exit;
        }
    }

    // Delete Expense
    if ( 'delete_expense' === $action ) {
        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'ozone_manage_settings' ) ) {
            wp_die( esc_html__( 'Forbidden: Only managers can delete expense logs.', 'swimming-pool-manager' ), 403 );
        }

        $expense_id = isset( $_POST['expense_id'] ) ? absint( wp_unslash( $_POST['expense_id'] ) ) : 0;
        if ( $expense_id > 0 ) {
            $wpdb->delete( $t_expense, array( 'id' => $expense_id ), array( '%d' ) );
            wp_safe_redirect( add_query_arg( array( 'tab' => 'list' ), $base_url ) );
            exit;
        }
    }
}

/**
 * CSV Ledger Exporter AJAX
 */
add_action( 'wp_ajax_ifs_pms_export_csv_action', 'ifs_pms_export_csv_action_callback' );

function ifs_pms_export_csv_action_callback() {
    check_ajax_referer( 'ifs_pms_security_token', 'security' );

    if ( ! current_user_can( 'ozone_view_finances' ) && ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Unauthorized access.', 'swimming-pool-manager' ), 403 );
    }

    global $wpdb;
    $t_tick = $wpdb->prefix . 'ifs_pms_tickets';
    $t_cust = $wpdb->prefix . 'ifs_pms_customers';

    $tickets = $wpdb->get_results(
        "SELECT t.ticket_code, c.name as customer_name, c.phone as customer_phone, t.guest_type, t.package_details, t.duration_hours, t.payment_method, t.room_no, t.amount, t.sold_by, t.status, t.sold_at, t.scanned_at, t.scanned_by 
         FROM {$t_tick} t 
         LEFT JOIN {$t_cust} c ON t.customer_id = c.id 
         ORDER BY t.id DESC",
        ARRAY_A
    );

    nocache_headers();
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=ozone_skypool_ledger_' . gmdate( 'Y-m-d' ) . '.csv' );

    $output = fopen( 'php://output', 'w' );
    if ( false !== $output ) {
        fputcsv( $output, array( 'Ticket Code', 'Patron Name', 'Phone', 'Guest Type', 'Packages Enrolled', 'Hours', 'Payment Method', 'Room Number', 'Amount', 'Sold By', 'Status', 'Sold At', 'Scanned At', 'Scanned By' ) );

        if ( ! empty( $tickets ) ) {
            foreach ( $tickets as $row ) {
                fputcsv( $output, array(
                    $row['ticket_code'], $row['customer_name'],
                    $row['customer_phone'], $row['guest_type'],
                    $row['package_details'], $row['duration_hours'],
                    $row['payment_method'], $row['room_no'],
                    $row['amount'], $row['sold_by'],
                    $row['status'], $row['sold_at'],
                    $row['scanned_at'], $row['scanned_by'],
                ) );
            }
        }
        fclose( $output );
    }
    exit;
}