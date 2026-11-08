<?php
/**
 * View: Locker Control & Allocation (Enterprise UI/UX Edition)
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

global $wpdb;
$t_lockers = $wpdb->prefix . 'ifs_pms_lockers';
$base_url  = admin_url( 'admin.php?page=ifs-pms-lockers' );

// ============================================================================
// SELF-CONTAINED ACTION DISPATCHER (GENERATE & UPDATE LOCKERS)
// ============================================================================
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['ifs_pms_locker_action'] ) ) {
    if ( ! isset( $_POST['ifs_pms_locker_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifs_pms_locker_nonce'] ) ), 'ifs_pms_secure_locker' ) ) {
        wp_die( esc_html__( 'Security check failed. Please refresh and try again.', 'swimming-pool-manager' ) );
    }

    $action = sanitize_key( $_POST['ifs_pms_locker_action'] );

    // 1. Bulk Generate Lockers
    if ( 'generate_lockers' === $action && current_user_can('manage_options') ) {
        $count  = isset( $_POST['locker_count'] ) ? absint( $_POST['locker_count'] ) : 0;
        $prefix = isset( $_POST['locker_prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['locker_prefix'] ) ) : '';
        
        if ( $count > 0 ) {
            $generated = 0;
            for ( $i = 1; $i <= $count; $i++ ) {
                // Format as 001, 002, etc.
                $num_str   = str_pad( (string) $i, 3, '0', STR_PAD_LEFT );
                $locker_no = $prefix . $num_str;

                // Check if exists to prevent duplicate key errors
                $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_lockers} WHERE locker_number = %s", $locker_no ) );
                
                if ( ! $exists ) {
                    $wpdb->insert(
                        $t_lockers,
                        array(
                            'locker_number' => $locker_no,
                            'status'        => 'Available',
                        ),
                        array( '%s', '%s' )
                    );
                    $generated++;
                }
            }
            wp_safe_redirect( add_query_arg( array( 'generated' => $generated ), $base_url ) );
            exit;
        }
    }

    // 2. Update Single Locker Status
    if ( 'update_locker' === $action ) {
        $locker_id = isset( $_POST['locker_id'] ) ? absint( $_POST['locker_id'] ) : 0;
        $status    = isset( $_POST['locker_status'] ) ? sanitize_text_field( wp_unslash( $_POST['locker_status'] ) ) : 'Available';

        $allowed_statuses = array( 'Available', 'Occupied', 'Maintenance' );
        if ( ! in_array( $status, $allowed_statuses, true ) ) {
            $status = 'Available';
        }

        if ( $locker_id > 0 ) {
            $wpdb->update(
                $t_lockers,
                array( 'status' => $status ),
                array( 'id' => $locker_id ),
                array( '%s' ),
                array( '%d' )
            );
            wp_safe_redirect( add_query_arg( array( 'updated' => '1' ), $base_url ) );
            exit;
        }
    }
}

// Fetch all lockers
$lockers = $wpdb->get_results( "SELECT * FROM {$t_lockers} ORDER BY CAST(REGEXP_REPLACE(locker_number, '[^0-9]', '') AS UNSIGNED) ASC" );

// Calculate Stats
$total_lockers = count($lockers);
$available     = 0;
$occupied      = 0;
$maintenance   = 0;

foreach ( $lockers as $l ) {
    if ( 'Available' === $l->status ) $available++;
    if ( 'Occupied' === $l->status ) $occupied++;
    if ( 'Maintenance' === $l->status ) $maintenance++;
}
?>

<div class="devnahian-pool-panel" style="background: var(--ifs-surface, #ffffff); border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04);">
    
    <?php if ( isset( $_GET['generated'] ) ) : ?>
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
            <?php printf( esc_html__( 'Successfully generated %d new lockers.', 'swimming-pool-manager' ), absint( $_GET['generated'] ) ); ?>
        </div>
    <?php endif; ?>

    <?php if ( isset( $_GET['updated'] ) ) : ?>
        <div style="background: #f0f9ff; border: 1.5px solid #bae6fd; color: #0369a1; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
            <?php esc_html_e( 'Locker status updated successfully.', 'swimming-pool-manager' ); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 16px;">
        <div>
            <h2 style="margin: 0 0 6px 0; font-size: 18px; font-weight: 800; color: var(--ifs-text-primary, #0f172a); display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-lock" style="color: #0284c7;"></span>
                <?php esc_html_e( 'Locker Control Matrix & Allocation', 'swimming-pool-manager' ); ?>
            </h2>
            <?php if ( $total_lockers > 0 ) : ?>
                <div style="font-size: 13px; font-weight: 700; color: #64748b; display: flex; gap: 16px;">
                    <span style="color: #059669; display: inline-flex; align-items: center; gap: 4px;"><span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span> <?php echo esc_html($available); ?> Available</span>
                    <span style="color: #dc2626; display: inline-flex; align-items: center; gap: 4px;"><span style="width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span> <?php echo esc_html($occupied); ?> Occupied</span>
                    <span style="color: #d97706; display: inline-flex; align-items: center; gap: 4px;"><span style="width: 8px; height: 8px; background: #f59e0b; border-radius: 50%;"></span> <?php echo esc_html($maintenance); ?> Maintenance</span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( current_user_can('manage_options') ) : ?>
            <button class="ifs-pms-btn ifs-pms-btn-primary" onclick="devnahianPoolOpenLockerGenModal()" style="background: #0284c7; color: #fff; font-weight: 800; padding: 8px 18px; border-radius: 10px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);">
                <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Bulk Generate Lockers', 'swimming-pool-manager' ); ?>
            </button>
        <?php endif; ?>
    </div>

    <?php if ( empty( $lockers ) ) : ?>
        <div style="text-align: center; padding: 60px 20px; border: 1.5px dashed #cbd5e1; border-radius: 16px; background: #f8fafc;">
            <span class="dashicons dashicons-lock" style="font-size: 48px; width: 48px; height: 48px; color: #94a3b8; margin-bottom: 12px;"></span>
            <h3 style="margin: 0 0 8px 0; color: #0f172a; font-weight: 800;"><?php esc_html_e( 'No lockers configured yet', 'swimming-pool-manager' ); ?></h3>
            <p style="margin: 0; color: #64748b; font-size: 13.5px;"><?php esc_html_e( 'Use the "Bulk Generate" button above to create your facility\'s locker grid.', 'swimming-pool-manager' ); ?></p>
        </div>
    <?php else : ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 16px;">
            <?php foreach ( $lockers as $locker ) : 
                if ( 'Available' === $locker->status ) {
                    $bg_color   = '#ecfdf5';
                    $border     = '#10b981';
                    $text_color = '#059669';
                    $icon       = 'dashicons-unlock';
                } elseif ( 'Occupied' === $locker->status ) {
                    $bg_color   = '#fef2f2';
                    $border     = '#ef4444';
                    $text_color = '#dc2626';
                    $icon       = 'dashicons-lock';
                } else {
                    $bg_color   = '#fffbeb';
                    $border     = '#f59e0b';
                    $text_color = '#d97706';
                    $icon       = 'dashicons-admin-tools';
                }
            ?>
                <div onclick="devnahianPoolOpenLockerEditModal(<?php echo esc_attr( $locker->id ); ?>, '<?php echo esc_js( $locker->locker_number ); ?>', '<?php echo esc_js( $locker->status ); ?>')" 
                     style="background: <?php echo esc_attr($bg_color); ?>; border: 2px solid <?php echo esc_attr($border); ?>; border-radius: 14px; padding: 20px 12px; text-align: center; cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease;"
                     onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 20px -5px rgba(0,0,0,0.12)';"
                     onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                    
                    <div style="color: <?php echo esc_attr($border); ?>; margin-bottom: 8px;">
                        <span class="dashicons <?php echo esc_attr($icon); ?>" style="font-size: 26px; width: 26px; height: 26px;"></span>
                    </div>
                    <h3 class="ifs-pms-mono" style="margin: 0 0 4px 0; font-size: 22px; font-weight: 800; color: #0f172a;">
                        <?php echo esc_html( $locker->locker_number ); ?>
                    </h3>
                    <span style="font-size: 10px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; color: <?php echo esc_attr($text_color); ?>;">
                        <?php echo esc_html( $locker->status ); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL: Bulk Generate Lockers -->
<div id="devnahianPoolLockerGenModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 420px; padding: 28px; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <h3 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-plus-alt2" style="color: #0284c7;"></span> <?php esc_html_e( 'Bulk Generate Lockers', 'swimming-pool-manager' ); ?>
        </h3>
        
        <form method="POST" action="<?php echo esc_url( $base_url ); ?>">
            <?php wp_nonce_field( 'ifs_pms_secure_locker', 'ifs_pms_locker_nonce' ); ?>
            <input type="hidden" name="ifs_pms_locker_action" value="generate_lockers">
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Number of Lockers to Create', 'swimming-pool-manager' ); ?> *</label>
                <input type="number" name="locker_count" required min="1" max="500" value="50" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 700;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Prefix (Optional)', 'swimming-pool-manager' ); ?></label>
                <input type="text" name="locker_prefix" placeholder="<?php esc_attr_e( 'e.g. L-', 'swimming-pool-manager' ); ?>" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 700;">
                <p style="margin: 6px 0 0; font-size: 11.5px; color: #64748b;"><?php esc_html_e( 'Example: 50 lockers with prefix "L-" creates L-001 to L-050.', 'swimming-pool-manager' ); ?></p>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="ifs-pms-btn ifs-pms-btn-primary" style="flex: 2; height: 46px; font-weight: 800; border-radius: 10px; background: #0284c7; color: #fff; border: none; cursor: pointer;">
                    <?php esc_html_e( 'Generate Grid', 'swimming-pool-manager' ); ?>
                </button>
                <button type="button" class="ifs-pms-btn" style="flex: 1; height: 46px; background: #f1f5f9; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; border-radius: 10px; cursor: pointer;" onclick="document.getElementById('devnahianPoolLockerGenModal').style.display='none';">
                    <?php esc_html_e( 'Cancel', 'swimming-pool-manager' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit Locker Status -->
<div id="devnahianPoolLockerEditModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 380px; padding: 28px; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-admin-tools" style="color: #0284c7;"></span> 
            <?php esc_html_e( 'Update Locker', 'swimming-pool-manager' ); ?> <span id="devnahianPoolLockerEditTitleNo" class="ifs-pms-mono" style="color: #0284c7;"></span>
        </h3>
        
        <form method="POST" action="<?php echo esc_url( $base_url ); ?>">
            <?php wp_nonce_field( 'ifs_pms_secure_locker', 'ifs_pms_locker_nonce' ); ?>
            <input type="hidden" name="ifs_pms_locker_action" value="update_locker">
            <input type="hidden" name="locker_id" id="devnahianPoolLockerEditId" value="">
            
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 10px; font-size: 13.5px;"><?php esc_html_e( 'Select Status', 'swimming-pool-manager' ); ?></label>
                
                <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px; margin-bottom: 8px; cursor: pointer;">
                    <input type="radio" name="locker_status" value="Available" id="devnahianPoolRadioAvailable">
                    <strong style="color: #059669; font-size: 13.5px;"><?php esc_html_e( 'Available (Free)', 'swimming-pool-manager' ); ?></strong>
                </label>

                <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px; margin-bottom: 8px; cursor: pointer;">
                    <input type="radio" name="locker_status" value="Occupied" id="devnahianPoolRadioOccupied">
                    <strong style="color: #dc2626; font-size: 13.5px;"><?php esc_html_e( 'Occupied (In Use)', 'swimming-pool-manager' ); ?></strong>
                </label>

                <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px; cursor: pointer;">
                    <input type="radio" name="locker_status" value="Maintenance" id="devnahianPoolRadioMaintenance">
                    <strong style="color: #d97706; font-size: 13.5px;"><?php esc_html_e( 'Maintenance / Broken', 'swimming-pool-manager' ); ?></strong>
                </label>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="ifs-pms-btn ifs-pms-btn-primary" style="flex: 2; height: 46px; font-weight: 800; border-radius: 10px; background: #0284c7; color: #fff; border: none; cursor: pointer;">
                    <?php esc_html_e( 'Save Status', 'swimming-pool-manager' ); ?>
                </button>
                <button type="button" class="ifs-pms-btn" style="flex: 1; height: 46px; background: #f1f5f9; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; border-radius: 10px; cursor: pointer;" onclick="document.getElementById('devnahianPoolLockerEditModal').style.display='none';">
                    <?php esc_html_e( 'Cancel', 'swimming-pool-manager' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function devnahianPoolOpenLockerGenModal() {
    document.getElementById('devnahianPoolLockerGenModal').style.display = 'flex';
}

function devnahianPoolOpenLockerEditModal(id, number, currentStatus) {
    document.getElementById('devnahianPoolLockerEditTitleNo').innerText = '#' + number;
    document.getElementById('devnahianPoolLockerEditId').value = id;
    
    // Set active radio
    if (currentStatus === 'Available') {
        document.getElementById('devnahianPoolRadioAvailable').checked = true;
    } else if (currentStatus === 'Occupied') {
        document.getElementById('devnahianPoolRadioOccupied').checked = true;
    } else {
        document.getElementById('devnahianPoolRadioMaintenance').checked = true;
    }
    
    document.getElementById('devnahianPoolLockerEditModal').style.display = 'flex';
}

// Close modals on clicking background overlay
document.addEventListener('click', function(e) {
    var genModal = document.getElementById('devnahianPoolLockerGenModal');
    var editModal = document.getElementById('devnahianPoolLockerEditModal');
    
    if (e.target === genModal) {
        genModal.style.display = 'none';
    }
    if (e.target === editModal) {
        editModal.style.display = 'none';
    }
});
</script>