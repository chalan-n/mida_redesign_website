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

// รับค่า account number จาก URL
$account_number = isset($_GET['account']) ? intval($_GET['account']) : 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EA Control Center - Trading History</title>
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
        
        .main-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header {
            background: var(--primary-gradient);
            color: white;
            padding: 20px 30px;
            border: none;
        }
        
        .card-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.3rem;
        }
        
        .account-info {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* Table Styling */
        .table-wrapper {
            overflow-x: auto;
        }
        
        .trading-table {
            width: 100%;
            min-width: 1200px;
            border-collapse: collapse;
        }
        
        .trading-table thead th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
            text-align: center;
        }
        
        .trading-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            text-align: center;
            white-space: nowrap;
        }
        
        .trading-table tbody tr:hover {
            background: #f8fafc;
        }
        
        .trading-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Data Styling */
        .date-cell {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.95rem;
        }
        
        .lot-size {
            font-weight: 600;
            color: #475569;
        }
        
        .frequency {
            background: none !important;
            color: #1e293b !important;
            border-radius: 0 !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            box-shadow: none !important;
            transition: none !important;
        }
        
        .frequency:hover {
            transform: none !important;
            box-shadow: none !important;
        }
        
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
        
        .percentage {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .balance {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.95rem;
        }
        
        .floating-loss {
            color: #dc2626;
            font-weight: 600;
        }
        
        .floating-profit {
            color: #16a34a;
            font-weight: 600;
        }
        
        .winning-rate {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .winning-rate.low {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .winning-rate.medium {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #94a3b8;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        /* Loading */
        .loading-container {
            text-align: center;
            padding: 100px 20px;
        }
        
        /* Back Button */
        .back-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }
        
        /* Summary Stats */
        .stats-row {
            background: #f8fafc;
            padding: 20px;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        
        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .stat-value.profit {
            color: #16a34a;
        }
        
        .stat-value.loss {
            color: #dc2626;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-robot"></i> EA Commander <small class="opacity-75">| Trading History</small></span>
        <div class="d-flex align-items-center gap-2">
            <a href="dashboard2.php" class="back-btn">
                <i class="bi bi-arrow-left"></i> กลับ
            </a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-grid-fill"></i> Card View
            </a>
            <a href="settings.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-gear-fill"></i> ตั้งค่า
            </a>
            <a href="?logout" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="main-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3><i class="bi bi-graph-up me-2"></i>ประวัติการเทรด</h3>
            <div class="account-info">
                <i class="bi bi-person-circle me-1"></i>
                <span id="account-display">กำลังโหลด...</span>
            </div>
        </div>
        
        <div class="stats-row" id="summary-stats" style="display: none;">
            <div class="stat-item">
                <div class="stat-label">วันที่เทรดทั้งหมด</div>
                <div class="stat-value" id="total-days">-</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">กำไร/ขาดทุนรวม</div>
                <div class="stat-value" id="total-pl">-</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">อัตราชนะเฉลี่ย</div>
                <div class="stat-value" id="avg-win-rate">-</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">ความถี่เฉลี่ย</div>
                <div class="stat-value" id="avg-frequency">-</div>
            </div>
        </div>
        
        <div class="loading-container" id="loading">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-muted">กำลังโหลดข้อมูลประวัติการเทรด...</p>
        </div>
        
        <div class="table-wrapper p-4" id="table-container" style="display: none;">
            <table class="trading-table">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>Lot ทั้งหมด</th>
                        <th>จำนวนเทรด</th>
                        <th>กำไร/ขาดทุน</th>
                        <th>เปอร์เซ็นต์</th>
                        <th>ขาดทุนสูงสุด</th>
                        <th>กำไรสูงสุด</th>
                        <th>อัตราชนะ</th>
                    </tr>
                </thead>
                <tbody id="trading-data">
                </tbody>
            </table>
        </div>
        
        <div class="empty-state" id="empty-state" style="display: none;">
            <i class="bi bi-calendar-x"></i>
            <p class="fs-5">ยังไม่มีข้อมูลประวัติการเทรด</p>
            <p class="text-muted">บัญชีนี้ยังไม่มีข้อมูลการเทรดในระบบ</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let accountNumber = <?php echo $account_number; ?>;
    
    // ฟังก์ชันดึงข้อมูลประวัติการเทรด
    async function fetchTradingHistory() {
        if (accountNumber === 0) {
            showError('ไม่พบเลขบัญชี');
            return;
        }
        
        try {
            const response = await fetch(`api.php?action=get_trading_history&account=${accountNumber}`);
            const data = await response.json();
            
            if (data.error) {
                showError(data.error);
                return;
            }
            
            renderTradingHistory(data);
        } catch (error) {
            console.error('Error:', error);
            showError('ไม่สามารถโหลดข้อมูลได้');
        }
    }
    
    // ฟังก์ชันแสดงข้อมูลประวัติการเทรด
    function renderTradingHistory(data) {
        const loading = document.getElementById('loading');
        const tableContainer = document.getElementById('table-container');
        const emptyState = document.getElementById('empty-state');
        const summaryStats = document.getElementById('summary-stats');
        const accountDisplay = document.getElementById('account-display');
        
        loading.style.display = 'none';
        
        if (!data.account_info || data.history.length === 0) {
            emptyState.style.display = 'block';
            return;
        }
        
        // แสดงข้อมูลบัญชี
        accountDisplay.textContent = `บัญชี ${data.account_info.account_number} - ${data.account_info.ea_name || 'N/A'}`;
        
        // คำนวณสถิติ
        const stats = calculateStats(data.history);
        document.getElementById('total-days').textContent = stats.totalDays;
        document.getElementById('total-pl').textContent = stats.totalPL;
        document.getElementById('total-pl').className = `stat-value ${stats.totalPLClass}`;
        document.getElementById('avg-win-rate').textContent = stats.avgWinRate;
        document.getElementById('avg-frequency').textContent = stats.avgFrequency;
        summaryStats.style.display = 'flex';
        
        // สร้างตารางข้อมูล
        const tbody = document.getElementById('trading-data');
        tbody.innerHTML = '';
        
        data.history.forEach(record => {
            const row = createTableRow(record);
            tbody.appendChild(row);
        });
        
        tableContainer.style.display = 'block';
    }
    
    // ฟังก์ชันสร้างแถวในตาราง
    function createTableRow(record) {
        const tr = document.createElement('tr');
        
        // Date
        const dateCell = document.createElement('td');
        dateCell.className = 'date-cell';
        dateCell.textContent = formatDate(record.trade_date);
        tr.appendChild(dateCell);
        
        // Total lot size
        const lotCell = document.createElement('td');
        lotCell.className = 'lot-size';
        lotCell.textContent = parseFloat(record.total_lot_size).toLocaleString();
        tr.appendChild(lotCell);
        
        // Frequency
        const freqCell = document.createElement('td');
        freqCell.className = 'frequency';
        freqCell.textContent = record.frequency;
        tr.appendChild(freqCell);
        
        // Profit and loss amount
        const plCell = document.createElement('td');
        const plAmount = parseFloat(record.profit_loss_amount);
        plCell.className = plAmount >= 0 ? 'profit-positive' : 'profit-negative';
        plCell.textContent = (plAmount >= 0 ? '+' : '') + plAmount.toLocaleString();
        tr.appendChild(plCell);
        
        // Percentage
        const pctCell = document.createElement('td');
        const pct = parseFloat(record.profit_loss_percentage);
        pctCell.className = 'percentage ' + (pct >= 0 ? 'profit-positive' : 'profit-negative');
        pctCell.textContent = (pct >= 0 ? '+' : '') + pct.toFixed(2) + '%';
        tr.appendChild(pctCell);
        
        // Max floating loss
        const maxLossCell = document.createElement('td');
        const maxLoss = parseFloat(record.max_floating_loss);
        maxLossCell.className = 'floating-loss';
        maxLossCell.textContent = '-' + Math.abs(maxLoss).toLocaleString();
        tr.appendChild(maxLossCell);
        
        // Max floating profit
        const maxProfitCell = document.createElement('td');
        const maxProfit = parseFloat(record.max_floating_profit);
        maxProfitCell.className = 'floating-profit';
        maxProfitCell.textContent = '+' + maxProfit.toLocaleString();
        tr.appendChild(maxProfitCell);
        
        // Winning rate
        const winRateCell = document.createElement('td');
        const winRate = parseFloat(record.winning_rate);
        const winRateSpan = document.createElement('span');
        winRateSpan.className = 'winning-rate';
        if (winRate < 40) winRateSpan.className += ' low';
        else if (winRate < 60) winRateSpan.className += ' medium';
        winRateSpan.textContent = winRate.toFixed(2) + '%';
        winRateCell.appendChild(winRateSpan);
        tr.appendChild(winRateCell);
        
        return tr;
    }
    
    // ฟังก์ชันคำนวณสถิติ
    function calculateStats(history) {
        const totalDays = history.length;
        let totalPL = 0;
        let totalWinRate = 0;
        let totalFrequency = 0;
        
        history.forEach(record => {
            totalPL += parseFloat(record.profit_loss_amount);
            totalWinRate += parseFloat(record.winning_rate);
            totalFrequency += parseInt(record.frequency);
        });
        
        const avgWinRate = totalDays > 0 ? (totalWinRate / totalDays).toFixed(2) + '%' : '0%';
        const avgFrequency = totalDays > 0 ? (totalFrequency / totalDays).toFixed(1) : '0';
        
        return {
            totalDays,
            totalPL: (totalPL >= 0 ? '+' : '') + totalPL.toLocaleString(),
            totalPLClass: totalPL >= 0 ? 'profit' : 'loss',
            avgWinRate,
            avgFrequency
        };
    }
    
    // ฟังก์ชันจัดรูปแบบวันที่
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('th-TH', { 
            year: 'numeric', 
            month: '2-digit', 
            day: '2-digit' 
        });
    }
    
    // ฟังก์ชันแสดงข้อผิดพลาด
    function showError(message) {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('empty-state').style.display = 'block';
        document.getElementById('empty-state').querySelector('p.fs-5').textContent = message;
    }
    
    // เริ่มโหลดข้อมูล
    fetchTradingHistory();
</script>
</body>
</html>
