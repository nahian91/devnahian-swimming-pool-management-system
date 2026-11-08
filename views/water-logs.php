<?php
/**
 * View: Water Quality Logs & Chemistry Monitoring (Enterprise UI/UX Edition)
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

global $wpdb;
$t_water  = $wpdb->prefix . 'ifs_pms_water_logs';
$base_url = admin_url( 'admin.php?page=ifs-pms-water-logs' );
$operator = wp_get_current_user()->display_name;

// ============================================================================
// SELF-CONTAINED ACTION DISPATCHER (CREATE WATER LOG ENTRY)
// ============================================================================
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['devnahian_pool_water_action'] ) ) {
    if ( ! isset( $_POST['devnahian_pool_water_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['devnahian_pool_water_nonce'] ) ), 'devnahian_pool_secure_water' ) ) {
        wp_die( esc_html__( 'Security check failed. Please refresh and try again.', 'swimming-pool-manager' ) );
    }

    $action = sanitize_key( $_POST['devnahian_pool_water_action'] );

    if ( 'create_water_log' === $action ) {
        $ph       = isset( $_POST['ph_level'] ) ? floatval( wp_unslash( $_POST['ph_level'] ) ) : 0.0;
        $chlorine = isset( $_POST['chlorine_level'] ) ? floatval( wp_unslash( $_POST['chlorine_level'] ) ) : 0.0;
        $temp     = isset( $_POST['temperature'] ) ? floatval( wp_unslash( $_POST['temperature'] ) ) : 0.0;
        $notes    = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';

        if ( $ph > 0 && $chlorine >= 0 && $temp > 0 ) {
            $wpdb->insert(
                $t_water,
                array(
                    'ph_level'       => $ph,
                    'chlorine_level' => $chlorine,
                    'temperature'    => $temp,
                    'notes'          => $notes,
                    'logged_by'      => $operator,
                    'logged_at'      => current_time( 'mysql' ),
                ),
                array( '%f', '%f', '%f', '%s', '%s', '%s' )
            );
            wp_safe_redirect( add_query_arg( array( 'msg' => 'water_logged' ), $base_url ) );
            exit;
        }
    }
}

$logs     = $wpdb->get_results( "SELECT * FROM {$t_water} ORDER BY logged_at DESC LIMIT 50" );
$msg_code = isset( $_GET['msg'] ) ? sanitize_key( $_GET['msg'] ) : '';
?>

<div class="devnahian-pool-panel" style="background: var(--ifs-surface, #ffffff); border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04);">
    
    <?php if ( 'water_logged' === $msg_code ) : ?>
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
            <?php esc_html_e( 'Water quality metrics successfully recorded to the audit log.', 'swimming-pool-manager' ); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 800; color: var(--ifs-text-primary, #0f172a); display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-dashboard" style="color: #0284c7;"></span> 
                <?php esc_html_e( 'Water Quality & Chemical Logs', 'swimming-pool-manager' ); ?>
            </h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;"><?php esc_html_e( 'Monitor hourly pool pH balance, free chlorine levels, and water temperatures.', 'swimming-pool-manager' ); ?></p>
        </div>
        <button class="ifs-pms-btn ifs-pms-btn-primary" onclick="devnahianPoolOpenWaterModal()" style="background: #0284c7; color: #fff; font-weight: 800; padding: 8px 18px; border-radius: 10px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);">
            <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( '+ Add Entry', 'swimming-pool-manager' ); ?>
        </button>
    </div>

    <table class="wp-list-table widefat fixed striped table-view-list" style="border-radius: 10px; overflow: hidden; border: 1.5px solid #cbd5e1;">
        <thead>
            <tr style="background: #f8fafc; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;">
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Date & Time', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'pH Level (7.2 - 7.8)', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Chlorine (1.0 - 3.0 ppm)', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Temperature', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Notes / Observations', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px; text-align: right;"><?php esc_html_e( 'Operator', 'swimming-pool-manager' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $logs ) ) : ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 48px; color: #64748b;">
                        <span class="dashicons dashicons-info" style="font-size: 36px; width: 36px; height: 36px; color: #0284c7; margin-bottom: 8px;"></span><br>
                        <div style="font-weight: 700; font-size: 14px; color: #0f172a;"><?php esc_html_e( 'No water logs recorded yet.', 'swimming-pool-manager' ); ?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;"><?php esc_html_e( 'Click "+ Add Entry" above to log the first pool water analysis.', 'swimming-pool-manager' ); ?></div>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $logs as $log ) : 
                    // Safe range checks
                    $is_ph_safe = ( (float) $log->ph_level >= 7.2 && (float) $log->ph_level <= 7.8 );
                    $is_cl_safe = ( (float) $log->chlorine_level >= 1.0 && (float) $log->chlorine_level <= 3.0 );
                ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td class="ifs-pms-mono" style="padding: 12px 14px; font-weight: 700; color: #0f172a;">
                            <?php echo esc_html( gmdate( 'M j, Y — g:i A', strtotime( $log->logged_at ) ) ); ?>
                        </td>
                        <td style="padding: 12px 14px;">
                            <span class="ifs-pms-mono" style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-weight: 800; font-size: 12.5px; background: <?php echo $is_ph_safe ? '#ecfdf5; color: #059669; border: 1px solid #a7f3d0;' : '#fef2f2; color: #dc2626; border: 1px solid #fca5a5;'; ?>">
                                <?php echo esc_html( $log->ph_level ); ?>
                            </span>
                        </td>
                        <td style="padding: 12px 14px;">
                            <span class="ifs-pms-mono" style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-weight: 800; font-size: 12.5px; background: <?php echo $is_cl_safe ? '#ecfdf5; color: #059669; border: 1px solid #a7f3d0;' : '#fffbeb; color: #d97706; border: 1px solid #fde68a;'; ?>">
                                <?php echo esc_html( $log->chlorine_level ); ?> ppm
                            </span>
                        </td>
                        <td class="ifs-pms-mono" style="padding: 12px 14px; font-weight: 700; color: #334155;">
                            <?php echo esc_html( $log->temperature ); ?>°C
                        </td>
                        <td style="padding: 12px 14px; color: #475569; font-size: 13px;">
                            <?php echo esc_html( ! empty( $log->notes ) ? $log->notes : '-' ); ?>
                        </td>
                        <td style="padding: 12px 14px; text-align: right; color: #64748b; font-weight: 600;">
                            <?php echo esc_html( $log->logged_by ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL: Add Water Quality Entry -->
<div id="devnahianPoolWaterModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 440px; padding: 24px; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <h3 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-dashboard" style="color: #0284c7;"></span> <?php esc_html_e( 'Record Water Quality Test', 'swimming-pool-manager' ); ?>
        </h3>
        
        <form method="POST" action="<?php echo esc_url( $base_url ); ?>">
            <?php wp_nonce_field( 'devnahian_pool_secure_water', 'devnahian_pool_water_nonce' ); ?>
            <input type="hidden" name="devnahian_pool_water_action" value="create_water_log">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13px;"><?php esc_html_e( 'pH Level (Ideal: 7.4)', 'swimming-pool-manager' ); ?> *</label>
                    <input type="number" step="0.1" name="ph_level" required value="7.4" min="0" max="14" class="ifs-pms-mono" style="width: 100%; height: 42px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-weight: 700;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13px;"><?php esc_html_e( 'Chlorine (ppm)', 'swimming-pool-manager' ); ?> *</label>
                    <input type="number" step="0.1" name="chlorine_level" required value="2.0" min="0" max="10" class="ifs-pms-mono" style="width: 100%; height: 42px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-weight: 700;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13px;"><?php esc_html_e( 'Temperature (°C)', 'swimming-pool-manager' ); ?> *</label>
                <input type="number" step="0.1" name="temperature" required value="28.5" min="10" max="50" class="ifs-pms-mono" style="width: 100%; height: 42px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-weight: 700;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13px;"><?php esc_html_e( 'Operator Notes / Chemical Dosed', 'swimming-pool-manager' ); ?></label>
                <textarea name="notes" rows="2" placeholder="<?php esc_attr_e( 'e.g. Added 500g Sodium Bicarbonate', 'swimming-pool-manager' ); ?>" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 10px; font-weight: 600; font-family: inherit;"></textarea>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="ifs-pms-btn ifs-pms-btn-primary" style="flex: 2; height: 44px; font-weight: 800; border-radius: 8px; background: #0284c7; color: #fff; border: none; cursor: pointer;">
                    <?php esc_html_e( 'Save Water Log', 'swimming-pool-manager' ); ?>
                </button>
                <button type="button" class="ifs-pms-btn" style="flex: 1; height: 44px; background: #f1f5f9; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; border-radius: 8px; cursor: pointer;" onclick="document.getElementById('devnahianPoolWaterModal').style.display='none';">
                    <?php esc_html_e( 'Cancel', 'swimming-pool-manager' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function devnahianPoolOpenWaterModal() {
    document.getElementById('devnahianPoolWaterModal').style.display = 'flex';
}

// Close modal on clicking background overlay
document.addEventListener('click', function(e) {
    var modal = document.getElementById('devnahianPoolWaterModal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});
</script>