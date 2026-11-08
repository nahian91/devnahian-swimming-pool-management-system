<?php
/**
 * View: Maintenance & Work Orders (Enterprise UI/UX Edition)
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

global $wpdb;
$t_maint  = $wpdb->prefix . 'ifs_pms_maintenance';
$base_url = admin_url( 'admin.php?page=ifs-pms-maintenance' );
$operator = wp_get_current_user()->display_name;

// ============================================================================
// SELF-CONTAINED ACTION DISPATCHER (CREATE, UPDATE, DELETE MAINTENANCE TASKS)
// ============================================================================
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['devnahian_pool_maint_action'] ) ) {
    if ( ! isset( $_POST['devnahian_pool_maint_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['devnahian_pool_maint_nonce'] ) ), 'devnahian_pool_secure_maint' ) ) {
        wp_die( esc_html__( 'Security check failed. Please refresh and try again.', 'swimming-pool-manager' ) );
    }

    $action = sanitize_key( $_POST['devnahian_pool_maint_action'] );

    // 1. Report New Issue
    if ( 'create_task' === $action ) {
        $title = isset( $_POST['task_title'] ) ? sanitize_text_field( wp_unslash( $_POST['task_title'] ) ) : '';
        if ( ! empty( $title ) ) {
            $wpdb->insert(
                $t_maint,
                array(
                    'task_title'  => $title,
                    'status'      => 'Pending',
                    'reported_by' => $operator,
                    'reported_at' => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%s', '%s' )
            );
            wp_safe_redirect( add_query_arg( array( 'msg' => 'task_created' ), $base_url ) );
            exit;
        }
    }

    // 2. Update Task Status (Resolve / In Progress)
    if ( 'update_task' === $action ) {
        $task_id = isset( $_POST['task_id'] ) ? absint( $_POST['task_id'] ) : 0;
        $status  = isset( $_POST['task_status'] ) ? sanitize_text_field( wp_unslash( $_POST['task_status'] ) ) : 'Pending';

        $allowed = array( 'Pending', 'In Progress', 'Resolved' );
        if ( ! in_array( $status, $allowed, true ) ) {
            $status = 'Pending';
        }

        if ( $task_id > 0 ) {
            $update_data = array( 'status' => $status );
            $format      = array( '%s' );

            if ( 'Resolved' === $status ) {
                $update_data['resolved_by'] = $operator;
                $update_data['resolved_at'] = current_time( 'mysql' );
                $format[]                   = '%s';
                $format[]                   = '%s';
            }

            $wpdb->update( $t_maint, $update_data, array( 'id' => $task_id ), $format, array( '%d' ) );
            wp_safe_redirect( add_query_arg( array( 'msg' => 'task_updated' ), $base_url ) );
            exit;
        }
    }

    // 3. Delete Task
    if ( 'delete_task' === $action && ( current_user_can( 'manage_options' ) || current_user_can( 'ozone_manage_settings' ) ) ) {
        $task_id = isset( $_POST['task_id'] ) ? absint( $_POST['task_id'] ) : 0;
        if ( $task_id > 0 ) {
            $wpdb->delete( $t_maint, array( 'id' => $task_id ), array( '%d' ) );
            wp_safe_redirect( add_query_arg( array( 'msg' => 'task_deleted' ), $base_url ) );
            exit;
        }
    }
}

// Fetch all maintenance records
$tasks    = $wpdb->get_results( "SELECT * FROM {$t_maint} ORDER BY reported_at DESC" );
$msg_code = isset( $_GET['msg'] ) ? sanitize_key( $_GET['msg'] ) : '';
?>

<div class="devnahian-pool-panel" style="background: var(--ifs-surface, #ffffff); border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04);">
    
    <?php if ( 'task_created' === $msg_code ) : ?>
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
            <?php esc_html_e( 'Work order successfully logged and dispatched.', 'swimming-pool-manager' ); ?>
        </div>
    <?php elseif ( 'task_updated' === $msg_code ) : ?>
        <div style="background: #f0f9ff; border: 1.5px solid #bae6fd; color: #0369a1; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
            <?php esc_html_e( 'Maintenance task status updated.', 'swimming-pool-manager' ); ?>
        </div>
    <?php elseif ( 'task_deleted' === $msg_code ) : ?>
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
            <?php esc_html_e( 'Work order record deleted permanently.', 'swimming-pool-manager' ); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 800; color: var(--ifs-text-primary, #0f172a); display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-admin-tools" style="color: #0284c7;"></span> 
                <?php esc_html_e( 'Maintenance & Facility Work Orders', 'swimming-pool-manager' ); ?>
            </h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;"><?php esc_html_e( 'Track pool deck repairs, equipment servicing, and resolution logs.', 'swimming-pool-manager' ); ?></p>
        </div>
        <button class="ifs-pms-btn ifs-pms-btn-primary" onclick="devnahianPoolOpenMaintModal()" style="background: #0284c7; color: #fff; font-weight: 800; padding: 8px 18px; border-radius: 10px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);">
            <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Report Issue', 'swimming-pool-manager' ); ?>
        </button>
    </div>

    <table class="wp-list-table widefat fixed striped table-view-list" style="border-radius: 10px; overflow: hidden; border: 1.5px solid #cbd5e1;">
        <thead>
            <tr style="background: #f8fafc; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;">
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Task / Issue Description', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Reported By', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Date Reported', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Status', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px; text-align: right;"><?php esc_html_e( 'Actions', 'swimming-pool-manager' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $tasks ) ) : ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 48px; color: #64748b;">
                        <span class="dashicons dashicons-yes-alt" style="font-size: 36px; width: 36px; height: 36px; color: #10b981; margin-bottom: 8px;"></span><br>
                        <div style="font-weight: 700; font-size: 14px; color: #0f172a;"><?php esc_html_e( 'No active maintenance tasks.', 'swimming-pool-manager' ); ?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;"><?php esc_html_e( 'All swimming pool facilities and equipment are fully operational.', 'swimming-pool-manager' ); ?></div>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $tasks as $task ) : 
                    // Status Badge Colors
                    if ( 'Resolved' === $task->status ) {
                        $badge_style = 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;';
                    } elseif ( 'In Progress' === $task->status ) {
                        $badge_style = 'background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;';
                    } else {
                        $badge_style = 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;';
                    }
                ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px 14px;">
                            <strong style="color: #0f172a; font-size: 14px; display: block; margin-bottom: 2px;"><?php echo esc_html( $task->task_title ); ?></strong>
                            <?php if ( ! empty( $task->resolved_by ) ) : ?>
                                <span style="font-size: 11px; color: #64748b;">Resolved by <?php echo esc_html( $task->resolved_by ); ?> on <?php echo esc_html( gmdate( 'M j, Y', strtotime( $task->resolved_at ) ) ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px 14px; color: #334155; font-weight: 600;">
                            <?php echo esc_html( $task->reported_by ); ?>
                        </td>
                        <td class="ifs-pms-mono" style="padding: 12px 14px; color: #64748b; font-size: 12.5px;">
                            <?php echo esc_html( gmdate( 'M j, Y — g:i A', strtotime( $task->reported_at ) ) ); ?>
                        </td>
                        <td style="padding: 12px 14px;">
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; <?php echo esc_attr( $badge_style ); ?>">
                                <?php echo esc_html( $task->status ); ?>
                            </span>
                        </td>
                        <td style="padding: 12px 14px; text-align: right; white-space: nowrap;">
                            <button type="button" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; padding: 5px 10px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" onclick="devnahianPoolOpenMaintEditModal(<?php echo esc_attr( $task->id ); ?>, '<?php echo esc_js( $task->task_title ); ?>', '<?php echo esc_js( $task->status ); ?>')">
                                <span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Update Status', 'swimming-pool-manager' ); ?>
                            </button>

                            <?php if ( current_user_can( 'manage_options' ) || current_user_can( 'ozone_manage_settings' ) ) : ?>
                                <form method="POST" action="<?php echo esc_url( $base_url ); ?>" style="display: inline-block; margin: 0 0 0 4px;" onsubmit="return confirm('<?php esc_attr_e( 'Permanently delete this maintenance log?', 'swimming-pool-manager' ); ?>');">
                                    <?php wp_nonce_field( 'devnahian_pool_secure_maint', 'devnahian_pool_maint_nonce' ); ?>
                                    <input type="hidden" name="devnahian_pool_maint_action" value="delete_task">
                                    <input type="hidden" name="task_id" value="<?php echo esc_attr( $task->id ); ?>">
                                    <button type="submit" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #fff1f2; color: #ef4444; border: 1px solid #fecaca; padding: 5px 8px; border-radius: 6px; cursor: pointer;" title="<?php esc_attr_e( 'Delete Task', 'swimming-pool-manager' ); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL: Report New Maintenance Issue -->
<div id="devnahianPoolMaintModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 440px; padding: 24px; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <h3 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-admin-tools" style="color: #0284c7;"></span> <?php esc_html_e( 'Report Maintenance Issue', 'swimming-pool-manager' ); ?>
        </h3>
        
        <form method="POST" action="<?php echo esc_url( $base_url ); ?>">
            <?php wp_nonce_field( 'devnahian_pool_secure_maint', 'devnahian_pool_maint_nonce' ); ?>
            <input type="hidden" name="devnahian_pool_maint_action" value="create_task">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Issue Description / Task Title', 'swimming-pool-manager' ); ?> *</label>
                <textarea name="task_title" required rows="3" placeholder="<?php esc_attr_e( 'e.g. Pump #2 filter pressure high / Skimmer basket cracked', 'swimming-pool-manager' ); ?>" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 12px; font-weight: 600; font-family: inherit;"></textarea>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="ifs-pms-btn ifs-pms-btn-primary" style="flex: 2; height: 44px; font-weight: 800; border-radius: 8px; background: #0284c7; color: #fff; border: none; cursor: pointer;">
                    <?php esc_html_e( 'Dispatch Work Order', 'swimming-pool-manager' ); ?>
                </button>
                <button type="button" class="ifs-pms-btn" style="flex: 1; height: 44px; background: #f1f5f9; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; border-radius: 8px; cursor: pointer;" onclick="document.getElementById('devnahianPoolMaintModal').style.display='none';">
                    <?php esc_html_e( 'Cancel', 'swimming-pool-manager' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Update Task Status -->
<div id="devnahianPoolMaintEditModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 380px; padding: 24px; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-edit" style="color: #0284c7;"></span> <?php esc_html_e( 'Update Work Order Status', 'swimming-pool-manager' ); ?>
        </h3>
        
        <div id="devnahianPoolEditTaskTitleDisplay" style="font-size: 13px; font-weight: 700; color: #475569; background: #f8fafc; padding: 10px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #e2e8f0;"></div>

        <form method="POST" action="<?php echo esc_url( $base_url ); ?>">
            <?php wp_nonce_field( 'devnahian_pool_secure_maint', 'devnahian_pool_maint_nonce' ); ?>
            <input type="hidden" name="devnahian_pool_maint_action" value="update_task">
            <input type="hidden" name="task_id" id="devnahianPoolEditTaskId" value="">
            
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 8px; font-size: 13.5px;"><?php esc_html_e( 'Current Status', 'swimming-pool-manager' ); ?></label>
                <select name="task_status" id="devnahianPoolEditTaskStatusSelect" style="width: 100%; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 700; padding: 0 12px; color: #0f172a;">
                    <option value="Pending"><?php esc_html_e( 'Pending (Awaiting Repair)', 'swimming-pool-manager' ); ?></option>
                    <option value="In Progress"><?php esc_html_e( 'In Progress (Being Serviced)', 'swimming-pool-manager' ); ?></option>
                    <option value="Resolved"><?php esc_html_e( 'Resolved (Fixed & Operational)', 'swimming-pool-manager' ); ?></option>
                </select>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="ifs-pms-btn ifs-pms-btn-primary" style="flex: 2; height: 44px; font-weight: 800; border-radius: 8px; background: #0284c7; color: #fff; border: none; cursor: pointer;">
                    <?php esc_html_e( 'Save Status', 'swimming-pool-manager' ); ?>
                </button>
                <button type="button" class="ifs-pms-btn" style="flex: 1; height: 44px; background: #f1f5f9; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; border-radius: 8px; cursor: pointer;" onclick="document.getElementById('devnahianPoolMaintEditModal').style.display='none';">
                    <?php esc_html_e( 'Cancel', 'swimming-pool-manager' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function devnahianPoolOpenMaintModal() {
    document.getElementById('devnahianPoolMaintModal').style.display = 'flex';
}

function devnahianPoolOpenMaintEditModal(id, title, status) {
    document.getElementById('devnahianPoolEditTaskId').value = id;
    document.getElementById('devnahianPoolEditTaskTitleDisplay').textContent = title;
    document.getElementById('devnahianPoolEditTaskStatusSelect').value = status;
    document.getElementById('devnahianPoolMaintEditModal').style.display = 'flex';
}

// Close modals on clicking background overlay
document.addEventListener('click', function(e) {
    var addModal = document.getElementById('devnahianPoolMaintModal');
    var editModal = document.getElementById('devnahianPoolMaintEditModal');
    if (e.target === addModal) {
        addModal.style.display = 'none';
    }
    if (e.target === editModal) {
        editModal.style.display = 'none';
    }
});
</script>