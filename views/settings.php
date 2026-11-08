<?php
/**
 * View: Terminal Configuration & Master Settings (Pro iOS-Toggle UX Edition)
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$currency      = esc_html( (string) get_option( 'ifs_pms_currency', 'BDT' ) );
$b_name        = esc_html( (string) get_option( 'ifs_pms_business_name', 'DevNahian Pool OS' ) );
$phone         = esc_html( (string) get_option( 'ifs_pms_phone', '' ) );
$address       = esc_html( (string) get_option( 'ifs_pms_address', '' ) );
$capacity_raw  = get_option( 'ifs_pms_max_capacity', '' );
$capacity      = ( '' !== $capacity_raw && is_numeric( $capacity_raw ) ) ? (int) $capacity_raw : '';
$receipt_note  = esc_textarea( (string) get_option( 'ifs_pms_receipt_note', '' ) );
$logo_url      = esc_attr( (string) get_option( 'ifs_pms_logo_url', '' ) );
$base_url      = admin_url( 'admin.php?page=ifs-pms-settings' );

// POS Operational States & Toggles - Strictly Pulled from Database
$active_tab          = sanitize_key( $_GET['tab'] ?? 'pos' );
$enable_strict_scan  = (string) get_option( 'ifs_pms_enable_strict_scan', '1' );
$auto_expire_passes  = (string) get_option( 'ifs_pms_auto_expire_passes', '1' );
$enable_audio_buzzer = (string) get_option( 'ifs_pms_enable_audio_buzzer', '1' );
$enable_amenities    = (string) get_option( 'ifs_pms_enable_amenities', '0' );
$pool_status         = (string) get_option( 'ifs_pms_pool_status', 'open' );

$pricing_tiers   = get_option( 'ifs_pms_pricing_tiers', array() );
$amenity_addons  = get_option( 'ifs_pms_amenity_addons', array() );
$enabled_tenders = get_option( 'ifs_pms_enabled_tenders', array( 'Cash' ) );
if ( ! is_array( $enabled_tenders ) ) {
    $enabled_tenders = array();
}

$quick_cash_str = esc_attr( (string) get_option( 'ifs_pms_quick_cash_presets', '500, 1000, 2000' ) );
$paper_width    = (string) get_option( 'ifs_pms_thermal_paper_width', '80mm' );

// Operating Days
$days_map = array(
    'monday'    => __( 'Monday', 'swimming-pool-manager' ),
    'tuesday'   => __( 'Tuesday', 'swimming-pool-manager' ),
    'wednesday' => __( 'Wednesday', 'swimming-pool-manager' ),
    'thursday'  => __( 'Thursday', 'swimming-pool-manager' ),
    'friday'    => __( 'Friday', 'swimming-pool-manager' ),
    'saturday'  => __( 'Saturday', 'swimming-pool-manager' ),
    'sunday'    => __( 'Sunday', 'swimming-pool-manager' ),
);

$weekly_schedule = get_option( 'ifs_pms_weekly_schedule', array() );
if ( ! is_array( $weekly_schedule ) ) {
    $weekly_schedule = array();
}
?>

<style>
/* Modern iOS Switch Toggle Styling */
.devnahian-pool-ios-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
    cursor: pointer;
}
.devnahian-pool-ios-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.devnahian-pool-ios-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 26px;
}
.devnahian-pool-ios-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.devnahian-pool-ios-switch input:checked + .devnahian-pool-ios-slider {
    background-color: #10b981;
}
.devnahian-pool-ios-switch input:checked + .devnahian-pool-ios-slider:before {
    transform: translateX(22px);
}
</style>

