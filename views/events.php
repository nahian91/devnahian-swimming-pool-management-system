<?php
/**
 * View: Bookings & Events Management (Enterprise UI/UX Edition)
 *
 * @package SwimmingPoolManager
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

global $wpdb;

$t_events = $wpdb->prefix . 'ifs_pms_events';
$t_cust   = $wpdb->prefix . 'ifs_pms_customers';
$currency = esc_html( (string) get_option( 'ifs_pms_currency', 'BDT' ) );
$base_url = admin_url( 'admin.php?page=ifs-pms-events' );

// ============================================================================
// ACTION DISPATCHER (CREATE, EDIT, DELETE EVENTS)
// ============================================================================
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['ifs_pms_event_action'] ) ) {
    if ( ! isset( $_POST['ifs_pms_event_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifs_pms_event_nonce'] ) ), 'ifs_pms_secure_event' ) ) {
        wp_die( esc_html__( 'Security check failed. Please refresh and try again.', 'swimming-pool-manager' ) );
    }

    $action = sanitize_key( $_POST['ifs_pms_event_action'] );

    if ( 'create_event' === $action ) {
        $title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $customer_id = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
        $event_date  = isset( $_POST['event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_date'] ) ) : '';
        $amount      = isset( $_POST['amount'] ) ? max( 0.00, floatval( wp_unslash( $_POST['amount'] ) ) ) : 0.00;
        $status      = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'Scheduled';

        // Convert datetime-local to MySQL format
        $mysql_date = gmdate( 'Y-m-d H:i:s', strtotime( $event_date ) );

        if ( ! empty( $title ) && $customer_id > 0 && ! empty( $event_date ) ) {
            $wpdb->insert(
                $t_events,
                array(
                    'title'       => $title,
                    'customer_id' => $customer_id,
                    'event_date'  => $mysql_date,
                    'amount'      => $amount,
                    'status'      => $status,
                ),
                array( '%s', '%d', '%s', '%f', '%s' )
            );
            wp_safe_redirect( add_query_arg( array( 'msg' => 'event_created' ), $base_url ) );
            exit;
        }
    }

    if ( 'edit_event' === $action ) {
        $event_id    = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
        $title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $customer_id = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
        $event_date  = isset( $_POST['event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_date'] ) ) : '';
        $amount      = isset( $_POST['amount'] ) ? max( 0.00, floatval( wp_unslash( $_POST['amount'] ) ) ) : 0.00;
        $status      = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'Scheduled';

        $mysql_date = gmdate( 'Y-m-d H:i:s', strtotime( $event_date ) );

        if ( $event_id > 0 && ! empty( $title ) && $customer_id > 0 ) {
            $wpdb->update(
                $t_events,
                array(
                    'title'       => $title,
                    'customer_id' => $customer_id,
                    'event_date'  => $mysql_date,
                    'amount'      => $amount,
                    'status'      => $status,
                ),
                array( 'id' => $event_id ),
                array( '%s', '%d', '%s', '%f', '%s' ),
                array( '%d' )
            );
            wp_safe_redirect( add_query_arg( array( 'msg' => 'event_updated' ), $base_url ) );
            exit;
        }
    }

    if ( 'delete_event' === $action ) {
        $event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
        if ( $event_id > 0 ) {
            $wpdb->delete( $t_events, array( 'id' => $event_id ), array( '%d' ) );
            wp_safe_redirect( add_query_arg( array( 'msg' => 'event_deleted' ), $base_url ) );
            exit;
        }
    }
}

// ============================================================================
// DATA FETCHING
// ============================================================================

// Fetch all events joined with customer details
$events = $wpdb->get_results( "
    SELECT e.*, c.name AS customer_name, c.phone AS customer_phone 
    FROM {$t_events} e
    LEFT JOIN {$t_cust} c ON e.customer_id = c.id
    ORDER BY e.event_date ASC
" );

// Fetch customers for the dropdowns
$customers = $wpdb->get_results( "SELECT id, name, phone FROM {$t_cust} ORDER BY name ASC" );

// Handle Flash Messages
$msg_code = isset( $_GET['msg'] ) ? sanitize_key( $_GET['msg'] ) : '';
?>

<div class="devnahian-pool-panel" style="background: var(--ifs-surface, #ffffff); border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04);">
    
    <?php if ( 'event_created' === $msg_code ) : ?>
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
            <?php esc_html_e( 'New booking successfully scheduled.', 'swimming-pool-manager' ); ?>
        </div>
    <?php elseif ( 'event_updated' === $msg_code ) : ?>
        <div style="background: #f0f9ff; border: 1.5px solid #bae6fd; color: #0369a1; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
            <?php esc_html_e( 'Booking details updated successfully.', 'swimming-pool-manager' ); ?>
        </div>
    <?php elseif ( 'event_deleted' === $msg_code ) : ?>
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
            <?php esc_html_e( 'Booking removed permanently from the registry.', 'swimming-pool-manager' ); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 800; color: var(--ifs-text-primary, #0f172a); display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-calendar-alt" style="color: #0284c7;"></span> 
                <?php esc_html_e( 'Facility Bookings & Events Ledger', 'swimming-pool-manager' ); ?>
            </h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;"><?php esc_html_e( 'Manage private pool parties, cabana rentals, and corporate events.', 'swimming-pool-manager' ); ?></p>
        </div>
        <button class="ifs-pms-btn ifs-pms-btn-primary" onclick="devnahianPoolOpenEventAddModal()" style="background: #0284c7; color: #fff; font-weight: 800; padding: 8px 18px; border-radius: 10px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);">
            <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'New Booking', 'swimming-pool-manager' ); ?>
        </button>
    </div>

    <table class="wp-list-table widefat fixed striped table-view-list" style="border-radius: 10px; overflow: hidden; border: 1.5px solid #cbd5e1;">
        <thead>
            <tr style="background: #f8fafc; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;">
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Event Title / Occasion', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Host / Patron', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Scheduled Date & Time', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Quoted Amount', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px;"><?php esc_html_e( 'Status', 'swimming-pool-manager' ); ?></th>
                <th style="padding: 12px 14px; text-align: right;"><?php esc_html_e( 'Actions', 'swimming-pool-manager' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $events ) ) : ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 48px; color: #64748b;">
                        <span class="dashicons dashicons-calendar-alt" style="font-size: 36px; width: 36px; height: 36px; opacity: 0.4; margin-bottom: 8px;"></span><br>
                        <div style="font-weight: 700; font-size: 14px; color: #0f172a;"><?php esc_html_e( 'No upcoming events or bookings scheduled.', 'swimming-pool-manager' ); ?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;"><?php esc_html_e( 'Click "New Booking" above to schedule a pool event.', 'swimming-pool-manager' ); ?></div>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $events as $event ) : 
                    // Determine Status Badge Colors
                    $badge_style = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
                    if ( 'Scheduled' === $event->status ) {
                        $badge_style = 'background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;';
                    } elseif ( 'Ongoing' === $event->status || 'In Progress' === $event->status ) {
                        $badge_style = 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;';
                    } elseif ( 'Completed' === $event->status ) {
                        $badge_style = 'background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;';
                    } elseif ( 'Cancelled' === $event->status ) {
                        $badge_style = 'background: #fef2f2; color: #ef4444; border: 1px solid #fca5a5;';
                    }
                ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px 14px;">
                            <strong style="color: #0f172a; font-size: 14px;"><?php echo esc_html( $event->title ); ?></strong>
                        </td>
                        <td style="padding: 12px 14px;">
                            <div style="font-weight: 700; color: #334155; margin-bottom: 2px;">
                                <?php echo esc_html( $event->customer_name ?: __( 'Unknown Patron', 'swimming-pool-manager' ) ); ?>
                            </div>
                            <div class="ifs-pms-mono" style="font-size: 11.5px; color: #64748b;">
                                <?php echo esc_html( $event->customer_phone ?: '-' ); ?>
                            </div>
                        </td>
                        <td style="padding: 12px 14px; color: #334155;">
                            <div style="font-weight: 700; font-size: 13px;"><?php echo esc_html( gmdate( 'F j, Y', strtotime( $event->event_date ) ) ); ?></div>
                            <div class="ifs-pms-mono" style="font-size: 11.5px; color: #64748b;"><?php echo esc_html( gmdate( 'g:i A', strtotime( $event->event_date ) ) ); ?></div>
                        </td>
                        <td class="ifs-pms-mono" style="padding: 12px 14px; font-weight: 800; color: #0284c7;">
                            <?php echo esc_html( $currency . ' ' . number_format( (float) $event->amount, 2 ) ); ?>
                        </td>
                        <td style="padding: 12px 14px;">
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; <?php echo esc_attr( $badge_style ); ?>">
                                <?php echo esc_html( $event->status ); ?>
                            </span>
                        </td>
                        <td style="padding: 12px 14px; text-align: right; white-space: nowrap;">
                            <button type="button" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" 
                                onclick='devnahianPoolOpenEventEditModal(<?php echo wp_json_encode( array(
                                    'id'          => $event->id,
                                    'title'       => $event->title,
                                    'customer_id' => $event->customer_id,
                                    'event_date'  => gmdate( 'Y-m-d\TH:i', strtotime( $event->event_date ) ),
                                    'amount'      => $event->amount,
                                    'status'      => $event->status,
                                ) ); ?>)'>
                                <span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Edit', 'swimming-pool-manager' ); ?>
                            </button>

                            <form method="POST" action="<?php echo esc_url( $base_url ); ?>" style="display: inline-block; margin: 0 0 0 4px;" onsubmit="return confirm('<?php esc_attr_e( 'Are you sure you want to permanently delete this booking?', 'swimming-pool-manager' ); ?>');">
                                <?php wp_nonce_field( 'ifs_pms_secure_event', 'ifs_pms_event_nonce' ); ?>
                                <input type="hidden" name="ifs_pms_event_action" value="delete_event">
                                <input type="hidden" name="event_id" value="<?php echo esc_attr( $event->id ); ?>">
                                <button type="submit" class="ifs-pms-btn ifs-pms-btn-sm" style="background: #fff1f2; color: #ef4444; border: 1px solid #fecaca; padding: 6px 8px; border-radius: 6px; cursor: pointer;" title="<?php esc_attr_e( 'Delete Booking', 'swimming-pool-manager' ); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL: Add New Booking -->
<div id="devnahianPoolEventAddModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 500px; padding: 28px; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <h3 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-calendar-alt" style="color: #0284c7;"></span> <?php esc_html_e( 'Schedule New Booking', 'swimming-pool-manager' ); ?>
        </h3>
        
        <form method="POST" action="<?php echo esc_url( $base_url ); ?>">
            <?php wp_nonce_field( 'ifs_pms_secure_event', 'ifs_pms_event_nonce' ); ?>
            <input type="hidden" name="ifs_pms_event_action" value="create_event">
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Event Title / Occasion', 'swimming-pool-manager' ); ?> *</label>
                <input type="text" name="title" required placeholder="<?php esc_attr_e( 'e.g. Corporate Retreat Pool Party', 'swimming-pool-manager' ); ?>" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 600;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Select Host / Patron', 'swimming-pool-manager' ); ?> *</label>
                <select name="customer_id" required style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 600; color: #0f172a;">
                    <option value=""><?php esc_html_e( '-- Select Registered Patron --', 'swimming-pool-manager' ); ?></option>
                    <?php foreach ( $customers as $c ) : ?>
                        <option value="<?php echo esc_attr( $c->id ); ?>"><?php echo esc_html( $c->name . ' (' . $c->phone . ')' ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Date & Time', 'swimming-pool-manager' ); ?> *</label>
                    <input type="datetime-local" name="event_date" required style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php printf( esc_html__( 'Amount (%s)', 'swimming-pool-manager' ), $currency ); ?></label>
                    <input type="number" step="0.01" name="amount" value="0.00" style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 600;" class="ifs-pms-mono">
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="ifs-pms-btn ifs-pms-btn-primary" style="flex: 2; height: 46px; font-weight: 800; border-radius: 10px; background: #0284c7; color: #fff; border: none; cursor: pointer;">
                    <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Save Booking', 'swimming-pool-manager' ); ?>
                </button>
                <button type="button" class="ifs-pms-btn" style="flex: 1; height: 46px; background: #f1f5f9; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; border-radius: 10px; cursor: pointer;" onclick="document.getElementById('devnahianPoolEventAddModal').style.display='none';">
                    <?php esc_html_e( 'Cancel', 'swimming-pool-manager' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit Booking -->
<div id="devnahianPoolEventEditModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 500px; padding: 28px; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <h3 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-edit" style="color: #0284c7;"></span> <?php esc_html_e( 'Update Booking Record', 'swimming-pool-manager' ); ?>
        </h3>
        
        <form method="POST" action="<?php echo esc_url( $base_url ); ?>">
            <?php wp_nonce_field( 'ifs_pms_secure_event', 'ifs_pms_event_nonce' ); ?>
            <input type="hidden" name="ifs_pms_event_action" value="edit_event">
            <input type="hidden" name="event_id" id="devnahianPoolEventEditId" value="">
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Event Title / Occasion', 'swimming-pool-manager' ); ?> *</label>
                <input type="text" name="title" id="devnahianPoolEventEditTitle" required style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 600;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Select Host / Patron', 'swimming-pool-manager' ); ?> *</label>
                <select name="customer_id" id="devnahianPoolEventEditCustomer" required style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 600; color: #0f172a;">
                    <?php foreach ( $customers as $c ) : ?>
                        <option value="<?php echo esc_attr( $c->id ); ?>"><?php echo esc_html( $c->name . ' (' . $c->phone . ')' ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Date & Time', 'swimming-pool-manager' ); ?> *</label>
                    <input type="datetime-local" name="event_date" id="devnahianPoolEventEditDate" required style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php printf( esc_html__( 'Amount (%s)', 'swimming-pool-manager' ), $currency ); ?></label>
                    <input type="number" step="0.01" name="amount" id="devnahianPoolEventEditAmount" required style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 600;" class="ifs-pms-mono">
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13.5px;"><?php esc_html_e( 'Event Status', 'swimming-pool-manager' ); ?> *</label>
                <select name="status" id="devnahianPoolEventEditStatus" required style="width: 100%; border-radius: 8px; border: 1.5px solid #cbd5e1; height: 44px; padding: 0 12px; font-weight: 600; color: #0f172a;">
                    <option value="Scheduled"><?php esc_html_e( 'Scheduled', 'swimming-pool-manager' ); ?></option>
                    <option value="Ongoing"><?php esc_html_e( 'Ongoing', 'swimming-pool-manager' ); ?></option>
                    <option value="Completed"><?php esc_html_e( 'Completed', 'swimming-pool-manager' ); ?></option>
                    <option value="Cancelled"><?php esc_html_e( 'Cancelled', 'swimming-pool-manager' ); ?></option>
                </select>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="ifs-pms-btn ifs-pms-btn-primary" style="flex: 2; height: 46px; font-weight: 800; border-radius: 10px; background: #0284c7; color: #fff; border: none; cursor: pointer;">
                    <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Update Booking', 'swimming-pool-manager' ); ?>
                </button>
                <button type="button" class="ifs-pms-btn" style="flex: 1; height: 46px; background: #f1f5f9; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; border-radius: 10px; cursor: pointer;" onclick="document.getElementById('devnahianPoolEventEditModal').style.display='none';">
                    <?php esc_html_e( 'Cancel', 'swimming-pool-manager' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function devnahianPoolOpenEventAddModal() {
    document.getElementById('devnahianPoolEventAddModal').style.display = 'flex';
}

function devnahianPoolOpenEventEditModal(data) {
    document.getElementById('devnahianPoolEventEditId').value = data.id;
    document.getElementById('devnahianPoolEventEditTitle').value = data.title;
    document.getElementById('devnahianPoolEventEditCustomer').value = data.customer_id;
    document.getElementById('devnahianPoolEventEditDate').value = data.event_date;
    document.getElementById('devnahianPoolEventEditAmount').value = parseFloat(data.amount).toFixed(2);
    document.getElementById('devnahianPoolEventEditStatus').value = data.status;
    
    document.getElementById('devnahianPoolEventEditModal').style.display = 'flex';
}

// Close modals when clicking on the dark background overlay
document.addEventListener('click', function(e) {
    var addModal = document.getElementById('devnahianPoolEventAddModal');
    var editModal = document.getElementById('devnahianPoolEventEditModal');
    
    if (e.target === addModal) {
        addModal.style.display = 'none';
    }
    if (e.target === editModal) {
        editModal.style.display = 'none';
    }
});
</script>