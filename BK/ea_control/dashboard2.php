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
    <title>EA Control Center - Dashboard 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0d47a1 0%, #1565c0 50%, #1e88e5 100%);
            --card-shadow: 0 8px 32px rgba(13, 71, 161, 0.12);
        }
        
        * {
            font-family: 'Prompt', sans-serif;
        }
        
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: var(--primary-gradient) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        /* VPS Group Card */
        .vps-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .vps-header {
            background: var(--primary-gradient);
            color: white;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .vps-header.no-vps {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }
        
        .vps-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .vps-ip {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        /* Table Wrapper - always scrollable */
        .accounts-table-wrapper {
            overflow-x: auto;
        }
        
        /* Table Styling */
        .accounts-table {
            width: 100%;
            min-width: 1600px;
            border-collapse: collapse;
        }
        
        .accounts-table thead th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 500;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        
        .accounts-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            white-space: nowrap;
        }
        
        .accounts-table tbody tr:hover {
            background: #f8fafc;
        }
        
        .accounts-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Account Number */
        .account-number {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }
        
        .account-number:hover {
            color: #0d47a1;
        }
        
        /* EA Badge */
        .ea-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        /* MT Type Badge */
        .mt-badge {
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
            margin-left: 6px;
        }
        .mt-badge.mt4 {
            background: #dcfce7;
            color: #166534;
        }
        .mt-badge.mt5 {
            background: #dbeafe;
            color: #1e40af;
        }
        
        /* Account Mode Badge */
        .mode-badge {
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
        .mode-badge.demo {
            background: #fef3c7;
            color: #d97706;
        }
        .mode-badge.real {
            background: #d1fae5;
            color: #059669;
        }
        
        /* Balance */
        .balance-value {
            font-weight: 600;
            font-size: 0.95rem;
            color: #1e293b;
        }
        
        /* Profit Values */
        .profit-positive {
            color: #16a34a;
            font-weight: 600;
        }
        
        .profit-negative {
            color: #dc2626;
            font-weight: 600;
        }
        
        .profit-zero {
            color: #64748b;
            font-weight: 500;
        }
        
        /* Order Count Badge */
        .order-badge {
            background: #e0e7ff;
            color: #4338ca;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .order-badge.has-orders {
            background: #fef3c7;
            color: #d97706;
        }
        
        /* Status Dot */
        .status-container {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .status-dot {
            display: inline-block;
            height: 8px;
            width: 8px;
            border-radius: 50%;
        }
        
        .status-dot.online {
            background-color: #22c55e;
            box-shadow: 0 0 8px rgba(34, 197, 94, 0.6);
        }
        
        .status-dot.offline {
            background-color: #ef4444;
        }
        
        /* Action Button */
        .btn-close-orders {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: none;
            color: #dc2626;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-close-orders:hover:not(:disabled) {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-close-orders:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        /* Badge Counter */
        .account-count {
            background: rgba(255,255,255,0.25);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        /* Responsive Table */
        @media (max-width: 992px) {
            .accounts-table-wrapper {
                overflow-x: auto;
            }
            
            .accounts-table {
                min-width: 1200px;
            }
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        /* Loading */
        .loading-container {
            text-align: center;
            padding: 80px 20px;
        }
        
        /* Star Button */
        .star-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 4px 8px;
            transition: all 0.3s ease;
            color: #cbd5e1;
        }
        
        .star-btn:hover {
            transform: scale(1.2);
        }
        
        .star-btn.active {
            color: #fbbf24;
            text-shadow: 0 0 8px rgba(251, 191, 36, 0.6);
        }
        
        .star-btn:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-robot"></i> EA Commander <small class="opacity-75">| Horizontal View</small></span>
        <div class="d-flex align-items-center gap-2">
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-grid-fill"></i> Card View
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

<div class="container-fluid" id="main-container">
    <div class="loading-container">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-3 text-muted">กำลังเชื่อมต่อข้อมูล...</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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

    // ฟังก์ชันวาดหน้าจอ - แบบตารางแนวนอน จัดกลุ่มตาม VPS
    function renderAccounts(accounts) {
        const container = document.getElementById('main-container');
        
        if (accounts.length === 0) {
            container.innerHTML = `
                <div class="vps-card">
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p class="fs-5">ยังไม่มีข้อมูลบัญชีเชื่อมต่อเข้ามา</p>
                    </div>
                </div>`;
            return;
        }

        // จัดกลุ่มบัญชีตาม VPS
        const vpsGroups = {};
        accounts.forEach(acc => {
            const vpsKey = acc.vps_name || '__NO_VPS__';
            if (!vpsGroups[vpsKey]) {
                vpsGroups[vpsKey] = {
                    ip: acc.vps_ip || '',
                    accounts: []
                };
            }
            vpsGroups[vpsKey].accounts.push(acc);
        });
        
        // เรียงลำดับบัญชีในแต่ละกลุ่ม VPS โดยบัญชีที่มาร์กดาวจะแสดงก่อน
        for (const vpsKey in vpsGroups) {
            vpsGroups[vpsKey].accounts.sort((a, b) => {
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
        for (const [vpsName, vpsData] of Object.entries(vpsGroups)) {
            const isNoVps = vpsName === '__NO_VPS__';
            const displayName = isNoVps ? 'ยังไม่ได้กำหนด VPS' : vpsName;
            const headerClass = isNoVps ? 'vps-header no-vps' : 'vps-header';
            const accs = vpsData.accounts;
            const vpsIp = vpsData.ip;

            html += `
            <div class="vps-card">
                <div class="${headerClass}">
                    <h5><i class="bi bi-server me-2"></i> ${displayName}</h5>
                    <div class="d-flex align-items-center gap-3">
                        ${vpsIp ? `<span class="vps-ip"><i class="bi bi-globe me-1"></i>${vpsIp}</span>` : ''}
                        <span class="account-count">${accs.length} บัญชี</span>
                    </div>
                </div>
                <div class="accounts-table-wrapper">
                    <table class="accounts-table">
                        <thead>
                            <tr>
                                <th>เลขบัญชี</th>
                                <th>ชื่อ EA</th>
                                <th>เงินทุนเริ่มต้น</th>
                                <th>Balance</th>
                                <th>กำไรทั้งหมด</th>
                                <th>กำไรวันนี้</th>
                                <th>ออเดอร์</th>
                                <th>Floating P/L</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>`;

            accs.forEach(acc => {
                // คำนวณสี Floating P/L
                const floatingProfit = parseFloat(acc.profit) || 0;
                const floatingClass = floatingProfit > 0 ? 'profit-positive' : (floatingProfit < 0 ? 'profit-negative' : 'profit-zero');
                const floatingIcon = floatingProfit > 0 ? 'bi-caret-up-fill' : (floatingProfit < 0 ? 'bi-caret-down-fill' : '');
                const floatingPrefix = floatingProfit > 0 ? '+' : '';
                
                // คำนวณสี Daily Profit
                const dailyProfit = parseFloat(acc.daily_profit) || 0;
                const dailyClass = dailyProfit > 0 ? 'profit-positive' : (dailyProfit < 0 ? 'profit-negative' : 'profit-zero');
                const dailyIcon = dailyProfit > 0 ? 'bi-caret-up-fill' : (dailyProfit < 0 ? 'bi-caret-down-fill' : '');
                const dailyPrefix = dailyProfit > 0 ? '+' : '';
                
                // สถานะ Online/Offline
                const statusClass = acc.is_online ? 'online' : 'offline';
                const statusText = acc.is_online ? 'Online' : 'Offline';
                
                // Order count
                const orderCount = parseInt(acc.order_count) || 0;
                const orderBadgeClass = orderCount > 0 ? 'order-badge has-orders' : 'order-badge';
                
                // คำนวณกำไรทั้งหมด (Total Profit = Balance - Initial Capital)
                const initialCapital = parseFloat(acc.initial_capital) || 0;
                const balance = parseFloat(acc.balance) || 0;
                const totalProfit = initialCapital > 0 ? balance - initialCapital : null;
                const totalProfitClass = totalProfit !== null ? (totalProfit >= 0 ? 'profit-positive' : 'profit-negative') : '';
                const totalProfitIcon = totalProfit !== null ? (totalProfit >= 0 ? 'bi-caret-up-fill' : 'bi-caret-down-fill') : '';
                const totalProfitPrefix = totalProfit !== null && totalProfit > 0 ? '+' : '';

                html += `
                            <tr>
                                <td><a href="dashboard3.php?account=${acc.account_number}" class="account-number text-decoration-none">
                                    <button onclick="toggleFavorite(${acc.account_number})" class="star-btn ${acc.is_favorite ? 'active' : ''}" title="${acc.is_favorite ? 'ยกเลิกการมาร์ก' : 'มาร์กบัญชีสำคัญ'}" style="margin-right: 6px;">
                                        ${acc.is_favorite ? '★' : '☆'}
                                    </button>
                                    <span class="status-dot ${statusClass} me-2"></span>${acc.account_number}
                                </a></td>
                                <td>
                                    ${acc.mt_type === 'MT4' ? '<span class="mt-badge mt4">MT4</span>' : (acc.mt_type === 'MT5' ? '<span class="mt-badge mt5">MT5</span>' : '<span class="text-muted">-</span>')}
                                    ${acc.account_mode === 'Demo' ? '<span class="mode-badge demo">Demo</span>' : (acc.account_mode === 'Real' ? '<span class="mode-badge real">Real</span>' : '<span class="text-muted">-</span>')}
                                    ${acc.ea_name ? `<span class="ea-badge"><i class="bi bi-robot"></i> ${acc.ea_name}</span>` : '<span class="text-muted">-</span>'}
                                </td>
                                <td><span class="balance-value">${acc.initial_capital ? '$' + parseFloat(acc.initial_capital).toLocaleString() : '<span class="text-muted">-</span>'}</span></td>
                                <td><span class="balance-value">$${parseFloat(acc.balance).toLocaleString()}</span></td>
                                <td><span class="${totalProfitClass}">${totalProfit !== null ? totalProfitPrefix + totalProfit.toLocaleString() + (totalProfitIcon ? ' <i class="bi ' + totalProfitIcon + '"></i>' : '') : '<span class="text-muted">-</span>'}</span></td>
                                <td><span class="${dailyClass}">${dailyPrefix}${dailyProfit.toLocaleString()} ${dailyIcon ? `<i class="bi ${dailyIcon}"></i>` : ''}</span></td>
                                <td><span class="${orderBadgeClass}">${orderCount}</span></td>
                                <td><span class="${floatingClass}">${floatingPrefix}${floatingProfit.toLocaleString()} ${floatingIcon ? `<i class="bi ${floatingIcon}"></i>` : ''}</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button onclick="toggleTrading(${acc.account_number})" class="btn ${acc.trading_enabled !== 0 ? 'btn-success' : 'btn-secondary'}" title="${acc.trading_enabled !== 0 ? 'หยุดเทรด' : 'เริ่มเทรด'}" style="padding: 6px 12px;">
                                            <i class="bi ${acc.trading_enabled !== 0 ? 'bi-play-fill' : 'bi-pause-fill'}"></i>
                                        </button>
                                        <button onclick="sendCloseCommand(${acc.account_number})" class="btn btn-outline-danger" ${orderCount === 0 ? 'disabled' : ''} title="ปิดออเดอร์ทั้งหมด" style="padding: 6px 12px;">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
            });

            html += `
                        </tbody>
                    </table>
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
                fetchAccounts();
            } else {
                Swal.fire('ผิดพลาด', data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            Swal.fire('ผิดพลาด', 'ไม่สามารถเปลี่ยนสถานะได้', 'error');
        }
    }

    // ฟังก์ชัน Toggle สถานะ Favorite (ดาว)
    async function toggleFavorite(accNum) {
        const formData = new FormData();
        formData.append('account_number', accNum);

        try {
            const res = await fetch('api.php?action=toggle_favorite', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: data.is_favorite ? 'มาร์กบัญชีสำคัญ' : 'ยกเลิกการมาร์ก',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                fetchAccounts();
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
