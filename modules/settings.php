<?php
/**
 * Module: Terminal Settings & Venue Config
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_init', 'ifs_pms_handle_settings_submissions' );

function ifs_pms_handle_settings_submissions() {
    if ( ! isset( $_POST['ifs_pms_action'] ) || 'save_settings' !== $_POST['ifs_pms_action'] ) {
        return;
    }

    check_admin_referer( 'ifs_pms_secure_action', 'ifs_pms_action_nonce' );

    if ( ! current_user_can( 'ozone_manage_settings' ) && ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Forbidden: Insufficient privileges.', 'swimming-pool-manager' ), 403 );
    }

    $base_url   = admin_url( 'admin.php?page=ifs-pms-settings' );
    $active_tab = isset( $_POST['ifs_pms_active_tab'] ) ? sanitize_key( wp_unslash( $_POST['ifs_pms_active_tab'] ) ) : 'pos';

    update_option( 'ifs_pms_business_name', isset( $_POST['ifs_pms_business_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ifs_pms_business_name'] ) ) : '' );
    update_option( 'ifs_pms_currency', isset( $_POST['ifs_pms_currency'] ) ? sanitize_text_field( wp_unslash( $_POST['ifs_pms_currency'] ) ) : '' );
    update_option( 'ifs_pms_phone', isset( $_POST['ifs_pms_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['ifs_pms_phone'] ) ) : '' );
    update_option( 'ifs_pms_address', isset( $_POST['ifs_pms_address'] ) ? sanitize_text_field( wp_unslash( $_POST['ifs_pms_address'] ) ) : '' );
    update_option( 'ifs_pms_max_capacity', isset( $_POST['ifs_pms_max_capacity'] ) ? sanitize_text_field( wp_unslash( $_POST['ifs_pms_max_capacity'] ) ) : '' );
    update_option( 'ifs_pms_receipt_note', isset( $_POST['ifs_pms_receipt_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ifs_pms_receipt_note'] ) ) : '' );
    update_option( 'ifs_pms_logo_url', isset( $_POST['ifs_pms_logo_url'] ) ? esc_url_raw( wp_unslash( $_POST['ifs_pms_logo_url'] ) ) : '' );

    update_option( 'ifs_pms_enable_strict_scan', isset( $_POST['ifs_pms_enable_strict_scan'] ) ? '1' : '0' );
    update_option( 'ifs_pms_auto_expire_passes', isset( $_POST['ifs_pms_auto_expire_passes'] ) ? '1' : '0' );
    update_option( 'ifs_pms_thermal_paper_width', isset( $_POST['ifs_pms_thermal_paper_width'] ) ? sanitize_text_field( wp_unslash( $_POST['ifs_pms_thermal_paper_width'] ) ) : '' );
    update_option( 'ifs_pms_enable_audio_buzzer', isset( $_POST['ifs_pms_enable_audio_buzzer'] ) ? '1' : '0' );
    update_option( 'ifs_pms_enable_amenities', isset( $_POST['ifs_pms_enable_amenities'] ) ? '1' : '0' );
    update_option( 'ifs_pms_pool_status', isset( $_POST['ifs_pms_pool_status'] ) ? sanitize_text_field( wp_unslash( $_POST['ifs_pms_pool_status'] ) ) : '' );

    $tenders = ( isset( $_POST['ifs_pms_enabled_tenders'] ) && is_array( $_POST['ifs_pms_enabled_tenders'] ) )
        ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ifs_pms_enabled_tenders'] ) )
        : array();
    update_option( 'ifs_pms_enabled_tenders', $tenders );

    $cash_presets = isset( $_POST['ifs_pms_quick_cash_presets'] ) ? sanitize_text_field( wp_unslash( $_POST['ifs_pms_quick_cash_presets'] ) ) : '';
    update_option( 'ifs_pms_quick_cash_presets', $cash_presets );

    // Dynamic Pricing Tiers
    if ( isset( $_POST['ifs_pricing_tier_name'] ) && is_array( $_POST['ifs_pricing_tier_name'] ) ) {
        $tiers         = array();
        $posted_names  = array_map( 'sanitize_text_field', wp_unslash( $_POST['ifs_pricing_tier_name'] ) );
        $posted_ages   = ( isset( $_POST['ifs_pricing_tier_age'] ) && is_array( $_POST['ifs_pricing_tier_age'] ) )
            ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ifs_pricing_tier_age'] ) )
            : array();
        $posted_prices = ( isset( $_POST['ifs_pricing_tier_price'] ) && is_array( $_POST['ifs_pricing_tier_price'] ) )
            ? array_map( 'floatval', wp_unslash( $_POST['ifs_pricing_tier_price'] ) )
            : array();

        $names  = array_values( $posted_names );
        $ages   = array_values( $posted_ages );
        $prices = array_values( $posted_prices );

        for ( $i = 0; $i < count( $names ); $i++ ) {
            $item_name = trim( $names[ $i ] ?? '' );
            if ( ! empty( $item_name ) ) {
                $tiers[] = array(
                    'name'      => $item_name,
                    'age_group' => $ages[ $i ] ?? '',
                    'price'     => max( 0.00, $prices[ $i ] ?? 0.00 ),
                );
            }
        }
        update_option( 'ifs_pms_pricing_tiers', $tiers );
    } else {
        update_option( 'ifs_pms_pricing_tiers', array() );
    }

    // Dynamic Amenity Add-Ons
    if ( isset( $_POST['ifs_addon_name'] ) && is_array( $_POST['ifs_addon_name'] ) ) {
        $addons         = array();
        $posted_anames  = array_map( 'sanitize_text_field', wp_unslash( $_POST['ifs_addon_name'] ) );
        $posted_aprices = ( isset( $_POST['ifs_addon_price'] ) && is_array( $_POST['ifs_addon_price'] ) )
            ? array_map( 'floatval', wp_unslash( $_POST['ifs_addon_price'] ) )
            : array();

        $a_names  = array_values( $posted_anames );
        $a_prices = array_values( $posted_aprices );

        for ( $j = 0; $j < count( $a_names ); $j++ ) {
            $add_name = trim( $a_names[ $j ] ?? '' );
            if ( ! empty( $add_name ) ) {
                $addons[] = array(
                    'name'  => $add_name,
                    'price' => max( 0.00, $a_prices[ $j ] ?? 0.00 ),
                );
            }
        }
        update_option( 'ifs_pms_amenity_addons', $addons );
    } else {
        update_option( 'ifs_pms_amenity_addons', array() );
    }

    // Weekly Schedule
    $days_of_week = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
    $schedule     = array();
    foreach ( $days_of_week as $day ) {
        $status_key = 'ifs_pms_schedule_' . $day . '_status';
        $open_key   = 'ifs_pms_schedule_' . $day . '_open';
        $close_key  = 'ifs_pms_schedule_' . $day . '_close';

        $schedule[ $day ] = array(
            'status' => isset( $_POST[ $status_key ] ) ? sanitize_key( wp_unslash( $_POST[ $status_key ] ) ) : 'open',
            'open'   => isset( $_POST[ $open_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $open_key ] ) ) : '08:00',
            'close'  => isset( $_POST[ $close_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $close_key ] ) ) : '20:00',
        );
    }
    update_option( 'ifs_pms_weekly_schedule', $schedule );

    wp_safe_redirect( add_query_arg( array( 'msg' => 'settings_saved', 'tab' => $active_tab ), $base_url ) );
    exit;
}