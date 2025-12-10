<?php
require_once 'config/auth.php';
requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - Monitoring Pakan Ikan</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
</head>
<body class="dashboard-page">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1>Log Aktivitas Sistem</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="refreshTable()">
                        <i data-lucide="refresh-cw"></i>
                        <span>Refresh</span>
                    </button>
                    <button class="btn btn-primary" onclick="exportCSV()">
                        <i data-lucide="download"></i>
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>
            
            <div class="card">
                <div class="table-container">
                    <table id="logTable" class="display">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Waktu Eksekusi</th>
                                <th>Sumber</th>
                                <th>Bukaan Servo</th>
                                <th>Suhu Air (°C)</th>
                                <th>Pesan</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        let table;
        
        $(document).ready(function() {
            table = $('#logTable').DataTable({
                ajax: {
                    url: '/api/get-logs.php',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'id' },
                    { data: 'waktu_eksekusi' },
                    { 
                        data: 'sumber',
                        render: function(data) {
                            let badgeClass = 'badge-default';
                            if (data.includes('ML')) badgeClass = 'badge-success';
                            else if (data.includes('Manual')) badgeClass = 'badge-primary';
                            else if (data.includes('Auto')) badgeClass = 'badge-warning';
                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    { data: 'bukaan_servo' },
                    { 
                        data: 'suhu_air',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(1) : '-';
                        }
                    },
                    { data: 'pesan' }
                ],
                order: [[1, 'desc']],
                pageLength: 25,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });
            
            // Auto refresh every 10 seconds
            setInterval(refreshTable, 10000);
        });
        
        function refreshTable() {
            table.ajax.reload(null, false);
        }
        
        function exportCSV() {
            window.location.href = '/api/export-logs.php';
        }
        
        lucide.createIcons();
    </script>
</body>
</html>
