<?php include 'blocks/menu.php'; ?>
<?php
$inAgg = $inAgg ?? [];
$outAgg = $outAgg ?? [];
$deadline = $deadline ?? [];

$inDraftRows = (int)($inAgg['draft_rows'] ?? 0);
$outDraftRows = (int)($outAgg['draft_rows'] ?? 0);
$totalProducts = (int)($products['total_products'] ?? 0);
$linkedProducts = (int)($products['linked_products'] ?? 0);

$deadlineState = $deadline['state'] ?? 'before_quarter_close';
$deadlineStatusLabel = $deadline['status_label'] ?? 'Quarter open';
$deadlineBadgeClass = 'badge-success';
if ($deadlineState === 'before_quarter_close') {
    $deadlineBadgeClass = 'badge-info';
} elseif ($deadlineState === 'autosend_due') {
    $deadlineBadgeClass = 'badge-warning';
} elseif ($deadlineState === 'closed_window') {
    $deadlineBadgeClass = 'badge-secondary';
}
?>
<style>
.vamreg-send-theme {
    --vamreg-bg: #f7fafc;
    --vamreg-card: #ffffff;
    --vamreg-text: #1f2937;
    --vamreg-muted: #667085;
    --vamreg-border: #e5eaf0;
    --vamreg-accent: #0f766e;
    --vamreg-accent-2: #0ea5e9;
    --vamreg-warning: #f59e0b;
    --vamreg-danger: #dc2626;
}

.vamreg-send-theme .card,
.vamreg-send-theme .dashboard-card {
    border-radius: 14px;
    border: 1px solid var(--vamreg-border);
    background: var(--vamreg-card);
}

.vamreg-send-theme .dashboard-card {
    padding: 1rem;
    height: 100%;
}

.vamreg-send-theme .deadline-list {
    margin: 0;
    padding: 0;
    list-style: none;
}

.vamreg-send-theme .deadline-item {
    display: grid;
    grid-template-columns: 120px 16px 1fr;
    gap: .6rem;
    padding-bottom: .75rem;
}

.vamreg-send-theme .deadline-when {
    font-size: .76rem;
    color: var(--vamreg-muted);
    padding-top: .05rem;
}

.vamreg-send-theme .deadline-marker {
    position: relative;
    display: flex;
    justify-content: center;
}

.vamreg-send-theme .deadline-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-top: .2rem;
    z-index: 2;
}

.vamreg-send-theme .deadline-line {
    position: absolute;
    top: .85rem;
    bottom: -.15rem;
    width: 2px;
    background: #dbe5ef;
}

.vamreg-send-theme .deadline-item:last-child .deadline-line {
    display: none;
}

.vamreg-send-theme .deadline-title {
    font-weight: 600;
    line-height: 1.2;
    margin-bottom: .1rem;
}

.vamreg-send-theme .deadline-note {
    font-size: .78rem;
    color: var(--vamreg-muted);
}

.vamreg-send-theme .table-sm td,
.vamreg-send-theme .table-sm th {
    padding: .45rem;
}

.vamreg-send-theme .soft-panel {
    background: var(--vamreg-bg);
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    padding: .75rem;
}