<div class="devnahian-pool-settings-wrapper" style="max-width: 1240px; margin: 0 auto; padding-bottom: 60px;">
    
    <!-- Premium Header Banner -->
    <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-radius: 16px; padding: 24px 32px; color: #fff; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.3); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; opacity: 0.85; margin-bottom: 4px;"><?php esc_html_e( 'System Control Center', 'swimming-pool-manager' ); ?></div>
            <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: #fff;"><?php esc_html_e( 'Terminal Configuration & Master Settings', 'swimming-pool-manager' ); ?></h2>
        </div>
        <!-- Navigation Tabs inside Header -->
        <div class="devnahian-pool-subnav-bar" role="tablist" style="display: flex; gap: 8px; background: rgba(255, 255, 255, 0.15); padding: 6px; border-radius: 12px; backdrop-filter: blur(8px);">
            <button type="button" class="devnahian-pool-subnav-btn <?php echo ( 'pos' === $active_tab ) ? 'active' : ''; ?>" onclick="devnahianPoolSwitchSettingsTab('pos', this)" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; border: none; font-weight: 700; font-size: 13px; cursor: pointer; background: <?php echo ( 'pos' === $active_tab ) ? '#fff' : 'transparent'; ?>; color: <?php echo ( 'pos' === $active_tab ) ? '#0284c7' : '#fff'; ?>; transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
                <span class="dashicons dashicons-tickets-alt" style="font-size: 16px; width: 16px; height: 16px;"></span> <?php esc_html_e( 'POS & Operations', 'swimming-pool-manager' ); ?>
            </button>
            <button type="button" class="devnahian-pool-subnav-btn <?php echo ( 'general' === $active_tab ) ? 'active' : ''; ?>" onclick="devnahianPoolSwitchSettingsTab('general', this)" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; border: none; font-weight: 700; font-size: 13px; cursor: pointer; background: <?php echo ( 'general' === $active_tab ) ? '#fff' : 'transparent'; ?>; color: <?php echo ( 'general' === $active_tab ) ? '#0284c7' : '#fff'; ?>; transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
                <span class="dashicons dashicons-building" style="font-size: 16px; width: 16px; height: 16px;"></span> <?php esc_html_e( 'Venue & Receipt', 'swimming-pool-manager' ); ?>
            </button>
        </div>
    </div>

    <form method="POST" action="<?php echo esc_url( $base_url ); ?>" id="devnahianPoolSettingsForm">
        <?php wp_nonce_field( 'ifs_pms_secure_action', 'ifs_pms_action_nonce' ); ?>
        <input type="hidden" name="ifs_pms_action" value="save_settings">
        <input type="hidden" name="ifs_pms_active_tab" id="devnahianPoolActiveTabInput" value="<?php echo esc_attr( $active_tab ); ?>">

        <!-- TAB 1: POS & Operational Settings -->
        <div id="devnahianPoolSettingsPanePos" class="devnahian-pool-tab-pane" style="display: <?php echo ( 'pos' === $active_tab ) ? 'block' : 'none'; ?>;">
            
            <!-- 1. Admission Package Tiers -->
            <div class="devnahian-pool-panel-card" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04);">
                <div class="devnahian-pool-panel-head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 16px;">
                    <div>
                        <h3 class="devnahian-pool-panel-title" style="margin: 0 0 4px 0; font-size: 16.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <span class="dashicons dashicons-tag" style="color: #0284c7;"></span>
                            <?php esc_html_e( 'Admission Packages & Tier Rates', 'swimming-pool-manager' ); ?>
                        </h3>
                        <p style="margin: 0; font-size: 12.5px; color: #64748b;"><?php esc_html_e( 'Define hourly admission pricing categories available on the front desk POS.', 'swimming-pool-manager' ); ?></p>
                    </div>
                    <button type="button" class="ifs-pms-btn ifs-pms-btn-secondary" onclick="devnahianPoolAddPricingTierRow()" style="background: #f0f9ff; color: #0284c7; border: 1.5px solid #bae6fd; font-weight: 700; padding: 8px 16px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.08);">
                        <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add Package Tier', 'swimming-pool-manager' ); ?>
                    </button>
                </div>

                <div class="devnahian-pool-form-stack">
                    <table class="devnahian-pool-repeater-table" id="devnahianPoolPricingTierTable" style="width: 100%; text-align: left; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e2e8f0; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;">
                                <th style="padding: 12px 10px;"><?php esc_html_e( 'Package Name', 'swimming-pool-manager' ); ?></th>
                                <th style="padding: 12px 10px;"><?php esc_html_e( 'Attendee Category / Age Group', 'swimming-pool-manager' ); ?></th>
                                <th style="padding: 12px 10px;"><?php printf( esc_html__( 'Rate / Hr (%s)', 'swimming-pool-manager' ), esc_html( $currency ) ); ?></th>
                                <th style="padding: 12px 10px; text-align: center;"><?php esc_html_e( 'Action', 'swimming-pool-manager' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ( ! empty( $pricing_tiers ) && is_array( $pricing_tiers ) ) : ?>
                                <?php foreach ( $pricing_tiers as $tier ) : ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 10px;"><input type="text" name="ifs_pricing_tier_name[]" value="<?php echo esc_attr( $tier['name'] ?? '' ); ?>" required placeholder="<?php esc_attr_e( 'Enter package title', 'swimming-pool-manager' ); ?>" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 9px 12px; font-weight: 600;"></td>
                                        <td style="padding: 10px;"><input type="text" name="ifs_pricing_tier_age[]" value="<?php echo esc_attr( $tier['age_group'] ?? '' ); ?>" required placeholder="<?php esc_attr_e( 'Enter age/category group', 'swimming-pool-manager' ); ?>" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 9px 12px; font-weight: 600;"></td>
                                        <td style="padding: 10px;"><input type="number" step="0.01" name="ifs_pricing_tier_price[]" value="<?php echo esc_attr( $tier['price'] ?? '' ); ?>" required class="ifs-pms-mono" placeholder="0.00" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 9px 12px; font-weight: 600;"></td>
                                        <td style="padding: 10px; text-align: center;">
                                            <button type="button" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #fef2f2; color: #ef4444; border: 1.5px solid #fca5a5; padding: 8px 12px; border-radius: 8px; cursor: pointer;" onclick="devnahianPoolRemoveRow(this)">
                                                <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px;"></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr class="devnahian-pool-empty-row">
                                    <td colspan="4" style="text-align: center; padding: 40px; color: #64748b; font-style: italic; background: #f8fafc; border-radius: 8px;">
                                        <span class="dashicons dashicons-tag" style="font-size: 32px; width: 32px; height: 32px; opacity: 0.4; margin-bottom: 8px;"></span><br>
                                        <?php esc_html_e( 'No package tiers registered. Click "Add Package Tier" above to configure.', 'swimming-pool-manager' ); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                <!-- 2. Swimming Pool Status -->
                <div class="devnahian-pool-panel-card" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04); margin-bottom: 0;">
                    <div class="devnahian-pool-panel-head" style="border-bottom: 1.5px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 16px;">
                        <h3 class="devnahian-pool-panel-title" style="margin: 0; font-size: 15.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <span class="dashicons dashicons-groups" style="color: #0284c7;"></span>
                            <?php esc_html_e( 'Pool Operational State', 'swimming-pool-manager' ); ?>
                        </h3>
                    </div>
                    <div class="devnahian-pool-form-stack">
                        <div class="devnahian-pool-field-group">
                            <label style="display:block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 13px;"><?php esc_html_e( 'Select Current Mode', 'swimming-pool-manager' ); ?></label>
                            <select name="ifs_pms_pool_status" id="devnahianPoolPoolStatusSelect" style="width: 100%; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 700; padding: 0 12px; color: #0f172a;">
                                <option value="open" <?php selected( $pool_status, 'open' ); ?>><?php esc_html_e( '🟢 Open (Accepting Visitors & Sales)', 'swimming-pool-manager' ); ?></option>
                                <option value="closed" <?php selected( $pool_status, 'closed' ); ?>><?php esc_html_e( '🔴 Closed (Sales Blocked)', 'swimming-pool-manager' ); ?></option>
                                <option value="maintenance" <?php selected( $pool_status, 'maintenance' ); ?>><?php esc_html_e( '🟡 Maintenance Mode (Servicing Deck)', 'swimming-pool-manager' ); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 3. Thermal Printer Size -->
                <div class="devnahian-pool-panel-card" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04); margin-bottom: 0;">
                    <div class="devnahian-pool-panel-head" style="border-bottom: 1.5px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 16px;">
                        <h3 class="devnahian-pool-panel-title" style="margin: 0; font-size: 15.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <span class="dashicons dashicons-printer" style="color: #0284c7;"></span>
                            <?php esc_html_e( 'Thermal Printer Format', 'swimming-pool-manager' ); ?>
                        </h3>
                    </div>
                    <div class="devnahian-pool-form-stack">
                        <div class="devnahian-pool-field-group">
                            <label style="display:block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 13px;"><?php esc_html_e( 'Receipt Paper Width', 'swimming-pool-manager' ); ?></label>
                            <select name="ifs_pms_thermal_paper_width" id="devnahianPoolThermalPaperWidth" style="width: 100%; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 700; padding: 0 12px; color: #0f172a;">
                                <option value="80mm" <?php selected( $paper_width, '80mm' ); ?>><?php esc_html_e( '80mm Standard POS Slip (Recommended)', 'swimming-pool-manager' ); ?></option>
                                <option value="58mm" <?php selected( $paper_width, '58mm' ); ?>><?php esc_html_e( '58mm Mobile Bluetooth Slip', 'swimming-pool-manager' ); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Day-Wise Operating Hours -->
            <div class="devnahian-pool-panel-card" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04);">
                <div class="devnahian-pool-panel-head" style="border-bottom: 1.5px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 20px;">
                    <h3 class="devnahian-pool-panel-title" style="margin: 0 0 4px 0; font-size: 16.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span class="dashicons dashicons-clock" style="color: #0284c7;"></span>
                        <?php esc_html_e( 'Weekly Operating Hours Schedule', 'swimming-pool-manager' ); ?>
                    </h3>
                    <p style="margin: 0; font-size: 12.5px; color: #64748b;"><?php esc_html_e( 'Set automated facility opening and closing schedules for each day of the week.', 'swimming-pool-manager' ); ?></p>
                </div>
                <div class="devnahian-pool-form-stack">
                    <div class="devnahian-pool-schedule-stack" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 12px;">
                        <?php foreach ( $days_map as $day_key => $day_label ) : 
                            $day_data     = $weekly_schedule[ $day_key ] ?? array();
                            $saved_status = $day_data['status'] ?? 'open';
                            $saved_open   = $day_data['open'] ?? '08:00';
                            $saved_close  = $day_data['close'] ?? '22:00';
                        ?>
                            <div class="devnahian-pool-schedule-row" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;">
                                <strong style="width: 90px; color: #0f172a; font-size: 13.5px;"><?php echo esc_html( $day_label ); ?></strong>
                                
                                <select name="ifs_pms_schedule_<?php echo esc_attr( $day_key ); ?>_status" id="devnahianPoolSchedStatus_<?php echo esc_attr( $day_key ); ?>" onchange="devnahianPoolToggleDayHours('<?php echo esc_attr( $day_key ); ?>')" style="border-radius: 6px; border: 1.5px solid #cbd5e1; height: 36px; font-weight: 700; font-size: 12px;">
                                    <option value="open" <?php selected( $saved_status, 'open' ); ?>><?php esc_html_e( 'Open', 'swimming-pool-manager' ); ?></option>
                                    <option value="closed" <?php selected( $saved_status, 'closed' ); ?>><?php esc_html_e( 'Closed', 'swimming-pool-manager' ); ?></option>
                                </select>

                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <input type="time" name="ifs_pms_schedule_<?php echo esc_attr( $day_key ); ?>_open" id="devnahianPoolOpen_<?php echo esc_attr( $day_key ); ?>" value="<?php echo esc_attr( $saved_open ); ?>" class="ifs-pms-mono" style="border-radius: 6px; border: 1.5px solid #cbd5e1; height: 36px; width: 84px; padding: 0 6px; font-size: 12px; font-weight: 600; <?php echo ( 'closed' === $saved_status ) ? 'opacity: 0.4;' : ''; ?>" <?php disabled( $saved_status, 'closed' ); ?>>
                                    <span style="color: #94a3b8; font-size: 11px;">to</span>
                                    <input type="time" name="ifs_pms_schedule_<?php echo esc_attr( $day_key ); ?>_close" id="devnahianPoolClose_<?php echo esc_attr( $day_key ); ?>" value="<?php echo esc_attr( $saved_close ); ?>" class="ifs-pms-mono" style="border-radius: 6px; border: 1.5px solid #cbd5e1; height: 36px; width: 84px; padding: 0 6px; font-size: 12px; font-weight: 600; <?php echo ( 'closed' === $saved_status ) ? 'opacity: 0.4;' : ''; ?>" <?php disabled( $saved_status, 'closed' ); ?>>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 5. Gear Rentals & Amenities -->
            <div class="devnahian-pool-panel-card" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04);">
                <div class="devnahian-pool-panel-head" style="border-bottom: 1.5px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 20px;">
                    <h3 class="devnahian-pool-panel-title" style="margin: 0 0 4px 0; font-size: 16.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span class="dashicons dashicons-admin-settings" style="color: #0284c7;"></span>
                        <?php esc_html_e( 'Gear Rentals & Amenity Add-Ons', 'swimming-pool-manager' ); ?>
                    </h3>
                    <p style="margin: 0; font-size: 12.5px; color: #64748b;"><?php esc_html_e( 'Configure rentable items such as towels, goggles, or lockers that can be added to POS receipts.', 'swimming-pool-manager' ); ?></p>
                </div>
                <div class="devnahian-pool-form-stack">
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; padding: 18px 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div>
                            <div style="font-weight: 800; color: #0f172a; margin-bottom: 2px; font-size: 14px;"><?php esc_html_e( 'Enable Gear Rentals on POS Checkout', 'swimming-pool-manager' ); ?></div>
                            <div style="font-size: 12.5px; color: #64748b;"><?php esc_html_e( 'Toggle to show or completely hide rentable gear items during ticket issuance.', 'swimming-pool-manager' ); ?></div>
                        </div>
                        <label class="devnahian-pool-ios-switch">
                            <input type="checkbox" name="ifs_pms_enable_amenities" id="devnahianPoolEnableAmenitiesToggle" value="1" <?php checked( $enable_amenities, '1' ); ?> onchange="devnahianPoolToggleAmenitiesBox()">
                            <span class="devnahian-pool-ios-slider"></span>
                        </label>
                    </div>

                    <div id="devnahianPoolAmenitiesRepeaterBox" style="<?php echo ( '1' === $enable_amenities ) ? 'display: block;' : 'display: none;'; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <label style="font-weight: 700; color: #334155; font-size: 13.5px;"><?php esc_html_e( 'Rentable Inventory & Rental Rates', 'swimming-pool-manager' ); ?></label>
                            <button type="button" class="ifs-pms-btn ifs-pms-btn-secondary ifs-pms-btn-sm" style="background: #f0f9ff; color: #0284c7; border: 1.5px solid #bae6fd; font-weight: 700; padding: 6px 14px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" onclick="devnahianPoolAddAddonRow()">
                                <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add Amenity Item', 'swimming-pool-manager' ); ?>
                            </button>
                        </div>

                        <table class="devnahian-pool-repeater-table" id="devnahianPoolAmenityAddonTable" style="width: 100%; text-align: left; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid #e2e8f0; font-size: 12px; text-transform: uppercase; color: #64748b;">
                                    <th style="padding: 10px;"><?php esc_html_e( 'Amenity Item Name', 'swimming-pool-manager' ); ?></th>
                                    <th style="padding: 10px;"><?php printf( esc_html__( 'Rental Fee (%s)', 'swimming-pool-manager' ), esc_html( $currency ) ); ?></th>
                                    <th style="padding: 10px; text-align: center;"><?php esc_html_e( 'Action', 'swimming-pool-manager' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( ! empty( $amenity_addons ) && is_array( $amenity_addons ) ) : ?>
                                    <?php foreach ( $amenity_addons as $addon ) : ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 8px;"><input type="text" name="ifs_addon_name[]" value="<?php echo esc_attr( $addon['name'] ?? '' ); ?>" required placeholder="<?php esc_attr_e( 'Item name', 'swimming-pool-manager' ); ?>" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 8px 12px; font-weight: 600;"></td>
                                            <td style="padding: 8px;"><input type="number" step="0.01" name="ifs_addon_price[]" value="<?php echo esc_attr( $addon['price'] ?? '' ); ?>" required class="ifs-pms-mono" placeholder="0.00" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 8px 12px; font-weight: 600;"></td>
                                            <td style="padding: 8px; text-align: center;">
                                                <button type="button" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #fef2f2; color: #ef4444; border: 1.5px solid #fca5a5; padding: 6px 10px; border-radius: 6px; cursor: pointer;" onclick="devnahianPoolRemoveRow(this)">
                                                    <span class="dashicons dashicons-trash"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr class="devnahian-pool-empty-addon-row">
                                        <td colspan="3" style="text-align: center; padding: 30px; color: #64748b; font-style: italic;">
                                            <?php esc_html_e( 'No gear rental items currently configured.', 'swimming-pool-manager' ); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 6. Payment Methods & POS Security Defaults -->
            <div class="devnahian-pool-panel-card" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04);">
                <div class="devnahian-pool-panel-head" style="border-bottom: 1.5px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 20px;">
                    <h3 class="devnahian-pool-panel-title" style="margin: 0 0 4px 0; font-size: 16.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span class="dashicons dashicons-money-alt" style="color: #0284c7;"></span>
                        <?php esc_html_e( 'Payment Tenders & Security Protocols', 'swimming-pool-manager' ); ?>
                    </h3>
                    <p style="margin: 0; font-size: 12.5px; color: #64748b;"><?php esc_html_e( 'Configure accepted tender types, cash drawer shortcuts, and security validation switches.', 'swimming-pool-manager' ); ?></p>
                </div>
                <div class="devnahian-pool-form-stack">
                    <div class="devnahian-pool-field-group" style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom: 12px; font-weight: 700; color: #334155; font-size: 13.5px;"><?php esc_html_e( 'Active Payment Methods on POS', 'swimming-pool-manager' ); ?></label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 12px;">
                            <?php
                            $available_tenders = array(
                                'Cash'          => __( 'Cash Tender', 'swimming-pool-manager' ),
                                'Room Guest'    => __( 'Hotel Room Guest', 'swimming-pool-manager' ),
                                'Complementary' => __( 'Complementary Pass', 'swimming-pool-manager' ),
                                'bKash / Nagad' => __( 'Mobile Banking (MFS)', 'swimming-pool-manager' ),
                                'Card POS'      => __( 'Card Terminal (POS)', 'swimming-pool-manager' ),
                            );
                            foreach ( $available_tenders as $tender_key => $tender_label ) :
                                $checked = in_array( $tender_key, $enabled_tenders, true ) ? 'checked' : '';
                            ?>
                                <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1.5px solid #e2e8f0; padding: 12px 14px; border-radius: 10px;">
                                    <span style="font-weight: 700; color: #0f172a; font-size: 13px;"><?php echo esc_html( $tender_label ); ?></span>
                                    <label class="devnahian-pool-ios-switch">
                                        <input type="checkbox" name="ifs_pms_enabled_tenders[]" value="<?php echo esc_attr( $tender_key ); ?>" <?php echo esc_attr( $checked ); ?>>
                                        <span class="devnahian-pool-ios-slider"></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                        <div>
                            <label style="display:block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 13.5px;"><?php esc_html_e( 'Quick Cash Preset Buttons', 'swimming-pool-manager' ); ?></label>
                            <input type="text" name="ifs_pms_quick_cash_presets" id="devnahianPoolQuickCashPresets" value="<?php echo $quick_cash_str; ?>" placeholder="<?php esc_attr_e( 'e.g. 500, 1000, 2000', 'swimming-pool-manager' ); ?>" class="ifs-pms-mono" style="width: 100%; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-weight: 600;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 13.5px;"><?php esc_html_e( 'Thermal Printer Paper Size', 'swimming-pool-manager' ); ?></label>
                            <select name="ifs_pms_thermal_paper_width" id="devnahianPoolThermalPaperWidth" style="width: 100%; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 700; padding: 0 12px; color: #0f172a;">
                                <option value="80mm" <?php selected( $paper_width, '80mm' ); ?>><?php esc_html_e( '80mm Standard POS Slip', 'swimming-pool-manager' ); ?></option>
                                <option value="58mm" <?php selected( $paper_width, '58mm' ); ?>><?php esc_html_e( '58mm Mobile Bluetooth Slip', 'swimming-pool-manager' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1.5px dashed #cbd5e1; margin: 24px 0;">

                    <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 28px;">
                        <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; padding: 16px 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 800; color: #0f172a; margin-bottom: 3px; font-size: 14px;"><?php esc_html_e( 'Strict Pass Verification & Anti-Passback', 'swimming-pool-manager' ); ?></div>
                                <div style="font-size: 12.5px; color: #64748b;"><?php esc_html_e( 'Prevents double-entry by marking tickets used immediately upon turnstile scan.', 'swimming-pool-manager' ); ?></div>
                            </div>
                            <label class="devnahian-pool-ios-switch">
                                <input type="checkbox" name="ifs_pms_enable_strict_scan" value="1" <?php checked( $enable_strict_scan, '1' ); ?>>
                                <span class="devnahian-pool-ios-slider"></span>
                            </label>
                        </div>

                        <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; padding: 16px 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 800; color: #0f172a; margin-bottom: 3px; font-size: 14px;"><?php esc_html_e( 'Auto-Expire Day Passes at Midnight', 'swimming-pool-manager' ); ?></div>
                                <div style="font-size: 12.5px; color: #64748b;"><?php esc_html_e( 'Automatically expires single-entry day passes at midnight.', 'swimming-pool-manager' ); ?></div>
                            </div>
                            <label class="devnahian-pool-ios-switch">
                                <input type="checkbox" name="ifs_pms_auto_expire_passes" value="1" <?php checked( $auto_expire_passes, '1' ); ?>>
                                <span class="devnahian-pool-ios-slider"></span>
                            </label>
                        </div>

                        <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; padding: 16px 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 800; color: #0f172a; margin-bottom: 3px; font-size: 14px;"><?php esc_html_e( 'Turnstile & Scanner Sound Chime Alerts', 'swimming-pool-manager' ); ?></div>
                                <div style="font-size: 12.5px; color: #64748b;"><?php esc_html_e( 'Plays synthesized success and access-denied chime tones on scan.', 'swimming-pool-manager' ); ?></div>
                            </div>
                            <label class="devnahian-pool-ios-switch">
                                <input type="checkbox" name="ifs_pms_enable_audio_buzzer" value="1" <?php checked( $enable_audio_buzzer, '1' ); ?>>
                                <span class="devnahian-pool-ios-slider"></span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="ifs-pms-btn ifs-pms-btn-primary" style="width: 100%; height: 50px; border-radius: 10px; font-weight: 800; font-size: 15px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #0284c7; color: #fff; border: none; cursor: pointer; box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35); transition: transform 0.15s ease;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                        <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Save Operational Settings', 'swimming-pool-manager' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- TAB 2: Venue & Thermal Receipt Header -->
        <div id="devnahianPoolSettingsPaneGeneral" class="devnahian-pool-tab-pane" style="display: <?php echo ( 'general' === $active_tab ) ? 'block' : 'none'; ?>;">
            <div class="devnahian-pool-panel-card" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04);">
                <div class="devnahian-pool-panel-head" style="border-bottom: 1.5px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 20px;">
                    <h3 class="devnahian-pool-panel-title" style="margin: 0 0 4px 0; font-size: 16.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span class="dashicons dashicons-admin-home" style="color: #0284c7;"></span>
                        <?php esc_html_e( 'Venue Identity, Logo & Receipt Slip Header', 'swimming-pool-manager' ); ?>
                    </h3>
                    <p style="margin: 0; font-size: 12.5px; color: #64748b;"><?php esc_html_e( 'Customize your resort or property details printed on thermal POS slips.', 'swimming-pool-manager' ); ?></p>
                </div>

                <div class="devnahian-pool-form-stack">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display:block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 13.5px;"><?php esc_html_e( 'Venue / Property Name', 'swimming-pool-manager' ); ?> <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="ifs_pms_business_name" id="devnahianPoolBusinessName" value="<?php echo esc_attr( $b_name ); ?>" required placeholder="<?php esc_attr_e( 'e.g. Ozone Restaurant & Skypool', 'swimming-pool-manager' ); ?>" style="width: 100%; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-weight: 600;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 13.5px;"><?php esc_html_e( 'Currency Symbol / Code', 'swimming-pool-manager' ); ?> <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="ifs_pms_currency" id="devnahianPoolCurrency" value="<?php echo esc_attr( $currency ); ?>" required class="ifs-pms-mono" placeholder="<?php esc_attr_e( 'e.g. BDT or $', 'swimming-pool-manager' ); ?>" style="width: 100%; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-weight: 600;">
                            <!-- Currency Preset Quick Buttons -->
                            <div style="display: flex; gap: 6px; margin-top: 8px;">
                                <button type="button" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; cursor: pointer;" onclick="document.getElementById('devnahianPoolCurrency').value='BDT'">BDT</button>
                                <button type="button" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; cursor: pointer;" onclick="document.getElementById('devnahianPoolCurrency').value='USD'">USD ($)</button>
                                <button type="button" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; cursor: pointer;" onclick="document.getElementById('devnahianPoolCurrency').value='EUR'">EUR (€)</button>
                                <button type="button" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; cursor: pointer;" onclick="document.getElementById('devnahianPoolCurrency').value='GBP'">GBP (£)</button>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 13.5px;"><?php esc_html_e( 'Venue Logo URL', 'swimming-pool-manager' ); ?></label>
                        <div style="display: flex; gap: 8px;">
                            <input type="url" name="ifs_pms_logo_url" id="devnahianPoolLogoUrl" value="<?php echo esc_url( get_option( 'ifs_pms_logo_url', '' ) ); ?>" placeholder="https://example.com/logo.png" style="flex: 1; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-weight: 600;" oninput="devnahianPoolSyncLogoPreview(this.value)">
                            <button type="button" class="ifs-pms-btn ifs-pms-btn-secondary" style="height: 44px; border-radius: 8px; padding: 0 18px; background: #f0f9ff; color: #0284c7; border: 1.5px solid #bae6fd; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;" onclick="devnahianPoolOpenMediaUploader()">
                                <span class="dashicons dashicons-admin-media"></span> <?php esc_html_e( 'Media Library', 'swimming-pool-manager' ); ?>
                            </button>
                        </div>
                        
                        <!-- Real-time Logo Preview Box -->
                        <div id="devnahianPoolLogoPreviewContainer" style="margin-top: 12px; background: #f8fafc; padding: 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; display: <?php echo ! empty( $logo_url ) ? 'inline-block' : 'none'; ?>;">
                            <div style="font-size: 10.5px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;"><?php esc_html_e( 'Live Preview', 'swimming-pool-manager' ); ?></div>
                            <img src="<?php echo esc_url( get_option( 'ifs_pms_logo_url', '' ) ); ?>" alt="<?php esc_attr_e( 'Logo Preview', 'swimming-pool-manager' ); ?>" id="devnahianPoolLogoPreviewImg" style="max-height: 45px; width: auto; display: block;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display:block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 13.5px;"><?php esc_html_e( 'Front Desk Phone Number', 'swimming-pool-manager' ); ?></label>
                            <input type="text" name="ifs_pms_phone" id="devnahianPoolPhone" value="<?php echo esc_attr( $phone ); ?>" placeholder="<?php esc_attr_e( 'e.g. +880 1700-000000', 'swimming-pool-manager' ); ?>" style="width: 100%; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-weight: 600;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 13.5px;"><?php esc_html_e( 'Rooftop Guest Capacity Limit', 'swimming-pool-manager' ); ?></label>
                            <input type="number" name="ifs_pms_max_capacity" id="devnahianPoolCapacity" value="<?php echo esc_attr( (string) $capacity ); ?>" placeholder="<?php esc_attr_e( 'Maximum head count', 'swimming-pool-manager' ); ?>" style="width: 100%; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-weight: 600;">
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 13.5px;"><?php esc_html_e( 'Physical Address (Printed on Slip)', 'swimming-pool-manager' ); ?></label>
                        <input type="text" name="ifs_pms_address" id="devnahianPoolAddress" value="<?php echo esc_attr( $address ); ?>" placeholder="<?php esc_attr_e( 'Full address line', 'swimming-pool-manager' ); ?>" style="width: 100%; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-weight: 600;">
                    </div>

                    <div style="margin-bottom: 28px;">
                        <label style="display:block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 13.5px;"><?php esc_html_e( 'Thermal Receipt Footer Terms & Safety Policy', 'swimming-pool-manager' ); ?></label>
                        <textarea name="ifs_pms_receipt_note" id="devnahianPoolReceiptNote" rows="3" placeholder="<?php esc_attr_e( 'Add thermal slip policy statements...', 'swimming-pool-manager' ); ?>" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 12px; font-weight: 600;"><?php echo esc_textarea( get_option( 'ifs_pms_receipt_note', '' ) ); ?></textarea>
                    </div>

                    <button type="submit" class="ifs-pms-btn ifs-pms-btn-primary" style="width: 100%; height: 50px; border-radius: 10px; font-weight: 800; font-size: 15px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #0284c7; color: #fff; border: none; cursor: pointer; box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35); transition: transform 0.15s ease;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                        <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Save Venue Configuration', 'swimming-pool-manager' ); ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
