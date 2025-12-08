<?php
ob_start();
?>
<style>
.attendance-toggle-container {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Toggle Switch Style - Professional */
.attendance-toggle {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 32px;
}

.attendance-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.attendance-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ffffff;
    border: 2px solid #e5e7eb;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 32px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

.attendance-toggle-slider:before {
    position: absolute;
    content: "";
    height: 24px;
    width: 24px;
    left: 3px;
    bottom: 3px;
    background-color: #d1d5db;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

/* ON State - Green (Có mặt) */
.attendance-toggle input:checked + .attendance-toggle-slider {
    background-color: #22c55e;
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15), inset 0 1px 3px rgba(0,0,0,0.1);
}

.attendance-toggle input:checked + .attendance-toggle-slider:before {
    transform: translateX(28px);
    background-color: white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.25);
}

/* OFF State - White (Vắng mặt) */
.attendance-toggle input:not(:checked) + .attendance-toggle-slider {
    background-color: #ffffff;
    border-color: #e5e7eb;
}

.attendance-toggle:hover .attendance-toggle-slider {
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.15);
}

.attendance-toggle:hover input:checked + .attendance-toggle-slider {
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2), inset 0 1px 3px rgba(0,0,0,0.1);
}

.attendance-toggle:hover input:not(:checked) + .attendance-toggle-slider {
    border-color: #d1d5db;
}

.attendance-toggle input:focus + .attendance-toggle-slider {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}


.customer-row {
    transition: all 0.3s ease;
}

.customer-row:hover {
    background-color: #f8fafc;
}