.vamreg-send-theme .btn-loading {
    pointer-events: none;
}
</style>
<div class="card shadow mb-4">
    <div class="card-header d-flex flex-row align-items-center justify-content-between">
        <div>Vamreg / Pre-send dashboard for Q<?= (int)$quarter; ?> <?= (int)$year; ?></div>
        <div>
            <a href="<?= base_url("vamreg/index/$prevY/$prevQ") ?>"
            class="btn btn-outline-success btn-sm">
                <i class="fas fa-angle-double-left fa-fw"></i>
                Q<?= $prevQ ?> <?= $prevY ?>
            </a>

            <span class="mx-2 font-weight-bold">
                Q<?= $quarter ?> <?= $year ?>
            </span>

            <?php if (!$isCurrentQuarter): ?>
                <a href="<?= base_url("vamreg/index/$nextY/$nextQ") ?>"
                class="btn btn-outline-success btn-sm">
                    Q<?= $nextQ ?> <?= $nextY ?>
                    <i class="fas fa-angle-double-right fa-fw"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
	<div class="card-body vamreg-send-theme">
        <div id="vamreg-send-status">
            <?php include 'blocks/vamreg_status.php'; ?>
        </div>
        <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <small class="text-muted">
                    Reporting window: <?= htmlspecialchars($startDate ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    to
                    <?= htmlspecialchars($endDate ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </small>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">Send IN declarations</h6>
                            <small class="text-muted">Posts quarter draft lines from `vamreg_in_buffer`.</small>
                        </div>
                        <span class="badge badge-primary"><?= number_format($inDraftRows); ?> draft</span>
                    </div>
                    <?php if ($inDraftRows > 0): ?>
                        <button
                            type="button"
                            class="btn btn-primary btn-sm js-vamreg-send"
                            data-url="<?= base_url("vamreg/post_all/$year/$quarter"); ?>"
                            data-default-icon="fa-paper-plane"
                        >
                            <i class="fa-solid fa-paper-plane mr-1"></i><span class="js-btn-label">Post IN drafts</span>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-success btn-sm" disabled>
                            <i class="fa-solid fa-circle-check mr-1"></i> IN up to date
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">Send OUT declarations</h6>
                            <small class="text-muted">Posts quarter draft lines from `vamreg_out_buffer`.</small>
                        </div>
                        <span class="badge badge-success"><?= number_format($outDraftRows); ?> draft</span>
                    </div>
                    <?php if ($outDraftRows > 0): ?>
                        <button
                            type="button"
                            class="btn btn-success btn-sm js-vamreg-send"
                            data-url="<?= base_url("vamreg/post_all_out/$year/$quarter"); ?>"
                            data-default-icon="fa-paper-plane"
                        >
                            <i class="fa-solid fa-paper-plane mr-1"></i><span class="js-btn-label">Post OUT drafts</span>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-success btn-sm" disabled>
                            <i class="fa-solid fa-circle-check mr-1"></i> OUT up to date
                        </button>
                    <?php endif; ?>
        </div>
    </div>
</div>
        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Deadlines</h6>
                        <span class="badge <?= $deadlineBadgeClass; ?>">
                            <?= htmlspecialchars($deadlineStatusLabel, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
                    <ul class="deadline-list">
                        <li class="deadline-item">
                            <div class="deadline-when"><?= !empty($deadline['quarter_end']) ? htmlspecialchars(date('Y-m-d', strtotime($deadline['quarter_end'])), ENT_QUOTES, 'UTF-8') : '-'; ?></div>
                            <div class="deadline-marker">
                                <span class="deadline-dot" style="background:#7da9f7;"></span>
                                <span class="deadline-line"></span>
                            </div>
                            <div>
                                <div class="deadline-title">Quarter closes</div>
                            </div>
                        </li>
                        <li class="deadline-item">
                            <div class="deadline-when"><?= !empty($deadline['quarter_end']) ? htmlspecialchars(date('Y-m-d', strtotime($deadline['quarter_end'])), ENT_QUOTES, 'UTF-8') : '-'; ?></div>
                            <div class="deadline-marker">
                                <span class="deadline-dot" style="background:#6fbf73;"></span>
                                <span class="deadline-line"></span>
                            </div>
                            <div>
                                <div class="deadline-title">Edit window</div>
                                <div class="deadline-note">Insert / update / delete allowed.</div>
                            </div>
                        </li>
                        <li class="deadline-item">
                            <div class="deadline-when"><?= !empty($deadline['auto_send_at']) ? htmlspecialchars(date('Y-m-d', strtotime($deadline['auto_send_at'])), ENT_QUOTES, 'UTF-8') : '-'; ?></div>
                            <div class="deadline-marker">
                                <span class="deadline-dot" style="background:#9c88ff;"></span>
                                <span class="deadline-line"></span>
                            </div>
                            <div>
                                <div class="deadline-title">Auto send</div>
                                <div class="deadline-note">Quarter end + <?= (int)($deadline['auto_send_months'] ?? 1); ?> month(s), configurable.</div>
                            </div>
                        </li>
                        <li class="deadline-item">
                            <div class="deadline-when"><?= !empty($deadline['edit_window_closes_at']) ? htmlspecialchars(date('Y-m-d', strtotime($deadline['edit_window_closes_at'])), ENT_QUOTES, 'UTF-8') : '-'; ?></div>
                            <div class="deadline-marker">
                                <span class="deadline-dot" style="background:#f08a8a;"></span>
                            </div>
                            <div>
                                <div class="deadline-title">Edit window closes</div>
                                <div class="deadline-note">At quarter end + 1 month + 14 days.</div>
                            </div>
                        </li>
                    </ul>
                    <div class="soft-panel small">
                        <?php if ($deadlineState === 'before_quarter_close'): ?>
                            <strong>Current status:</strong> quarter is still open.
                        <?php elseif ($deadlineState === 'edit_window'): ?>
                            <strong>Current status:</strong> edit window active. Auto send in <?= (int)($deadline['days_to_auto_send'] ?? 0); ?> day(s).
                        <?php elseif ($deadlineState === 'autosend_due'): ?>
                            <strong>Current status:</strong> auto-send point reached. Edit window closes in <?= (int)($deadline['days_to_edit_window_close'] ?? 0); ?> day(s).
                        <?php else: ?>
                            <strong>Current status:</strong> edit window is closed for this quarter.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Product linking</h6>
                    </div>
                    <div class="soft-panel mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Linked products</span>
                            <span class="badge badge-success"><?= number_format($linkedProducts); ?></span>
                        </div>
                    </div>
                    <div class="soft-panel">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total products</span>
                            <span class="badge badge-primary"><?= number_format($totalProducts); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

	</div>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
	$("#vamreg-send").addClass('active');

	 var $statusTarget = $('#vamreg-send-status');

    function setButtonLoading($button, loading) {
        var $icon = $button.find('i').first();
        var $label = $button.find('.js-btn-label').first();

        if (!$icon.length) {
            return;
        }

        if (loading) {
            if ($label.length) {
                $button.data('originalLabel', $.trim($label.text()));
                $label.text('Loading...');
            }
            $button.prop('disabled', true).addClass('btn-loading');
            $icon.attr('class', 'fa-solid fa-spinner fa-spin mr-1');
            return;
        }

        $button.prop('disabled', false).removeClass('btn-loading');
        $icon.attr('class', 'fa-solid ' + ($button.data('defaultIcon') || 'fa-paper-plane') + ' mr-1');
        if ($label.length) {
            $label.text($button.data('originalLabel') || 'Post');
        }
    }

    $('.js-vamreg-send').on('click', function () {
        var $button = $(this);
        var url = $button.data('url');
        if (!url) {
            return;
        }

        setButtonLoading($button, true);

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .done(function (payload) {
                if (payload && payload.status_html && $statusTarget.length) {
                    $statusTarget.html(payload.status_html);
                }
            })
            .fail(function () {
                if ($statusTarget.length) {
                    $statusTarget.html('<div class="alert alert-danger" role="alert"><i class="fa-solid fa-triangle-exclamation"></i> Request failed while sending declarations. Please try again.</div>');
                }
            })
            .always(function () {
                setButtonLoading($button, false);
            });
    });

});
</script>


<a href="<?php echo base_url('vamreg/reset'); ?>" class="btn btn-danger btn-sm"> Reset</a>
