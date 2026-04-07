<?php
session_start();

// กั้นประตู: ถ้ายังไม่ได้ Login ให้ถีบส่งไปหน้า login.php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่าบัญชี - EA Control Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f8f9fa; }
        .card-settings { 
            transition: 0.3s; 
            border-left: 4px solid #0d6efd;
        }
        .card-settings:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 20px rgba(0,0,0,0.1); 
        }
        .status-badge { 
            font-size: 0.75rem; 
            padding: 4px 10px; 
        }
        .vps-select, .ea-input {
            border: 2px solid #e9ecef;
            transition: border-color 0.3s;
        }
        .vps-select:focus, .ea-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
        }
        .save-indicator {
            opacity: 0;
            transition: opacity 0.3s;
        }
        .save-indicator.show {
            opacity: 1;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a href="dashboard.php" class="navbar-brand mb-0 h1">
            <i class="bi bi-robot"></i> EA Commander
        </a>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="?logout" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-gear-fill text-primary"></i> ตั้งค่าบัญชี</h2>
            <p class="text-muted mb-0">กำหนด VPS และ EA Name ให้แต่ละบัญชี</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- ปุ่มเพิ่ม VPS -->
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#vpsModal">
                <i class="bi bi-plus-circle"></i> จัดการ VPS
            </button>
            <!-- แสดงสถานะบันทึก -->
            <span id="saveIndicator" class="save-indicator text-success fw-semibold">
                <i class="bi bi-check-circle-fill"></i> บันทึกแล้ว
            </span>
        </div>
    </div>

    <!-- ตารางบัญชี -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">บัญชี</th>
                            <th class="py-3">Broker</th>
                            <th class="py-3">สถานะ</th>
                            <th class="py-3" style="min-width: 110px;">ประเภท</th>
                            <th class="py-3" style="min-width: 110px;">บัญชี</th>
                            <th class="py-3" style="min-width: 200px;">VPS</th>
                            <th class="py-3" style="min-width: 220px;">EA Name</th>
                            <th class="py-3" style="min-width: 150px;">เงินทุนเริ่มต้น</th>
                            <th class="py-3 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="accountsTable">
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 mb-0 text-muted">กำลังโหลดข้อมูล...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal จัดการ VPS -->
<div class="modal fade" id="vpsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-server"></i> จัดการ VPS</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- ฟอร์มเพิ่ม VPS ใหม่ -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">เพิ่ม VPS ใหม่</label>
                    <div class="input-group">
                        <input type="text" id="newVpsName" class="form-control" placeholder="เช่น VPS-1, Singapore, USA-East">
                        <button class="btn btn-primary" onclick="addVps()">
                            <i class="bi bi-plus"></i> เพิ่ม
                        </button>
                    </div>
                </div>
                
                <!-- รายการ VPS ที่มี -->
                <label class="form-label fw-semibold">รายการ VPS ทั้งหมด</label>
                <div id="vpsList" class="list-group">
                    <div class="text-center text-muted py-3">
                        <div class="spinner-border spinner-border-sm" role="status"></div> กำลังโหลด...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let vpsList = [];
    let accounts = [];

    // โหลดข้อมูลตอนเปิดหน้า
    document.addEventListener('DOMContentLoaded', function() {
        fetchVpsList();
        fetchAccounts();
    });

    // ดึงรายการ VPS
    async function fetchVpsList() {
        try {
            const res = await fetch('api.php?action=get_vps_list');
            vpsList = await res.json();
            renderVpsList();
        } catch (e) {
            console.error('Error fetching VPS list:', e);
        }
    }

    // ดึงข้อมูลบัญชี
    async function fetchAccounts() {
        try {
            const res = await fetch('api.php?action=fetch_data_with_settings');
            accounts = await res.json();
            renderAccountsTable();
        } catch (e) {
            console.error('Error fetching accounts:', e);
        }
    }

    // แสดงรายการ VPS ใน Modal
    function renderVpsList() {
        const container = document.getElementById('vpsList');
        
        if (vpsList.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-3">ยังไม่มี VPS (เพิ่มด้านบน)</div>';
            return;
        }

        let html = '';
        vpsList.forEach(vps => {
            html += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="bi bi-server me-2 text-primary"></i> ${vps.vps_name}</span>
                <button onclick="deleteVps(${vps.id}, '${vps.vps_name}')" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`;
        });
        container.innerHTML = html;
    }

    // แสดงตารางบัญชี
    function renderAccountsTable() {
        const tbody = document.getElementById('accountsTable');
        
        if (accounts.length === 0) {
            tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mt-2 mb-0">ยังไม่มีบัญชีเชื่อมต่อ</p>
                </td>
            </tr>`;
            return;
        }

        // ฟังก์ชันเช็คว่าบัญชียังไม่ได้กรอกข้อมูลครบ
        const isIncomplete = (acc) => {
            return !acc.vps_id || !acc.ea_name || !acc.mt_type || !acc.account_mode;
        };

        // เรียงลำดับ: 1) ยังไม่ได้กรอกข้อมูลก่อน 2) จัดกลุ่มตาม VPS 3) เรียงตามหมายเลขบัญชี
        const sortedAccounts = [...accounts].sort((a, b) => {
            const aIncomplete = isIncomplete(a);
            const bIncomplete = isIncomplete(b);
            
            // ยังไม่ได้กรอกข้อมูลขึ้นก่อน
            if (aIncomplete && !bIncomplete) return -1;
            if (!aIncomplete && bIncomplete) return 1;
            
            // ถ้าทั้งคู่ยังไม่ได้กรอก หรือทั้งคู่กรอกแล้ว -> เรียงตาม VPS
            const aVpsName = getVpsName(a.vps_id) || 'zzz_ไม่ได้กำหนด';
            const bVpsName = getVpsName(b.vps_id) || 'zzz_ไม่ได้กำหนด';
            
            if (aVpsName !== bVpsName) {
                return aVpsName.localeCompare(bVpsName, 'th');
            }
            
            // ภายใน VPS เดียวกัน -> เรียงตามหมายเลขบัญชี
            return parseInt(a.account_number) - parseInt(b.account_number);
        });

        // ฟังก์ชันหาชื่อ VPS จาก ID
        function getVpsName(vpsId) {
            if (!vpsId) return null;
            const vps = vpsList.find(v => v.id == vpsId);
            return vps ? vps.vps_name : null;
        }

        let html = '';
        let lastVpsGroup = null; // ติดตามกลุ่ม VPS ก่อนหน้า
        let lastIncompleteStatus = null; // ติดตามสถานะยังไม่กรอก

        sortedAccounts.forEach(acc => {
            const incomplete = isIncomplete(acc);
            const currentVpsName = getVpsName(acc.vps_id) || 'ไม่ได้กำหนด VPS';
            const groupKey = incomplete ? '_incomplete' : currentVpsName;

            // แสดง Header แยกกลุ่ม
            if (groupKey !== lastVpsGroup) {
                if (incomplete && lastIncompleteStatus !== true) {
                    html += `
                    <tr class="table-warning">
                        <td colspan="8" class="px-4 py-2 fw-bold">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                            รอกรอกข้อมูล (${sortedAccounts.filter(a => isIncomplete(a)).length} บัญชี)
                        </td>
                    </tr>`;
                    lastIncompleteStatus = true;
                } else if (!incomplete && lastVpsGroup !== currentVpsName) {
                    html += `
                    <tr class="table-primary">
                        <td colspan="8" class="px-4 py-2 fw-bold">
                            <i class="bi bi-server text-primary me-2"></i>
                            ${currentVpsName}
                        </td>
                    </tr>`;
                }
                lastVpsGroup = groupKey;
            }
            const statusClass = acc.is_online ? 'bg-success' : 'bg-secondary';
            const statusText = acc.is_online ? 'Online' : 'Offline';
            
            // สร้าง dropdown VPS
            let vpsOptions = '<option value="">-- เลือก VPS --</option>';
            vpsList.forEach(vps => {
                const selected = (acc.vps_id == vps.id) ? 'selected' : '';
                vpsOptions += `<option value="${vps.id}" ${selected}>${vps.vps_name}</option>`;
            });

            html += `
            <tr data-account="${acc.account_number}">
                <td class="px-4">
                    <span class="fw-bold text-primary">#${acc.account_number}</span>
                </td>
                <td>
                    <span>${acc.broker || '-'}</span>
                </td>
                <td>
                    <span class="badge ${statusClass} status-badge">${statusText}</span>
                </td>
                <td>
                    <select class="form-select form-select-sm" 
                            id="mt_type_${acc.account_number}" 
                            onchange="markUnsaved(${acc.account_number})">
                        <option value="" ${!acc.mt_type ? 'selected' : ''}>-- เลือก --</option>
                        <option value="MT4" ${acc.mt_type === 'MT4' ? 'selected' : ''}>MT4</option>
                        <option value="MT5" ${acc.mt_type === 'MT5' ? 'selected' : ''}>MT5</option>
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm" 
                            id="account_mode_${acc.account_number}" 
                            onchange="markUnsaved(${acc.account_number})">
                        <option value="" ${!acc.account_mode ? 'selected' : ''}>-- เลือก --</option>
                        <option value="Demo" ${acc.account_mode === 'Demo' ? 'selected' : ''}>Demo</option>
                        <option value="Real" ${acc.account_mode === 'Real' ? 'selected' : ''}>Real</option>
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm vps-select" 
                            id="vps_${acc.account_number}" 
                            onchange="markUnsaved(${acc.account_number})">
                        ${vpsOptions}
                    </select>
                </td>
                <td>
                    <input type="text" 
                           class="form-control form-control-sm ea-input" 
                           id="ea_${acc.account_number}" 
                           value="${acc.ea_name || ''}" 
                           placeholder="ชื่อ EA ที่รัน..."
                           onchange="markUnsaved(${acc.account_number})">
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">$</span>
                        <input type="number" 
                               class="form-control form-control-sm" 
                               id="initial_capital_${acc.account_number}" 
                               value="${acc.initial_capital || ''}" 
                               placeholder="0.00"
                               step="0.01"
                               min="0"
                               onchange="markUnsaved(${acc.account_number})">
                    </div>
                </td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-primary" 
                                id="saveBtn_${acc.account_number}"
                                onclick="saveAccountSettings(${acc.account_number})">
                            <i class="bi bi-check2"></i> บันทึก
                        </button>
                        <button class="btn btn-sm btn-outline-danger" 
                                onclick="deleteAccount(${acc.account_number}, '${acc.account_name || acc.account_number}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    // ทำสีปุ่มบันทึกให้รู้ว่ามีการเปลี่ยนแปลง
    function markUnsaved(accNum) {
        const btn = document.getElementById(`saveBtn_${accNum}`);
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-warning');
        btn.innerHTML = '<i class="bi bi-pencil"></i> บันทึก';
    }

    // บันทึกการตั้งค่าบัญชี
    async function saveAccountSettings(accNum) {
        const vpsId = document.getElementById(`vps_${accNum}`).value;
        const eaName = document.getElementById(`ea_${accNum}`).value;
        const mtType = document.getElementById(`mt_type_${accNum}`).value;
        const accountMode = document.getElementById(`account_mode_${accNum}`).value;
        const initialCapital = document.getElementById(`initial_capital_${accNum}`).value;
        const btn = document.getElementById(`saveBtn_${accNum}`);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const formData = new FormData();
            formData.append('account_number', accNum);
            formData.append('vps_id', vpsId);
            formData.append('ea_name', eaName);
            formData.append('mt_type', mtType);
            formData.append('account_mode', accountMode);
            formData.append('initial_capital', initialCapital);

            const res = await fetch('api.php?action=save_account_settings', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.status === 'success') {
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-success');
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                
                // แสดง indicator
                showSaveIndicator();

                setTimeout(() => {
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-primary');
                    btn.innerHTML = '<i class="bi bi-check2"></i> บันทึก';
                    btn.disabled = false;
                }, 1500);
            } else {
                throw new Error(data.message || 'บันทึกไม่สำเร็จ');
            }
        } catch (e) {
            btn.classList.add('btn-danger');
            btn.innerHTML = '<i class="bi bi-x"></i> ผิดพลาด';
            btn.disabled = false;
            console.error(e);
        }
    }

    // แสดง indicator ว่าบันทึกแล้ว
    function showSaveIndicator() {
        const indicator = document.getElementById('saveIndicator');
        indicator.classList.add('show');
        setTimeout(() => indicator.classList.remove('show'), 2000);
    }

    // เพิ่ม VPS ใหม่
    async function addVps() {
        const input = document.getElementById('newVpsName');
        const name = input.value.trim();
        
        if (!name) {
            Swal.fire('แจ้งเตือน', 'กรุณาใส่ชื่อ VPS', 'warning');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('vps_name', name);

            const res = await fetch('api.php?action=add_vps', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.status === 'success') {
                input.value = '';
                fetchVpsList();
                fetchAccounts(); // refresh dropdown
                Swal.fire('สำเร็จ!', `เพิ่ม VPS "${name}" แล้ว`, 'success');
            } else {
                throw new Error(data.message);
            }
        } catch (e) {
            Swal.fire('ผิดพลาด', e.message, 'error');
        }
    }

    // ลบ VPS
    async function deleteVps(id, name) {
        const result = await Swal.fire({
            title: 'ยืนยันการลบ?',
            text: `ลบ VPS "${name}" ออกจากระบบ`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'ใช่, ลบเลย',
            cancelButtonText: 'ยกเลิก'
        });

        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('vps_id', id);

                const res = await fetch('api.php?action=delete_vps', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    fetchVpsList();
                    fetchAccounts();
                    Swal.fire('ลบแล้ว!', `VPS "${name}" ถูกลบออกแล้ว`, 'success');
                } else {
                    throw new Error(data.message);
                }
            } catch (e) {
                Swal.fire('ผิดพลาด', e.message, 'error');
            }
        }
    }

    // ลบบัญชี
    async function deleteAccount(accNum, accName) {
        const result = await Swal.fire({
            title: 'ยืนยันการลบบัญชี?',
            html: `ลบบัญชี <strong>#${accNum}</strong> (${accName}) ออกจากระบบ<br><small class="text-danger">การลบนี้ไม่สามารถกู้คืนได้!</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="bi bi-trash"></i> ใช่, ลบเลย',
            cancelButtonText: 'ยกเลิก'
        });

        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('account_number', accNum);

                const res = await fetch('api.php?action=delete_account', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    // ลบแถวออกจากตาราง
                    const row = document.querySelector(`tr[data-account="${accNum}"]`);
                    if (row) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }
                    Swal.fire('ลบแล้ว!', `บัญชี #${accNum} ถูกลบออกจากระบบแล้ว`, 'success');
                } else {
                    throw new Error(data.message);
                }
            } catch (e) {
                Swal.fire('ผิดพลาด', e.message, 'error');
            }
        }
    }
</script>

</body>
</html>
