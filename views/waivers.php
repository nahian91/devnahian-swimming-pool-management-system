<?php
/**
 * View: Digital Safety Waivers Management (Enterprise UI/UX Edition)
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

global $wpdb;
$t_waivers = $wpdb->prefix . 'ifs_pms_waivers';
$t_cust    = $wpdb->prefix . 'ifs_pms_customers';
$base_url  = admin_url( 'admin.php?page=ifs-pms-waivers' );

// ============================================================================
// SELF-CONTAINED ACTION DISPATCHER (CREATE WAIVER)
// ============================================================================
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['devnahian_pool_waiver_action'] ) ) {
    if ( ! isset( $_POST['devnahian_pool_waiver_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['devnahian_pool_waiver_nonce'] ) ), 'devnahian_pool_secure_waiver' ) ) {
        wp_die( esc_html__( 'Security check failed. Please refresh and try again.', 'swimming-pool-manager' ) );
    }

    $action = sanitize_key( $_POST['devnahian_pool_waiver_action'] );

    if ( 'create_waiver' === $action ) {
        $customer_id = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
        $duration_yrs = isset( $_POST['duration_years'] ) ? max( 1, absint( $_POST['duration_years'] ) ) : 1;

        if ( $customer_id > 0 ) {
            $signed_time  = current_time( 'mysql' );
            $expiry_time  = gmdate( 'Y-m-d H:i:s', strtotime( "+{$duration_yrs} years", strtotime( $signed_time ) ) );

            // Check if customer already has a valid waiver
            $existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_waivers} WHERE customer_id = %d AND status = 'Valid' AND expires_at > %s", $customer_id, $signed_time ) );

            if ( ! $existing ) {
                $wpdb->insert(
                    $t_waivers,
                    array(
                        'customer_id' => $customer_id,
                        'status'      => 'Valid',
                        'signed_at'   => $signed_time,
                        'expires_at'  => $expiry_time,
                    ),
                    array( '%d', '%s', '%s', '%s' )
                );
                wp_safe_redirect( add_query_arg( array( 'msg' => 'waiver_signed' ), $base_url ) );
                exit;
            } else {
                wp_safe_redirect( add_query_arg( array( 'msg' => 'waiver_exists' ), $base_url ) );
                exit;
            }
        }
    }
}

// Fetch waivers joined with customer data
$waivers = $wpdb->get_results( "
    SELECT w.*, c.name AS customer_name, c.phone AS customer_phone 
    FROM {$t_waivers} w
    LEFT JOIN {$t_cust} c ON w.customer_id = c.id
    ORDER BY w.signed_at DESC LIMIT 50
" );

// Fetch customers for dropdown selection
$customers = $wpdb->get_results( "SELECT id, name, phone FROM {$t_cust} ORDER BY name ASC" );

$msg_code = isset( $_GET['msg'] ) ? sanitize_key( $_GET['msg'] ) : '';
?>

<div class="devnahian-pool-panel" style="background: var(--ifs-surface, #ffffff); border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04);">
    
    <?php if ( 'waiver_signed' === $msg_code ) : ?>
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
            <?php esc_html_e( 'Digital safety waiver successfully executed and bound.', 'swimming-pool-manager' ); ?>
        </div>
    <?php elseif ( 'waiver_exists' === $msg_code ) : ?>
        <div style="background: #fffbeb; border: 1.5px solid #fde68a; color: #b45309; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
            <?php esc_html_e( 'Patron already has an active, valid safety waiver on file.', 'swimming-pool-manager' ); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 800; color: var(--ifs-text-primary, #0f172a); display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-clipboard" style="color: #0284c7;"></span> 
                <?php esc_html_e( 'Digital Safety Waivers Ledger', 'swimming-pool-manager' ); ?>
            </h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;"><?php esc_html_e( 'Track liability releases, patron health disclaimers, and legal agreement expiration dates.', 'swimming-pool-manager' ); ?></p>
        </div>
        <button class="ifs-pms-btn ifs-pms-btn-primary" onclick="devnahianPoolOpenWaiverModal()" style="background: #0284c7; color: #fff; font-weight: 800; padding: 8px 18px; border-radius: 10px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);">
            <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'New Waiver Sign', 'swimming-pool-manager' ); ?>
        </button>
    </div>

    <table class="wp-list-table widefat fixed striped table-view-list" style="border-radius: 10px; overflow: hidden; border: 1.5px solid #cbd5e1;">
        <thead>
            <tr style="background: #f8fafc; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;">
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Patron Identification', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Signed On', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Valid Until', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Legal Status', 'swimming-pool-manager' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $waivers ) ) : ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 48px; color: #64748b;">
                        <span class="dashicons dashicons-clipboard" style="font-size: 36px; width: 36px; height: 36px; opacity: 0.4; margin-bottom: 8px;"></span><br>
                        <div style="font-weight: 700; font-size: 14px; color: #0f172a;"><?php esc_html_e( 'No digital waivers signed yet.', 'swimming-pool-manager' ); ?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;"><?php esc_html_e( 'Click "New Waiver Sign" above to register a new legal agreement.', 'swimming-pool-manager' ); ?></div>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $waivers as $waiver ) : 
                    // Calculate if waiver is still legally active
                    $is_expired = ( strtotime( $waiver->expires_at ) < current_time( 'timestamp' ) );
                    $is_valid   = ( 'Valid' === $waiver->status && ! $is_expired );
                    
                    $badge_style = $is_valid 
                        ? 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;' 
                        : 'background: #fef2f2; color: #ef4444; border: 1px solid #fca5a5;';
                        
                    $status_text = $is_valid ? __( 'Active & Bound', 'swimming-pool-manager' ) : __( 'Expired / Invalid', 'swimming-pool-manager' );
                ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px 14px;">
                            <strong style="color: #0f172a; font-size: 14px; display: block; margin-bottom: 2px;">
                                <?php echo esc_html( ! empty( $waiver->customer_name ) ? $waiver->customer_name : __( 'Unknown Patron (ID: ', 'swimming-pool-manager' ) . $waiver->customer_id . ')' ); ?>
                            </strong>
                            <span class="ifs-pms-mono" style="font-size: 11.5px; color: #64748b;">
                                <?php echo esc_html( $waiver->customer_phone ?: '-' ); ?>
                            </span>
                        </td>
                        <td style="padding: 12px 14px; color: #334155; font-size: 13.5px;">
                            <?php echo esc_html( gmdate( 'F j, Y — g:i A', strtotime( $waiver->signed_at ) ) ); ?>
                        </td>
                        <td class="ifs-pms-mono" style="padding: 12px 14px; font-weight: 700; color: <?php echo $is_expired ? '#ef4444' : '#0284c7'; ?>;">
                            <?php echo esc_html( gmdate( 'Y-m-d', strtotime( $waiver->expires_at ) ) ); ?>
                        </td>
                        <td style="padding: 12px 14px;">
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; <?php echo esc_attr( $badge_style ); ?>">
                                <?php echo esc_html( $status_text ); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL: Sign New Waiver -->
<div id="devnahianPoolWaiverModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 440px; padding: 28px; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <h3 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-clipboard" style="color: #0284c7;"></span> <?php esc_html_e( 'Execute Safety Liability Waiver', 'swimming-pool-manager' ); ?>
        </h3>
        
        <form method="POST" action="<?php echo esc_url( $base_url ); ?>">
            <?php wp_nonce_field( 'devnahian_pool_secure_waiver', 'devnahian_pool_waiver_nonce' ); ?>
            <input type="hidden" name="devnahian_pool_waiver_action" value="create_waiver">
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Select Patron Signing', 'swimming-pool-manager' ); ?> *</label>
                <select name="customer_id" required style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 600; color: #0f172a;">
                    <option value=""><?php esc_html_e( '-- Select Registered Patron --', 'swimming-pool-manager' ); ?></option>
                    <?php foreach ( $customers as $c ) : ?>
                        <option value="<?php echo esc_attr( $c->id ); ?>"><?php echo esc_html( $c->name . ' (' . $c->phone . ')' ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Agreement Validity Duration', 'swimming-pool-manager' ); ?> *</label>
                <select name="duration_years" required style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 600; color: #0f172a;">
                    <option value="1"><?php esc_html_e( '1 Year (Standard Resort Pass)', 'swimming-pool-manager' ); ?></option>
                    <option value="2"><?php esc_html_e( '2 Years (Extended Member)', 'swimming-pool-manager' ); ?></option>
                    <option value="5"><?php esc_html_e( '5 Years (Lifetime Club)', 'swimming-pool-manager' ); ?></option>
                </select>
            </div>

            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; padding: 14px; border-radius: 10px; font-size: 12px; color: #475569; margin-bottom: 24px; line-height: 1.5;">
                <strong><?php esc_html_e( 'Legal Acknowledgment:', 'swimming-pool-manager' ); ?></strong> 
                <?php esc_html_e( 'By executing this waiver, the patron agrees to abide by pool safety protocols, releases management from liability, and certifies physical fitness for aquatic activities.', 'swimming-pool-manager' ); ?>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="ifs-pms-btn ifs-pms-btn-primary" style="flex: 2; height: 46px; font-weight: 800; border-radius: 10px; background: #0284c7; color: #fff; border: none; cursor: pointer;">
                    <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Confirm & Bind Waiver', 'swimming-pool-manager' ); ?>
                </button>
                <button type="button" class="ifs-pms-btn" style="flex: 1; height: 46px; background: #f1f5f9; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; border-radius: 10px; cursor: pointer;" onclick="document.getElementById('devnahianPoolWaiverModal').style.display='none';">
                    <?php esc_html_e( 'Cancel', 'swimming-pool-manager' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function devnahianPoolOpenWaiverModal() {
    document.getElementById('devnahianPoolWaiverModal').style.display = 'flex';
}

// Close modal on clicking background overlay
document.addEventListener('click', function(e) {
    var modal = document.getElementById('devnahianPoolWaiverModal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});
</script>