.stats-badge {
    padding: 0.75rem 1.25rem;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stats-badge i {
    font-size: 1.25rem;
}

/* Header Styles */
.attendance-header {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    padding: 1.75rem 2rem;
    border: none;
}

.attendance-header-title {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.5px;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.attendance-header-title i {
    font-size: 1.75rem;
    opacity: 0.95;
}

.attendance-header-info {
    font-size: 0.95rem;
    opacity: 0.95;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.attendance-header-info i {
    font-size: 1.1rem;
    opacity: 0.9;
}

.attendance-header-btn {
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.625rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.attendance-header-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    text-decoration: none;
}

.attendance-header-btn i {
    font-size: 1rem;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header attendance-header text-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="attendance-header-title mb-0">
                            <i class="bi bi-check2-square"></i>
                            <span>Điểm danh - <?= htmlspecialchars($booking['tour_name'] ?? 'Tour') ?></span>
                        </h3>
                        <?php if ($booking): ?>
                            <div class="attendance-header-info">
                                <i class="bi bi-calendar-event"></i>
                                <span>
                                    <strong>Khởi hành:</strong> <?= date('d/m/Y', strtotime($booking['start_date'])) ?>
                                    <?php if ($booking['end_date']): ?>
                                        <span class="mx-2">•</span>
                                        <strong>Kết thúc:</strong> <?= date('d/m/Y', strtotime($booking['end_date'])) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <a href="<?= BASE_URL ?>guides/attendance" class="attendance-header-btn">
                        <i class="bi bi-arrow-left"></i>
                        <span>Quay lại</span>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_GET['success']) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($customers)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Booking này chưa có khách hàng nào.
                    </div>
                <?php else: ?>
                    <form action="<?= BASE_URL ?>guides/save-attendance" method="post" id="attendanceForm">
                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                        
                        <!-- Hidden inputs để gửi tất cả trạng thái -->
                        <?php foreach ($customers as $customer): ?>
                            <input type="hidden" name="attendance[<?= $customer['id'] ?>]" id="hidden_<?= $customer['id'] ?>" 
                                   value="<?= ($customer['attendance_status'] ?? 'pending') === 'present' ? 'present' : 'absent' ?>">
                        <?php endforeach; ?>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Tên khách hàng</th>
                                        <th>Giới tính</th>
                                        <th>Số điện thoại</th>
                                        <th>Email</th>
                                        <th width="280" class="text-center">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customers as $index => $customer): ?>
                                        <tr class="customer-row">
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($customer['name'] ?? 'N/A') ?></strong>
                                                <?php if ($index === 0): ?>
                                                    <span class="badge bg-primary ms-2">Người đại diện</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $gender = $customer['gender'] ?? '';
                                                if ($gender === 'male') {
                                                    echo '<span class="badge bg-primary">Nam</span>';
                                                } elseif ($gender === 'female') {
                                                    echo '<span class="badge bg-danger">Nữ</span>';
                                                } elseif ($gender === 'other') {
                                                    echo '<span class="badge bg-info">Khác</span>';
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($customer['phone']): ?>
                                                    <i class="bi bi-telephone me-1"></i>
                                                    <?= htmlspecialchars($customer['phone']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($customer['email']): ?>
                                                    <i class="bi bi-envelope me-1"></i>
                                                    <?= htmlspecialchars($customer['email']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="attendance-toggle-container">
                                                    <label class="attendance-toggle">
                                                        <input type="checkbox" 
                                                               id="attendance_<?= $customer['id'] ?>" 
                                                               <?= ($customer['attendance_status'] ?? 'pending') === 'present' ? 'checked' : '' ?>
                                                               onchange="updateAttendanceStatus(<?= $customer['id'] ?>, this.checked)">
                                                        <span class="attendance-toggle-slider"></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <div class="d-flex gap-3">
                                <div class="stats-badge bg-success text-white">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div>
                                        <small class="d-block opacity-75" style="font-size: 0.75rem;">Có mặt</small>
                                        <strong style="font-size: 1.5rem;" id="presentCount">
                                            <?= count(array_filter($customers, fn($c) => ($c['attendance_status'] ?? 'pending') === 'present')) ?>
                                        </strong>
                                    </div>
                                </div>
                                <div class="stats-badge bg-danger text-white">
                                    <i class="bi bi-x-circle-fill"></i>
                                    <div>
                                        <small class="d-block opacity-75" style="font-size: 0.75rem;">Vắng mặt</small>
                                        <strong style="font-size: 1.5rem;" id="absentCount">
                                            <?= count(array_filter($customers, fn($c) => ($c['attendance_status'] ?? 'pending') === 'absent')) ?>
                                        </strong>
                                    </div>
                                </div>
                                <div class="stats-badge bg-secondary text-white">
                                    <i class="bi bi-clock-fill"></i>
                                    <div>
                                        <small class="d-block opacity-75" style="font-size: 0.75rem;">Chưa điểm danh</small>
                                        <strong style="font-size: 1.5rem;" id="pendingCount">
                                            <?= count(array_filter($customers, fn($c) => ($c['attendance_status'] ?? 'pending') === 'pending')) ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-success btn-lg shadow-sm" style="padding: 0.75rem 2rem; border-radius: 10px; font-weight: 600;">
                                    <i class="bi bi-save me-2"></i>Lưu điểm danh
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function updateAttendanceStatus(customerId, isPresent) {
    const hiddenInput = document.getElementById('hidden_' + customerId);
    
    if (isPresent) {
        hiddenInput.value = 'present';
    } else {
        hiddenInput.value = 'absent';
    }
    
    updateCounts();
}

// Cập nhật số lượng khi chọn toggle
document.querySelectorAll('input[type="checkbox"][id^="attendance_"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const customerId = this.id.replace('attendance_', '');
        updateAttendanceStatus(customerId, this.checked);
    });
});

function updateCounts() {
    let presentCount = 0;
    let absentCount = 0;
    let pendingCount = 0;
    
    document.querySelectorAll('input[type="checkbox"][id^="attendance_"]').forEach(checkbox => {
        if (checkbox.checked) {
            presentCount++;
        } else {
            absentCount++;
        }
    });
    
    const totalCustomers = <?= count($customers) ?>;
    pendingCount = totalCustomers - presentCount - absentCount;
    
    document.getElementById('presentCount').textContent = presentCount;
    document.getElementById('absentCount').textContent = absentCount;
    document.getElementById('pendingCount').textContent = pendingCount;
}

// Khởi tạo counts khi trang load
updateCounts();
</script>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Điểm danh',
    'pageTitle' => 'Điểm danh khách hàng',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Điểm danh', 'url' => BASE_URL . 'guides/attendance'],
        ['label' => 'Chi tiết', 'url' => '', 'active' => true],
    ],
]);
?>