/**
 * Settings Tab & Repeater Controllers
 */
function devnahianPoolSwitchSettingsTab(tabName, btn) {
    document.querySelectorAll('.devnahian-pool-subnav-btn').forEach(function(b) {
        b.classList.remove('active');
        b.style.background = 'transparent';
        b.style.color = '#fff';
    });
    document.querySelectorAll('.devnahian-pool-tab-pane').forEach(function(p) {
        p.style.display = 'none';
    });
    
    btn.classList.add('active');
    btn.style.background = '#fff';
    btn.style.color = '#0284c7';

    var paneId = (tabName === 'pos') ? 'devnahianPoolSettingsPanePos' : 'devnahianPoolSettingsPaneGeneral';
    var pane = document.getElementById(paneId);
    if (pane) {
        pane.style.display = 'block';
    }

    var input = document.getElementById('devnahianPoolActiveTabInput');
    if (input) {
        input.value = tabName;
    }

    if (window.history.replaceState) {
        var url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }
}

// 1. Admission Packages Table Dynamic Row Generator
function devnahianPoolAddPricingTierRow() {
    var tbody = document.querySelector('#devnahianPoolPricingTierTable tbody');
    var emptyRow = tbody.querySelector('.devnahian-pool-empty-row');
    if (emptyRow) {
        emptyRow.remove();
    }

    var tr = document.createElement('tr');
    tr.style.borderBottom = '1px solid #f1f5f9';
    tr.innerHTML = `
        <td style="padding: 8px;"><input type="text" name="ifs_pricing_tier_name[]" required placeholder="<?php esc_attr_e( 'Enter package title', 'swimming-pool-manager' ); ?>" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 8px 12px; font-weight: 600;"></td>
        <td style="padding: 8px;"><input type="text" name="ifs_pricing_tier_age[]" required placeholder="<?php esc_attr_e( 'Enter age/category group', 'swimming-pool-manager' ); ?>" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 8px 12px; font-weight: 600;"></td>
        <td style="padding: 8px;"><input type="number" step="0.01" name="ifs_pricing_tier_price[]" required class="ifs-pms-mono" placeholder="0.00" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 8px 12px; font-weight: 600;"></td>
        <td style="padding: 8px; text-align: center;">
            <button type="button" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #fef2f2; color: #ef4444; border: 1.5px solid #fca5a5; padding: 6px 10px; border-radius: 6px; cursor: pointer;" onclick="devnahianPoolRemoveRow(this)">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

// 2. Amenities Row Generator
function devnahianPoolAddAddonRow() {
    var tbody = document.querySelector('#devnahianPoolAmenityAddonTable tbody');
    var emptyRow = tbody.querySelector('.devnahian-pool-empty-addon-row');
    if (emptyRow) {
        emptyRow.remove();
    }

    var tr = document.createElement('tr');
    tr.style.borderBottom = '1px solid #f1f5f9';
    tr.innerHTML = `
        <td style="padding: 8px;"><input type="text" name="ifs_addon_name[]" required placeholder="<?php esc_attr_e( 'Item name', 'swimming-pool-manager' ); ?>" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 8px 12px; font-weight: 600;"></td>
        <td style="padding: 8px;"><input type="number" step="0.01" name="ifs_addon_price[]" required class="ifs-pms-mono" placeholder="0.00" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 8px 12px; font-weight: 600;"></td>
        <td style="padding: 8px; text-align: center;">
            <button type="button" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #fef2f2; color: #ef4444; border: 1.5px solid #fca5a5; padding: 6px 10px; border-radius: 6px; cursor: pointer;" onclick="devnahianPoolRemoveRow(this)">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

// Global Row Remover
function devnahianPoolRemoveRow(btn) {
    if (confirm('<?php esc_attr_e( 'Remove this row?', 'swimming-pool-manager' ); ?>')) {
        btn.closest('tr').remove();
    }
}

// 3. Operating Day Disabler
function devnahianPoolToggleDayHours(dayKey) {
    var select = document.getElementById('devnahianPoolSchedStatus_' + dayKey);
    var openIn = document.getElementById('devnahianPoolOpen_' + dayKey);
    var closeIn = document.getElementById('devnahianPoolClose_' + dayKey);
    
    var isClosed = (select.value === 'closed');
    openIn.disabled = isClosed;
    closeIn.disabled = isClosed;
    
    if (isClosed) {
        openIn.style.opacity = '0.4';
        closeIn.style.opacity = '0.4';
    } else {
        openIn.style.opacity = '1';
        closeIn.style.opacity = '1';
    }
}

// 4. Toggle Amenities Box visibility based on switch
function devnahianPoolToggleAmenitiesBox() {
    var toggle = document.getElementById('devnahianPoolEnableAmenitiesToggle');
    var box = document.getElementById('devnahianPoolAmenitiesRepeaterBox');
    
    if (toggle && box) {
        if (toggle.checked) {
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
        }
    }
}

// 5. Real-time Logo Preview Synchronizer
function devnahianPoolSyncLogoPreview(url) {
    var container = document.getElementById('devnahianPoolLogoPreviewContainer');
    var img = document.getElementById('devnahianPoolLogoPreviewImg');
    if (container && img) {
        if (url && url.trim() !== '') {
            img.src = url;
            container.style.display = 'inline-block';
        } else {
            container.style.display = 'none';
        }
    }
}

// 6. WordPress Native Media Uploader (Logo)
function devnahianPoolOpenMediaUploader() {
    if (typeof wp === 'undefined' || !wp.media) {
        alert('<?php esc_attr_e( 'WordPress media uploader is not available.', 'swimming-pool-manager' ); ?>');
        return;
    }
    
    var customUploader = wp.media({
        title: '<?php esc_attr_e( 'Select Venue Logo', 'swimming-pool-manager' ); ?>',
        button: { text: '<?php esc_attr_e( 'Use Image', 'swimming-pool-manager' ); ?>' },
        multiple: false
    });
    
    customUploader.on('select', function() {
        var attachment = customUploader.state().get('selection').first().toJSON();
        document.getElementById('devnahianPoolLogoUrl').value = attachment.url;
        devnahianPoolSyncLogoPreview(attachment.url);
    });
    
    customUploader.open();
}
</script>