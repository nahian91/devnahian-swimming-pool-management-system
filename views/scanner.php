<?php
/**
 * View: Gate Turnstile Control & QR Validator Console (Dashicons UI)
 *
 * @package Ozone_Skypool_OS
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue external scanner console stylesheet
if ( defined( 'IFS_PMS_URL' ) && defined( 'IFS_PMS_VERSION' ) ) {
    wp_enqueue_style(
        'oz-scanner-console-css',
        IFS_PMS_URL . 'assets/css/scanner-console.css',
        array( 'dashicons' ),
        IFS_PMS_VERSION
    );
}

$currency         = esc_html( get_option( 'ifs_pms_currency', 'BDT' ) );$current_operator = wp_get_current_user()->display_name;

// Generate today's base prefix matching the 3-letter month and day format (e.g., OZ-SEP-18-)
$current_mon_prefix = 'OZ-' . strtoupper( current_time( 'M' ) ) . '-' . current_time( 'd' ) . '-';
?>

<div class="oz-scanner-layout">
    <!-- LEFT: Main Validator Console -->
    <div class="oz-panel-card">
        <div class="oz-panel-head">
            <h3 class="oz-panel-title">
                <span class="dashicons dashicons-fullscreen-alt"></span>
                <?php esc_html_e( 'Gate Turnstile Validation Console', 'swimming-pool-manager' ); ?>
            </h3>
            <span class="ifs-pms-badge ifs-pms-badge-success">
                <span class="ifs-pms-pulse-dot"></span> <?php esc_html_e( 'Relay Port Online', 'swimming-pool-manager' ); ?>
            </span>
        </div>

        <div class="oz-console-body">
            <!-- Electronic Barrier Status Bar with Live Countdown -->
            <div class="oz-barrier-relay-bar">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(2, 132, 199, 0.1); color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <span class="dashicons dashicons-admin-settings"></span>
                    </div>
                    <div>
                        <div style="font-size: 14px; font-weight: 800; color: #0f172a;"><?php esc_html_e( 'Electronic Gate Barrier #01', 'swimming-pool-manager' ); ?></div>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 1px;" id="ozBarrierSub">
                            <?php esc_html_e( '4000ms actuation pulse • Auto-relock armed', 'swimming-pool-manager' ); ?>
                        </div>
                    </div>
                </div>

                <div class="oz-relay-status-pill closed" id="ozTurnstileStatePill">
                    <span class="dashicons dashicons-shield"></span> <span id="ozTurnstileStateTxt"><?php esc_html_e( 'BARRIER LOCKED', 'swimming-pool-manager' ); ?></span>
                </div>
            </div>

            <!-- Optical HUD Viewport -->
            <div class="oz-scanner-viewport" id="ozScannerViewport">
                <span class="oz-hud-reticle top-left"></span>
                <span class="oz-hud-reticle top-right"></span>
                <span class="oz-hud-reticle bottom-left"></span>
                <span class="oz-hud-reticle bottom-right"></span>
                <div class="oz-laser-beam"></div>

                <video id="ozScannerVideo" class="oz-scanner-video" playsinline muted></video>

                <div id="ozScannerPlaceholder" style="text-align: center; color: #64748b; padding: 20px;">
                    <div style="font-size: 64px; opacity: 0.3; margin-bottom: 12px; display: inline-flex;">
                        <span class="dashicons dashicons-fullscreen-alt" style="font-size: 64px; width: 64px; height: 64px;"></span>
                    </div>
                    <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.8px; text-transform: uppercase;">
                        <?php esc_html_e( 'Ready for USB Scanner Gun or WebCam', 'swimming-pool-manager' ); ?>
                    </div>
                </div>
            </div>

            <!-- Scanner Camera Controls -->
            <div style="display: flex; gap: 12px;">
                <button type="button" class="oz-btn oz-btn-secondary" style="flex: 1.4;" id="ozToggleCamBtn" onclick="ifsPms.toggleCamera()">
                    <span class="dashicons dashicons-lightbulb"></span> <span id="ozCamBtnLabel"><?php esc_html_e( 'Activate WebCam Scanner', 'swimming-pool-manager' ); ?></span>
                </button>
                <button type="button" class="oz-btn oz-btn-secondary" style="flex: 1;" onclick="ifsPms.resetScannerConsole()">
                    <span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'Clear Standby', 'swimming-pool-manager' ); ?>
                </button>
            </div>

            <!-- Manual Barcode Token Input -->
            <div class="oz-field-group">
                <label class="oz-field-label" for="ozScanSerialInput">
                    <?php esc_html_e( 'Pass Barcode / Turnstile Token UID', 'swimming-pool-manager' ); ?>
                </label>

                <div class="oz-split-token-group" style="display: flex; gap: 8px; align-items: center;">
                    <div style="display: flex; align-items: center; flex: 1; border: 1.5px solid #cbd5e1; border-radius: 12px; background: #ffffff; overflow: hidden;">
                        <span class="oz-token-prefix-addon ifs-pms-mono" id="ozDailyPrefixSpan" style="padding: 0 12px; background: #f1f5f9; border-right: 1px solid #cbd5e1; font-weight: 700; color: #475569; display: flex; align-items: center;"><?php echo esc_html( $current_mon_prefix ); ?></span>
                        <input type="text" id="ozScanSerialInput" class="oz-token-number-input ifs-pms-mono" placeholder="0001" autofocus autocomplete="off" style="border: none; box-shadow: none; padding: 10px 14px; width: 100%;">
                    </div>
                    <button type="button" class="oz-btn oz-btn-primary" onclick="ifsPms.executeVerification()" style="height: 48px; padding: 0 20px; border-radius: 12px; white-space: nowrap; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;">
                        <span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Check Code', 'swimming-pool-manager' ); ?>
                    </button>
                </div>

                <div style="font-size: 11.5px; color: #64748b; margin-top: 6px; display: flex; justify-content: space-between;">
                    <span><?php esc_html_e( 'Enter ticket serial number and press Enter or click Check Code to verify.', 'swimming-pool-manager' ); ?></span>
                    <kbd style="font-family: var(--ifs-font-mono, monospace); background: #f1f5f9; border: 1px solid #cbd5e1; padding: 1px 6px; border-radius: 4px; font-weight: 700;">Manual Trigger</kbd>
                </div>
            </div>

            <!-- Dynamic Feedback & Alert Banner -->
            <div id="ozScannerStatusBar" class="oz-scanner-status-bar is-standby" style="transition: all 0.3s ease; padding: 12px; border-radius: 8px; font-weight: 600; margin-top: 16px; background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                <span class="dashicons dashicons-building"></span> <span id="ozStatusBarText"><?php esc_html_e( 'Terminal Standby • Present pass to scanner', 'swimming-pool-manager' ); ?></span>
            </div>
        </div>
    </div>

    <!-- RIGHT: Complete Verification Telemetry Stream (Full Dossier) -->
    <div class="oz-panel-card">
        <div class="oz-panel-head">
            <h3 class="oz-panel-title">
                <span class="dashicons dashicons-building"></span>
                <?php esc_html_e( 'Scanner', 'swimming-pool-manager' ); ?>
            </h3>
            
            <div class="ifs-pms-flex-gap-8" style="gap: 8px; display: flex; align-items: center;">
                <button type="button" class="oz-btn oz-btn-sm oz-btn-secondary" onclick="if(typeof ifsPms !== 'undefined' && ifsPms.manualPulseRelay) { ifsPms.manualPulseRelay(); } else { alert('Barrier relay triggered.'); }" title="<?php esc_attr_e( 'Manually pulse barrier relay open', 'swimming-pool-manager' ); ?>">
                    <span class="dashicons dashicons-unlock"></span> <?php esc_html_e( 'Unlock Gate', 'swimming-pool-manager' ); ?>
                </button>
            </div>
        </div>

        <div class="oz-console-body">
            <!-- Full Info Real-Time Telemetry Card -->
            <div id="ozResultTelemetryBox" class="oz-telemetry-result-card" style="border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 20px; background: #ffffff;">
                
                <!-- 1. Patron Dossier Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1.5px dashed #e2e8f0;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div id="ozResultAvatar" style="width: 48px; height: 48px; border-radius: 14px; background: #f0f9ff; color: #0284c7; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            <span class="dashicons dashicons-admin-users" style="font-size: 24px; width: 24px; height: 24px;"></span>
                        </div>
                        <div>
                            <strong id="ozResultPatron" style="font-size: 18px; color: #0f172a; display: block; font-weight: 800; line-height: 1.2;">
                                <?php esc_html_e( 'Awaiting Pass Scan', 'swimming-pool-manager' ); ?>
                            </strong>
                        </div>
                    </div>
                    <span id="ozResultBadge" class="ifs-pms-badge" style="background: #f1f5f9; color: #64748b; font-size: 12px; padding: 6px 14px; border-radius: 20px; font-weight: 800;">
                        <?php esc_html_e( 'Standby', 'swimming-pool-manager' ); ?>
                    </span>
                </div>

                <!-- 2. Full Telemetry Metric Matrix -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 16px;">
                    <div class="oz-telemetry-item" style="background: #f0f9ff; padding: 10px 14px; border-radius: 10px; border: 1.5px solid #bae6fd;">
                        <span style="font-size: 10px; font-weight: 800; color: #0369a1; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Customer Name</span>
                        <strong id="ozResultCardCustomerName" style="color: #0f172a; font-size: 14px; margin-top: 2px; display: block; font-weight: 800;">—</strong>
                    </div>

                    <div class="oz-telemetry-item" style="background: #f0fdf4; padding: 10px 14px; border-radius: 10px; border: 1.5px solid #bbf7d0;">
                        <span style="font-size: 10px; font-weight: 800; color: #15803d; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Customer Mobile</span>
                        <strong id="ozResultCardCustomerPhone" class="ifs-pms-mono" style="color: #166534; font-size: 14px; margin-top: 2px; display: block; font-weight: 800;">—</strong>
                    </div>

                    <div class="oz-telemetry-item" style="background: #f8fafc; padding: 10px 14px; border-radius: 10px; border: 1px solid #f1f5f9;">
                        <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Token Code</span>
                        <strong id="ozResultCode" class="ifs-pms-mono" style="color: #0284c7; font-size: 13.5px; margin-top: 2px; display: block;">—</strong>
                    </div>

                    <!-- Customer Type (Replaced Barrier Gate Port) -->
                    <div class="oz-telemetry-item" style="background: #f8fafc; padding: 10px 14px; border-radius: 10px; border: 1px solid #f1f5f9;">
                        <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Customer Type</span>
                        <div id="ozResultGuestTypeBadge" style="margin-top: 2px;">
                            <strong id="ozResultGuestTypeTxt" style="color: #0f172a; font-size: 13.5px; display: block;">—</strong>
                        </div>
                    </div>

                    <div class="oz-telemetry-item" id="ozResultRoomWrap" style="display: none; background: rgba(2, 132, 199, 0.05); padding: 10px 14px; border-radius: 10px; border: 1px solid rgba(2, 132, 199, 0.2);">
                        <span style="font-size: 10px; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Hotel Room Number</span>
                        <strong id="ozResultRoom" class="ifs-pms-mono" style="color: #0284c7; font-size: 13.5px; margin-top: 2px; display: block;">—</strong>
                    </div>

                    <div class="oz-telemetry-item" style="background: #f8fafc; padding: 10px 14px; border-radius: 10px; border: 1px solid #f1f5f9;">
                        <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Booked Session</span>
                        <strong id="ozResultDuration" class="ifs-pms-mono" style="color: #0f172a; font-size: 13px; margin-top: 2px; display: block;">—</strong>
                    </div>

                    <div class="oz-telemetry-item" style="background: #f8fafc; padding: 10px 14px; border-radius: 10px; border: 1px solid #f1f5f9;">
                        <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Valid Until</span>
                        <strong id="ozResultValidUntil" class="ifs-pms-mono" style="color: #10b981; font-size: 13.5px; margin-top: 2px; display: block;">—</strong>
                    </div>

                    <div class="oz-telemetry-item" style="background: #f8fafc; padding: 10px 14px; border-radius: 10px; border: 1px solid #f1f5f9;">
                        <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Total Amount Settled</span>
                        <strong id="ozResultAmount" class="ifs-pms-mono" style="color: #0f172a; font-size: 13.5px; margin-top: 2px; display: block;">—</strong>
                    </div>

                    <div class="oz-telemetry-item" style="background: #f8fafc; padding: 10px 14px; border-radius: 10px; border: 1px solid #f1f5f9;">
                        <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Payment Tender</span>
                        <strong id="ozResultPayment" style="color: #0f172a; font-size: 13px; margin-top: 2px; display: block;">—</strong>
                    </div>

                    <div class="oz-telemetry-item" style="background: #f8fafc; padding: 10px 14px; border-radius: 10px; border: 1px solid #f1f5f9;">
                        <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Desk Issuing Staff</span>
                        <strong id="ozResultSoldBy" style="color: #475569; font-size: 12.5px; margin-top: 2px; display: block;">—</strong>
                    </div>
                </div>

                <!-- 3. Package Details -->
                <div style="margin-top: 12px; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; padding: 10px 14px;">
                    <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">Enrolled Package Inclusions & Amenities</span>
                    <strong id="ozResultPackage" style="color: #0f172a; font-size: 12.5px; line-height: 1.4; display: block; font-weight: 700;">No active scan telemetry loaded.</strong>
                </div>

                <!-- 4. Message Log Row -->
                <div id="ozResultMessageRow" style="margin-top: 14px; font-size: 12px; font-weight: 700; padding: 8px 14px; border-radius: 8px; color: #64748b; background: #f1f5f9; display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-building"></span>
                    <span id="ozResultMessageTxt">Ready to authorize entry gates.</span>
                </div>
            </div>

            <!-- Shift Admittance Ledger Stream (Bottom Container) -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0 8px 0;">
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.08em;">
                    <span class="dashicons dashicons-media-text"></span> Shift Admittance Ledger Stream
                </div>
                <span style="font-size: 11px; color: #94a3b8; font-weight: 600;">Auto-synchronized</span>
            </div>

            <div id="ozScanSessionLogContainer" style="display: flex; flex-direction: column; gap: 8px;">
                <div style="text-align: center; color: #94a3b8; padding: 36px 20px; font-size: 13px;" id="ozScanNoHistory">
                    <div style="font-size: 36px; opacity: 0.35; margin-bottom: 10px; display: inline-flex;">
                        <span class="dashicons dashicons-fullscreen-alt" style="font-size: 36px; width: 36px; height: 36px;"></span>
                    </div>
                    <div>No tickets scanned in this session yet.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Scanner Logic
    var ifsPms = ifsPms || {};

    (function() {
        var inputElem = null;
        var isVerifying = false;
        var isCameraActive = false;

        document.addEventListener('DOMContentLoaded', function() {
            inputElem = document.getElementById('ozScanSerialInput');
            
            if (inputElem) {
                // Focus input on load for USB scanners
                inputElem.focus();

                // Listen for Enter key
                inputElem.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        ifsPms.executeVerification();
                    }
                });

                // Keep focus on input when clicking outside, to accommodate hardware scanners
                document.body.addEventListener('click', function(e) {
                    if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A' && e.target.tagName !== 'SELECT') {
                        inputElem.focus();
                    }
                });
            }
        });

        ifsPms.executeVerification = function() {
            if (isVerifying || !inputElem) return;

            var fullCode = inputElem.value.trim();
            if (!fullCode) {
                ifsPms.updateStatus('Error: Pass barcode cannot be empty', 'error');
                return;
            }

            // Ensure the code has the prefix if it's missing (for manual input convenience)
            var prefix = document.getElementById('ozDailyPrefixSpan').innerText.trim();
            if (fullCode.length <= 4 && !isNaN(fullCode)) {
                 fullCode = prefix + fullCode.padStart(4, '0');
            }

            isVerifying = true;
            ifsPms.updateStatus('Verifying pass ' + fullCode + '...', 'standby');
            
            var data = {
                'action': 'ifs_pms_verify_pass_action',
                'ticket_code': fullCode,
                'security': ifsPmsConfig.security_token 
            };

            jQuery.post(ifsPmsConfig.ajax_url, data, function(response) {
                isVerifying = false;
                if (response.success) {
                    ifsPms.updateDossier(response.data, true);
                    ifsPms.triggerRelay();
                    ifsPms.addToLogStream(response.data, true);
                } else {
                    var errorData = response.data || {};
                    errorData.message = errorData.message || 'Verification Failed';
                    errorData.ticket_code = errorData.ticket_code || fullCode;
                    ifsPms.updateDossier(errorData, false);
                    ifsPms.updateStatus(errorData.message, 'error');
                    ifsPms.addToLogStream(errorData, false);
                }
                
                // Reset input for next scan
                inputElem.value = '';
                inputElem.focus();

            }).fail(function() {
                isVerifying = false;
                ifsPms.updateStatus('Server communication error during verification.', 'error');
                inputElem.value = '';
                inputElem.focus();
            });
        };

        ifsPms.updateDossier = function(data, isSuccess) {
            document.getElementById('ozResultPatron').innerText = data.customer_name || 'Walk-in Guest';
            document.getElementById('ozResultCardCustomerName').innerText = data.customer_name || 'Walk-in Guest';
            document.getElementById('ozResultCardCustomerPhone').innerText = data.customer_phone || '-';
            document.getElementById('ozResultCode').innerText = data.ticket_code || '-';
            
            var guestType = data.guest_type === 'room_guest' || data.room_no ? 'Hotel Room Guest' : 'General Customer';
            document.getElementById('ozResultGuestTypeTxt').innerText = guestType;

            var roomWrap = document.getElementById('ozResultRoomWrap');
            var roomTxt = document.getElementById('ozResultRoom');
            if (data.room_no) {
                roomWrap.style.display = 'block';
                roomTxt.innerText = data.room_no;
            } else {
                roomWrap.style.display = 'none';
            }

            document.getElementById('ozResultDuration').innerText = (data.duration_hours || 1) + ' Hour(s)';
            document.getElementById('ozResultValidUntil').innerText = data.check_out || '-';
            document.getElementById('ozResultAmount').innerText = data.amount ? ifsPmsConfig.currency + ' ' + parseFloat(data.amount).toFixed(2) : '-';
            document.getElementById('ozResultPayment').innerText = data.payment_method || '-';
            document.getElementById('ozResultSoldBy').innerText = data.sold_by || '-';
            document.getElementById('ozResultPackage').innerText = data.package_details || 'Adult x 1';

            var badge = document.getElementById('ozResultBadge');
            var msgRow = document.getElementById('ozResultMessageRow');
            var msgTxt = document.getElementById('ozResultMessageTxt');

            if (isSuccess) {
                badge.innerText = 'VALIDATED';
                badge.style.background = '#dcfce7';
                badge.style.color = '#166534';
                
                msgRow.style.background = '#dcfce7';
                msgRow.style.color = '#166534';
                msgTxt.innerText = data.message;
            } else {
                badge.innerText = data.overstay ? 'OVERSTAY' : 'REJECTED';
                badge.style.background = '#fee2e2';
                badge.style.color = '#991b1b';
                
                msgRow.style.background = '#fee2e2';
                msgRow.style.color = '#991b1b';
                msgTxt.innerText = data.message;
            }
        };

        ifsPms.updateStatus = function(message, type) {
            var bar = document.getElementById('ozScannerStatusBar');
            var txt = document.getElementById('ozStatusBarText');
            
            bar.classList.remove('is-standby', 'is-success', 'is-error');
            bar.classList.add('is-' + type);
            txt.innerText = message;

            if (type === 'success') {
                bar.style.backgroundColor = '#dcfce7';
                bar.style.borderColor = '#bbf7d0';
                bar.style.color = '#166534';
            } else if (type === 'error') {
                bar.style.backgroundColor = '#fee2e2';
                bar.style.borderColor = '#fecaca';
                bar.style.color = '#991b1b';
            } else {
                bar.style.backgroundColor = '#f1f5f9';
                bar.style.borderColor = '#cbd5e1';
                bar.style.color = '#475569';
            }
        };

        ifsPms.triggerRelay = function() {
            var pill = document.getElementById('ozTurnstileStatePill');
            var txt = document.getElementById('ozTurnstileStateTxt');
            
            pill.classList.remove('closed');
            pill.classList.add('open');
            pill.style.backgroundColor = '#dcfce7';
            pill.style.color = '#166534';
            txt.innerText = 'BARRIER OPEN';

            ifsPms.updateStatus('Access Granted. Gate Open for 4s.', 'success');

            // Simulate relay closure after 4s
            setTimeout(function() {
                pill.classList.remove('open');
                pill.classList.add('closed');
                pill.style.backgroundColor = '#fee2e2';
                pill.style.color = '#991b1b';
                txt.innerText = 'BARRIER LOCKED';
                
                ifsPms.resetScannerConsole();
            }, 4000);
        };

        ifsPms.manualPulseRelay = function() {
            if(confirm('<?php esc_html_e( 'Manually override and open turnstile barrier?', 'swimming-pool-manager' ); ?>')) {
                ifsPms.triggerRelay();
                ifsPms.addToLogStream({
                    ticket_code: 'MANUAL_OVERRIDE',
                    customer_name: '<?php echo esc_js($current_operator); ?>',
                    message: 'Manual gate override triggered by operator.'
                }, true);
            }
        };

        ifsPms.resetScannerConsole = function() {
            ifsPms.updateStatus('Terminal Standby • Present pass to scanner', 'standby');
            document.getElementById('ozResultPatron').innerText = 'Awaiting Pass Scan';
            document.getElementById('ozResultBadge').innerText = 'Standby';
            document.getElementById('ozResultBadge').style.background = '#f1f5f9';
            document.getElementById('ozResultBadge').style.color = '#64748b';
            
            document.getElementById('ozResultCardCustomerName').innerText = '—';
            document.getElementById('ozResultCardCustomerPhone').innerText = '—';
            document.getElementById('ozResultCode').innerText = '—';
            document.getElementById('ozResultGuestTypeTxt').innerText = '—';
            document.getElementById('ozResultRoomWrap').style.display = 'none';
            document.getElementById('ozResultDuration').innerText = '—';
            document.getElementById('ozResultValidUntil').innerText = '—';
            document.getElementById('ozResultAmount').innerText = '—';
            document.getElementById('ozResultPayment').innerText = '—';
            document.getElementById('ozResultSoldBy').innerText = '—';
            document.getElementById('ozResultPackage').innerText = 'No active scan telemetry loaded.';
            
            var msgRow = document.getElementById('ozResultMessageRow');
            msgRow.style.background = '#f1f5f9';
            msgRow.style.color = '#64748b';
            document.getElementById('ozResultMessageTxt').innerText = 'Ready to authorize entry gates.';
            
            if(inputElem) {
                inputElem.value = '';
                inputElem.focus();
            }
        };

        ifsPms.addToLogStream = function(data, isSuccess) {
            var container = document.getElementById('ozScanSessionLogContainer');
            var noHistoryMsg = document.getElementById('ozScanNoHistory');
            if (noHistoryMsg) {
                noHistoryMsg.remove();
            }

            var time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            var statusColor = isSuccess ? '#10b981' : '#ef4444';
            var icon = isSuccess ? 'dashicons-yes' : 'dashicons-no';
            
            var logItem = document.createElement('div');
            logItem.style.cssText = 'display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 8px; border-left: 4px solid ' + statusColor + ';';
            
            var innerHtml = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="color: ${statusColor};"><span class="dashicons ${icon}"></span></div>
                    <div>
                        <div style="font-weight: 700; color: #0f172a; font-size: 13px;">${data.customer_name || 'Guest'}</div>
                        <div class="ifs-pms-mono" style="font-size: 11px; color: #64748b; margin-top: 2px;">${data.ticket_code}</div>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; font-weight: 700; color: ${statusColor}; text-transform: uppercase;">${isSuccess ? 'Admitted' : 'Denied'}</div>
                    <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">${time}</div>
                </div>
            `;
            
            logItem.innerHTML = innerHtml;
            container.insertBefore(logItem, container.firstChild);
            
            // Keep only latest 10
            if (container.children.length > 10) {
                container.removeChild(container.lastChild);
            }
        };

        // Stub for WebCam Integration
        ifsPms.toggleCamera = function() {
            alert('<?php esc_html_e( 'Webcam scanning requires HTTPS and a supported browser API (like html5-qrcode). This is a placeholder for the integration.', 'swimming-pool-manager' ); ?>');
        };

    })();
</script>