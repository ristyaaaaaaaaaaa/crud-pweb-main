<?php
  session_start();
  // berasal dari login
  if(!isset($_SESSION['login'])){
    header("location: login.php");
    exit();
  }

  include 'koneksi.php';

  // Hitung jumlah user
  $stmt_users = $conn->prepare("SELECT COUNT(id) as total_users FROM user");
  $stmt_users->execute();
  $result_users = $stmt_users->get_result();
  $total_users = $result_users->fetch_assoc()['total_users'];
  $stmt_users->close();

  // Hitung jumlah transaksi dan total omset penjualan
  $stmt_penjualan = $conn->prepare("SELECT COUNT(id) as total_penjualan, SUM(total) as total_omset FROM penjualan");
  $stmt_penjualan->execute();
  $result_penjualan = $stmt_penjualan->get_result();
  $data_penjualan = $result_penjualan->fetch_assoc();
  $total_penjualan = $data_penjualan['total_penjualan'] ?? 0;
  $total_omset = $data_penjualan['total_omset'] ?? 0;
  $stmt_penjualan->close();

  // Hitung jumlah transaksi dan total biaya pembelian
  $stmt_pembelian = $conn->prepare("SELECT COUNT(id) as total_pembelian, SUM(total) as total_biaya FROM pembelian");
  $stmt_pembelian->execute();
  $result_pembelian = $stmt_pembelian->get_result();
  $data_pembelian = $result_pembelian->fetch_assoc();
  $total_pembelian = $data_pembelian['total_pembelian'] ?? 0;
  $total_biaya_pembelian = $data_pembelian['total_biaya'] ?? 0;
  $stmt_pembelian->close();

  // Hitung Keuntungan
  $keuntungan = $total_omset - $total_biaya_pembelian;

  // Data untuk Chart Penjualan dan Pembelian Bulanan
  $tahun_sekarang = date('Y');
  // Data Penjualan
  $query_chart_penjualan = "
      SELECT 
          MONTH(tanggal) as bulan, 
          SUM(total) as total_bulanan 
      FROM penjualan 
      WHERE YEAR(tanggal) = ?
      GROUP BY MONTH(tanggal)
      ORDER BY MONTH(tanggal) ASC";
  $stmt_chart_penjualan = $conn->prepare($query_chart_penjualan);
  $stmt_chart_penjualan->bind_param("i", $tahun_sekarang);
  $stmt_chart_penjualan->execute();
  $result_chart_penjualan = $stmt_chart_penjualan->get_result();

  $total_data_penjualan = array_fill(0, 12, 0); // Inisialisasi array penjualan dengan 12 nilai 0
  $nama_bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

  // Proses hasil query penjualan
  while ($row = $result_chart_penjualan->fetch_assoc()) {
      $total_data_penjualan[$row['bulan'] - 1] = $row['total_bulanan'];
  }
  $stmt_chart_penjualan->close();

  // Data Pembelian
  $query_chart_pembelian = "
      SELECT 
          MONTH(tanggal) as bulan, 
          SUM(total) as total_bulanan 
      FROM pembelian 
      WHERE YEAR(tanggal) = ?
      GROUP BY MONTH(tanggal)
      ORDER BY MONTH(tanggal) ASC";
  $stmt_chart_pembelian = $conn->prepare($query_chart_pembelian);
  $stmt_chart_pembelian->bind_param("i", $tahun_sekarang);
  $stmt_chart_pembelian->execute();
  $result_chart_pembelian = $stmt_chart_pembelian->get_result();
  
  $total_data_pembelian = array_fill(0, 12, 0); // Inisialisasi array pembelian dengan 12 nilai 0

  // Proses hasil query pembelian
  while ($row = $result_chart_pembelian->fetch_assoc()) {
      // array index is 0-11, but month is 1-12
      $total_data_pembelian[$row['bulan'] - 1] = $row['total_bulanan'];
  }
  $stmt_chart_pembelian->close();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Project UAS | Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <style>
    /* Scope only to the content dashboard body area to keep layout native */
    .content-wrapper {
      background-color: #f8fafc !important;
      padding: 30px 20px !important;
    }

    /* Target ONLY dashboard contents to use Poppins with standard native proportions */
    .modern-hero-card, 
    .modern-stat-card,
    .modern-chart-card {
      font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol" !important;
    }

    /* 1. Hero Card Container */
    .modern-hero-card {
      background: linear-gradient(135deg, #1e40af, #3b82f6);
      border-radius: 20px;
      padding: 35px;
      color: #ffffff;
      box-shadow: 0 10px 25px rgba(37, 99, 235, 0.15);
      margin-bottom: 35px;
      position: relative;
      overflow: hidden;
    }

    .modern-hero-card::after {
      content: '';
      position: absolute;
      right: -10%;
      top: -30%;
      width: 300px;
      height: 300px;
      background: rgba(255, 255, 255, 0.06);
      border-radius: 50%;
    }

    .modern-hero-card h2 {
      font-weight: 700;
      font-size: 28px;
      margin-bottom: 10px;
      letter-spacing: -0.5px;
    }

    .modern-hero-card p {
      font-weight: 400;
      font-size: 15px;
      opacity: 0.9;
      margin-bottom: 0;
    }

    /* 2 & 3. Modern Statistic Cards */
    .modern-stat-link {
      text-decoration: none !important;
      display: block;
      margin-bottom: 30px;
    }

    .modern-stat-card {
      border-radius: 20px;
      padding: 25px;
      color: #ffffff;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
      border: none;
    }

    .modern-stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
    }

    /* Gradient Variants */
    .card-omset { background: linear-gradient(135deg, #2563eb, #3b82f6); }
    .card-penjualan { background: linear-gradient(135deg, #10b981, #34d399); }
    .card-pembelian { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .card-user { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
    .card-biaya { background: linear-gradient(135deg, #ef4444, #f87171); }
    .card-keuntungan { background: linear-gradient(135deg, #06b6d4, #67e8f9); }

    .stat-content {
      display: flex;
      flex-direction: column;
      z-index: 2;
    }

    .stat-label {
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      opacity: 0.85;
      margin-bottom: 6px;
    }

    .stat-number {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 4px;
      line-height: 1.2;
    }

    .stat-subtext {
      font-size: 11px;
      font-weight: 400;
      opacity: 0.8;
    }

    .stat-icon-wrapper {
      width: 55px;
      height: 55px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(5px);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      z-index: 2;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    /* 5. Modern Graph Card Container */
    .modern-chart-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 25px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.02);
      border: 1px solid #edf2f7;
      margin-bottom: 30px;
    }

    .modern-chart-header {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      border-bottom: 1px solid #f1f5f9;
      padding-bottom: 15px;
    }

    .modern-chart-header i {
      font-size: 18px;
      color: #2563eb;
      margin-right: 12px;
    }

    .modern-chart-title {
      font-size: 16px;
      font-weight: 600;
      color: #1e293b;
      margin: 0;
    }

    /* Grid Spacing Utility Override */
    .content .row {
      margin-left: -12px;
      margin-right: -12px;
    }
    .content [class*="col-"] {
      padding-left: 12px;
      padding-right: 12px;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <li>
        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#logoutModal">
          <i class="fas fa-sign-out-alt mr-1"></i>Log out
        </button>
      </li>
    </ul>
  </nav>
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a class="d-block">Alexander Pierce</a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
              </p>
            </a>
          </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-table"></i>
              <p>
                Data Master
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="user.php" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data User</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-header">TRANSAKSI</li>
          <li class="nav-item">
            <a href="penjualan.php" class="nav-link">
              <i class="nav-icon far fa-calendar-alt"></i>
              <p>
                Penjualan
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="pembelian.php" class="nav-link">
              <i class="nav-icon far fa-image"></i>
              <p>
                Pembelian
              </p>
            </a>
          </li>

          <li class="nav-header">LAPORAN</li>
          <li class="nav-item">
            <a href="laporan_penjualan.php" class="nav-link">
              <i class="nav-icon fas fa-file"></i>
              <p>Laporan Penjualan</p>
            </a>
          </li>
        </ul>
      </nav>
      </div>
    </aside>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Dashboard</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div>
        </div>
      </div></section>

    <section class="content">
      <div class="container-fluid">
        
        <div class="row">
          <div class="col-12">
            <div class="modern-hero-card">
              <h2>Selamat Datang di Sistem Informasi Penjualan</h2>
              <p>Kelola transaksi penjualan dan pembelian dengan lebih mudah.</p>
            </div>
          </div>
        </div>

        <div class="row">
          
          <div class="col-lg-4 col-md-6 col-12">
            <a href="penjualan.php" class="modern-stat-link">
              <div class="modern-stat-card card-omset">
                <div class="stat-content">
                  <span class="stat-label">Total Omset</span>
                  <span class="stat-number">Rp <?= number_format($total_omset, 0, ',', '.'); ?></span>
                  <span class="stat-subtext">+12% dibanding bulan lalu</span>
                </div>
                <div class="stat-icon-wrapper">
                  <i class="fas fa-chart-line"></i>
                </div>
              </div>
            </a>
          </div>

          <div class="col-lg-4 col-md-6 col-12">
            <a href="penjualan.php" class="modern-stat-link">
              <div class="modern-stat-card card-penjualan">
                <div class="stat-content">
                  <span class="stat-label">Jumlah Transaksi Penjualan</span>
                  <span class="stat-number"><?= number_format($total_penjualan); ?></span>
                  <span class="stat-subtext">+12% dibanding bulan lalu</span>
                </div>
                <div class="stat-icon-wrapper">
                  <i class="fas fa-shopping-bag"></i>
                </div>
              </div>
            </a>
          </div>

          <div class="col-lg-4 col-md-6 col-12">
            <a href="pembelian.php" class="modern-stat-link">
              <div class="modern-stat-card card-pembelian">
                <div class="stat-content">
                  <span class="stat-label">Jumlah Transaksi Pembelian</span>
                  <span class="stat-number"><?= number_format($total_pembelian); ?></span>
                  <span class="stat-subtext">+12% dibanding bulan lalu</span>
                </div>
                <div class="stat-icon-wrapper">
                  <i class="fas fa-shopping-cart"></i>
                </div>
              </div>
            </a>
          </div>

          <div class="col-lg-4 col-md-6 col-12">
            <a href="user.php" class="modern-stat-link">
              <div class="modern-stat-card card-user">
                <div class="stat-content">
                  <span class="stat-label">Total User</span>
                  <span class="stat-number"><?= number_format($total_users); ?></span>
                  <span class="stat-subtext">+12% dibanding bulan lalu</span>
                </div>
                <div class="stat-icon-wrapper">
                  <i class="fas fa-users"></i>
                </div>
              </div>
            </a>
          </div>

          <div class="col-lg-4 col-md-6 col-12">
            <a href="pembelian.php" class="modern-stat-link">
              <div class="modern-stat-card card-biaya">
                <div class="stat-content">
                  <span class="stat-label">Total Biaya</span>
                  <span class="stat-number">Rp <?= number_format($total_biaya_pembelian, 0, ',', '.'); ?></span>
                  <span class="stat-subtext">+12% dibanding bulan lalu</span>
                </div>
                <div class="stat-icon-wrapper">
                  <i class="fas fa-wallet"></i>
                </div>
              </div>
            </a>
          </div>

          <div class="col-lg-4 col-md-6 col-12">
            <a href="laporan_penjualan.php" class="modern-stat-link">
              <div class="modern-stat-card card-keuntungan">
                <div class="stat-content">
                  <span class="stat-label">Total Keuntungan</span>
                  <span class="stat-number">Rp <?= number_format($keuntungan, 0, ',', '.'); ?></span>
                  <span class="stat-subtext">+12% dibanding bulan lalu</span>
                </div>
                <div class="stat-icon-wrapper">
                  <i class="fas fa-hand-holding-usd"></i>
                </div>
              </div>
            </a>
          </div>

        </div>
        <div class="row">
          <section class="col-lg-7 col-12">
            <div class="modern-chart-card">
              <div class="modern-chart-header">
                <i class="fas fa-chart-bar"></i>
                <h3 class="modern-chart-title">
                  Grafik Penjualan vs Pembelian Tahun <?= $tahun_sekarang ?>
                </h3>
              </div>
              <div class="chart-body-wrapper">
                <div class="chart">
                  <canvas id="monthlyBarChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              </div>
            </div>
          </section>

          <section class="col-lg-5 col-12">
            <div class="modern-chart-card">
              <div class="modern-chart-header">
                <i class="fas fa-chart-pie"></i>
                <h3 class="modern-chart-title">
                  Komposisi Omset
                </h3>
              </div>
              <div class="chart-body-wrapper">
                <div class="chart">
                  <canvas id="profitChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              </div>
            </div>
          </section>
        </div>
        </div></section>
    </div>
  <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 3.0.0
    </div>
    <strong>Copyright &copy; 2014-2019 <a href="http://adminlte.io">AdminLTE.io</a>.</strong> All rights
    reserved.
  </footer>

  <aside class="control-sidebar control-sidebar-dark">
    </aside>
  </div>
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="dist/js/adminlte.min.js"></script>
<script>
  $(function() {
    'use strict'

    // This will get the first returned node in the jQuery collection.
    var salesChart = new Chart($('#monthlyBarChart').get(0).getContext('2d'), { 
      type: 'bar', 
      data: {
        labels: <?= json_encode($nama_bulan) ?>,
        datasets: [
          {
            label: 'Omset Penjualan',
            backgroundColor: '#2563eb', 
            hoverBackgroundColor: '#1d4ed8', 
            borderColor: '#2563eb',
            data: <?= json_encode($total_data_penjualan) ?>,
            borderRadius: 6
          },
          {
            label: 'Biaya Pembelian',
            backgroundColor: '#ef4444', 
            hoverBackgroundColor: '#dc2626', 
            borderColor: '#ef4444',
            data: <?= json_encode($total_data_pembelian) ?>,
            borderRadius: 6
          }
        ]
      }, 
      options: {
        maintainAspectRatio: false,
        responsive: true,
        animations: {
          y: {
            duration: 800,
            easing: 'easeInOutQuad'
          }
        },
        plugins: {
          legend: {
            display: true,
            labels: {
              font: {
                size: 12
              }
            }
          }
        },
        scales: {
          x: {
            grid: {
              display: false
            }
          },
          y: {
            grid: {
              display: true,
              color: '#f1f5f9'
            },
            ticks: {
              beginAtZero: true
            }
          }
        }
      }
    })

    //-------------
    //- DONUT CHART -
    //-------------
    var profitChartCanvas = $('#profitChart').get(0).getContext('2d')
    var profitChartData = {
      labels: [ 'Biaya Pembelian', 'Keuntungan' ],
      datasets: [ {
          data: [<?= $total_biaya_pembelian ?>, <?= $keuntungan ?>],
          backgroundColor: ['#ef4444', '#06b6d4'],
          borderWidth: 2,
          borderColor: '#ffffff'
      }]
    }
    var profitChart = new Chart(profitChartCanvas, {
      type: 'doughnut',
      data: profitChartData,
      options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 15
            }
          }
        }
      }
    })
  });
</script>

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Log Out</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin keluar?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <a href="logout.php" class="btn btn-danger">Log Out</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>