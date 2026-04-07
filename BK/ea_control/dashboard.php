<?php
session_start();

// ดักจับการ Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("location: login.php");
    exit;
}

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
    <title>EA Control Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f8f9fa; }
        .card-acc { transition: 0.3s; }
        .card-acc:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .status-dot { height: 10px; width: 10px; background-color: #bbb; border-radius: 50%; display: inline-block; }
        .online { background-color: #28a745; box-shadow: 0 0 5px #28a745; }
        .offline { background-color: #dc3545; }
        
        /* VPS Group Styling */
        .vps-group { margin-bottom: 2rem; }
        .vps-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .vps-header.no-vps {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }
        .vps-header h5 { margin: 0; font-weight: 600; }
        .vps-header .badge { font-size: 0.85rem; }
        
        /* EA Badge */
        .ea-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        /* MT Type Badge */
        .mt-badge {
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            margin-left: 4px;
        }
        .mt-badge.mt4 {
            background: #4ade80;
            color: #166534;
        }
        .mt-badge.mt5 {
            background: #60a5fa;
            color: #1e40af;
        }
        
        /* Account Mode Badge */
        .mode-badge {
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            margin-left: 4px;
        }
        .mode-badge.demo {
            background: #fef3c7;
            color: #d97706;
        }
        .mode-badge.real {
            background: #d1fae5;
            color: #059669;
        }
        
        /* Star Icon */
        .star-icon {
            font-size: 1.1rem;
            color: #cbd5e1;
            margin-left: 8px;
        }
        
        .star-icon.active {
            color: #fbbf24;
            text-shadow: 0 0 8px rgba(251, 191, 36, 0.6);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-robot"></i> EA Commander</span>
        <div class="d-flex align-items-center gap-2">
            <a href="dashboard2.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-list"></i> Horizontal View
            </a>
            <a href="settings.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-gear-fill"></i> ตั้งค่า
            </a>
            <button onclick="sendCloseCommand(0)" class="btn btn-danger btn-sm">
                <i class="bi bi-exclamation-triangle-fill"></i> Panic Button
            </button>
            <a href="?logout" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</nav>

<div class="container" id="main-container">
    <div class="text-center mt-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p>กำลังเชื่อมต่อข้อมูล...</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <script>
    // ฟังก์ชันดึงข้อมูลบัญชี
    async function fetchAccounts() {
        try {
            const response = await fetch('api.php?action=fetch_data');
            const accounts = await response.json();
            renderAccounts(accounts);
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // ฟังก์ชันวาดหน้าจอ (Render HTML) - จัดกลุ่มตาม VPS
    function renderAccounts(accounts) {
        const container = document.getElementById('main-container');
        
        if (accounts.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-inbox fs-1"></i><p class="mt-2">ยังไม่มีข้อมูลบัญชีเชื่อมต่อเข้ามา</p></div>';
            return;
        }

        // จัดกลุ่มบัญชีตาม VPS
        const vpsGroups = {};
        accounts.forEach(acc => {
            const vpsKey = acc.vps_name || '__NO_VPS__';
            if (!vpsGroups[vpsKey]) {
                vpsGroups[vpsKey] = [];
            }
            vpsGroups[vpsKey].push(acc);
        });
        
        // เรียงลำดับบัญชีในแต่ละกลุ่ม VPS โดยบัญชีที่มาร์กดาวจะแสดงก่อน
        for (const vpsKey in vpsGroups) {
            vpsGroups[vpsKey].sort((a, b) => {
                // เรียงตาม is_favorite ก่อน (1 ก่อน 0)
                if (b.is_favorite !== a.is_favorite) {
                    return b.is_favorite - a.is_favorite;
                }
                // ถ้า is_favorite เท่ากัน เรียงตาม account_number
                return a.account_number - b.account_number;
            });
        }

        let html = '';
        
        // วนลูปแต่ละ VPS Group
        for (const [vpsName, accs] of Object.entries(vpsGroups)) {
            const isNoVps = vpsName === '__NO_VPS__';
            const displayName = isNoVps ? 'ยังไม่ได้กำหนด VPS' : vpsName;
            const headerClass = isNoVps ? 'vps-header no-vps' : 'vps-header';
            
            html += `
            <div class="vps-group">
                <div class="${headerClass}">
                    <h5><i class="bi bi-server me-2"></i> ${displayName}</h5>
                    <span class="badge bg-white text-dark">${accs.length} บัญชี</span>
                </div>
                <div class="row">`;

            accs.forEach(acc => {
                // คำนวณสี Profit (Floating)
                const profitClass = acc.profit >= 0 ? 'text-success' : 'text-danger';
                const profitIcon = acc.profit >= 0 ? 'bi-caret-up-fill' : 'bi-caret-down-fill';
                
                // คำนวณสี Daily Profit (กำไรวันนี้)
                const dailyProfit = parseFloat(acc.daily_profit) || 0;
                const dailyProfitClass = dailyProfit >= 0 ? 'text-success' : 'text-danger';
                const dailyProfitIcon = dailyProfit >= 0 ? 'bi-caret-up-fill' : 'bi-caret-down-fill';
                
                // สถานะ Online/Offline
                const statusClass = acc.is_online ? 'online' : 'offline';
                const statusText = acc.is_online ? 'Online' : 'Offline';
                const lastUpdate = new Date(acc.last_update).toLocaleTimeString('th-TH');
                
                // EA Name badge
                const eaBadge = acc.ea_name ? `<span class="ea-badge"><i class="bi bi-robot"></i> ${acc.ea_name}</span>` : '';
                
                // MT Type badge
                let mtBadge = '';
                if (acc.mt_type === 'MT4') {
                    mtBadge = '<span class="mt-badge mt4">MT4</span>';
                } else if (acc.mt_type === 'MT5') {
                    mtBadge = '<span class="mt-badge mt5">MT5</span>';
                }
                
                // Account Mode badge
                let modeBadge = '';
                if (acc.account_mode === 'Demo') {
                    modeBadge = '<span class="mode-badge demo">Demo</span>';
                } else if (acc.account_mode === 'Real') {
                    modeBadge = '<span class="mode-badge real">Real</span>';
                }
                
                // คำนวณกำไรทั้งหมด (Total Profit = Balance - Initial Capital)
                const initialCapital = parseFloat(acc.initial_capital) || 0;
                const balance = parseFloat(acc.balance) || 0;
                const totalProfit = initialCapital > 0 ? balance - initialCapital : null;
                const totalProfitClass = totalProfit !== null ? (totalProfit >= 0 ? 'text-success' : 'text-danger') : '';
                const totalProfitIcon = totalProfit !== null ? (totalProfit >= 0 ? 'bi-caret-up-fill' : 'bi-caret-down-fill') : '';

                html += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card card-acc border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold">
                                    ${acc.account_number} ${mtBadge} ${modeBadge}
                                    <span class="star-icon ${acc.is_favorite ? 'active' : ''}" title="${acc.is_favorite ? 'บัญชีสำคัญ' : 'บัญชีทั่วไป'}">
                                        ${acc.is_favorite ? '★' : '☆'}
                                    </span>
                                </h5>
                                ${eaBadge}
                            </div>
                            <small class="text-muted"><span class="status-dot ${statusClass}"></span> ${statusText}</small>
                        </div>
                        <div class="card-body">
                            <p class="mb-1 text-muted"><i class="bi bi-building"></i> ${acc.broker || '-'}</p>
                            ${acc.initial_capital ? `<p class="mb-1 text-muted"><i class="bi bi-wallet2"></i> เงินทุนเริ่มต้น: <strong>$${parseFloat(acc.initial_capital).toLocaleString()}</strong></p>` : ''}
                            
                            <div class="row mt-3 text-center">
                                <div class="col-6 border-end">
                                    <small class="text-muted">Balance</small>
                                    <h4 class="mb-0">$${parseFloat(acc.balance).toLocaleString()}</h4>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Equity</small>
                                    <h4 class="mb-0 fw-bold text-primary">$${parseFloat(acc.equity).toLocaleString()}</h4>
                                </div>
                            </div>

                            <div class="row mt-2 text-center">
                                <div class="col-4 border-end">
                                    <small class="text-muted">กำไรทั้งหมด</small>
                                    <h5 class="mb-0 ${totalProfitClass} fw-bold">
                                        ${totalProfit !== null ? (totalProfit > 0 ? '+' : '') + totalProfit.toLocaleString() + ' <i class="bi ' + totalProfitIcon + '"></i>' : '<span class="text-muted">-</span>'}
                                    </h5>
                                </div>
                                <div class="col-4 border-end">
                                    <small class="text-muted">กำไรวันนี้</small>
                                    <h5 class="mb-0 ${dailyProfitClass} fw-bold">
                                        ${dailyProfit > 0 ? '+' : ''}${dailyProfit.toLocaleString()} <i class="bi ${dailyProfitIcon}"></i>
                                    </h5>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Floating P/L</small>
                                    <h5 class="mb-0 ${profitClass} fw-bold">
                                        ${acc.profit > 0 ? '+' : ''}${parseFloat(acc.profit).toLocaleString()} <i class="bi ${profitIcon}"></i>
                                    </h5>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3 p-2 bg-light rounded">
                                <span><i class="bi bi-layers"></i> ออเดอร์: <strong>${acc.order_count}</strong></span>
                            </div>

                            <div class="mt-3 text-center">
                                <small class="text-muted d-block mb-2" style="font-size: 0.8rem;">อัปเดต: ${lastUpdate}</small>
                                <div class="btn-group w-100" role="group">
                                    <button onclick="toggleTrading(${acc.account_number})" class="btn ${acc.trading_enabled !== 0 ? 'btn-success' : 'btn-secondary'}" id="tradingBtn_${acc.account_number}">
                                        <i class="bi ${acc.trading_enabled !== 0 ? 'bi-play-fill' : 'bi-pause-fill'}"></i> ${acc.trading_enabled !== 0 ? 'เทรดอยู่' : 'หยุดอยู่'}
                                    </button>
                                    <button onclick="sendCloseCommand(${acc.account_number})" class="btn btn-outline-danger" ${acc.order_count == 0 ? 'disabled' : ''}>
                                        <i class="bi bi-x-circle"></i> ปิดออเดอร์
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            });

            html += `
                </div>
            </div>`;
        }
        
        container.innerHTML = html;
    }

    // ฟังก์ชันส่งคำสั่งปิดออเดอร์
    function sendCloseCommand(accNum) {
        let msg = accNum === 0 ? "คุณต้องการปิดทุกออเดอร์ ทุกบัญชี ใช่หรือไม่?" : `คุณต้องการปิดออเดอร์บัญชี ${accNum} ใช่หรือไม่?`;
        
        Swal.fire({
            title: 'ยืนยันคำสั่ง?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ใช่, ปิดทันที!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // ส่ง AJAX Post
                const formData = new FormData();
                formData.append('account_number', accNum);

                fetch('api.php?action=close_all', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire('สำเร็จ!', data.msg, 'success');
                });
            }
        });
    }

    // ฟังก์ชัน Toggle สถานะการเทรด
    async function toggleTrading(accNum) {
        const formData = new FormData();
        formData.append('account_number', accNum);

        try {
            const res = await fetch('api.php?action=toggle_trading', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                Swal.fire({
                    icon: data.trading_enabled ? 'success' : 'warning',
                    title: data.trading_enabled ? 'เปิดการเทรด' : 'หยุดการเทรด',
                    text: `บัญชี ${accNum} ${data.message}`,
                    timer: 1500,
                    showConfirmButton: false
                });
                fetchAccounts(); // รีเฟรชข้อมูล
            } else {
                Swal.fire('ผิดพลาด', data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            Swal.fire('ผิดพลาด', 'ไม่สามารถเปลี่ยนสถานะได้', 'error');
        }
    }

    // ตั้งเวลาให้โหลดข้อมูลใหม่ทุกๆ 2 วินาที (Auto Refresh)
    fetchAccounts();
    setInterval(fetchAccounts, 2000);

</script>
</body>
</html>