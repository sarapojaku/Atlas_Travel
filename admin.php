<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Admin Panel</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link rel="icon" href="images/logo.png" type="image/png" />
  <link rel="shortcut icon" href="images/logo.png" type="image/png" />
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #625d5d;
    color: #fff;
}
.sidebar {
    width: 220px;
    background: #4d4949;
    color: #fff;
    display: flex;
    flex-direction: column;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding-top: 2rem;
    box-shadow: 2px 0 5px rgba(0,0,0,0.3);
    z-index: 200;
    transition: transform 0.3s ease-in-out;
}
.sidebar a {
    color: #fff;
    padding: 12px 20px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: background 0.3s;
    margin: 4px 10px;
    cursor: pointer;
}
.sidebar a:hover { background: #625d5d; }
.logout-btn {
    padding: 12px 20px;
    background: #4d4949;
    color: #fff;
    text-align: center;
    text-decoration: none;
    margin-top: auto;
}
.sidebar a.logout-btn:hover { background: #d32f2f; }
.main-content {
    margin-left: 220px;
    min-height: 100vh;
    transition: margin-left 0.3s ease-in-out;
}
.content-header {
    height: 60px;
    background: #4d4949;
    position: sticky;
    top: 0;
    z-index: 150;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    box-shadow: -3px 0 5px rgba(0,0,0,0.3), 0 3px 5px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    padding: 0 1rem;
}
.hamburger {
    display: none;
    font-size: 28px;
    cursor: pointer;
    margin-right: 1rem;
}
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    .sidebar.active {
        transform: translateX(0);
    }
    .main-content {
        margin-left: 0;
    }
    .hamburger {
        display: block;
    }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a data-page="adminDashboard.php" data-title="Dashboard" data-hash="dashboard" class="active">
        <span class="material-icons-outlined">dashboard</span>Dashboard
    </a>
    <a data-page="countries.php" data-title="Countries" data-hash="countries">
        <span class="material-icons-outlined">public</span>Countries
    </a>
    <a data-page="destinations.php" data-title="Destinations" data-hash="destinations">
        <span class="material-icons-outlined">place</span>Destinations
    </a>
    <a data-page="clients.php" data-title="Clients" data-hash="clients">
        <span class="material-icons-outlined">person</span>Clients
    </a>
    <a data-page="sales.php" data-title="Sales" data-hash="sales">
        <span class="material-icons-outlined">attach_money</span>Sales
    </a>
    <a data-page="staff.php" data-title="Staff" data-hash="staff">
        <span class="material-icons-outlined">groups</span>Staff
    </a>
    <a href="admin_logout.php" class="logout-btn">
        <span class="material-icons-outlined">logout</span>Logout
    </a>
</div>

<!-- Main Content -->
<div class="main-content" id="content-area">
    <div class="content-header">
        <span class="material-icons-outlined hamburger" id="hamburger">menu</span>
    </div>
    <?php include 'adminDashboard.php'; ?>
</div>

<script>
// Toggle sidebar on mobile
document.getElementById("hamburger").addEventListener("click", () => {
    document.getElementById("sidebar").classList.toggle("active");
});
</script>

<script>
function loadPage(page, title, hash, makeActive = true) {
    if (makeActive) {
        document.querySelectorAll(".sidebar a").forEach(a => a.classList.remove("active"));
        const activeLink = document.querySelector(`.sidebar a[data-hash="${hash}"]`);
        if (activeLink) activeLink.classList.add("active");
    }

    document.title = title + " - Admin Panel";

    fetch(page)
        .then(res => res.text())
        .then(html => {
            const contentArea = document.getElementById("content-area");
            const header = contentArea.querySelector(".content-header");
            contentArea.innerHTML = "";
            contentArea.appendChild(header);
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = html;
            while(tempDiv.firstChild) {
                contentArea.appendChild(tempDiv.firstChild);
            }
            checkDashboardCharts(); // re-run charts if dashboard
        })
        .catch(() => {
            document.getElementById("content-area").innerHTML =
                `<div class="content-header"></div><p>Error loading page.</p>`;
        });

    window.location.hash = hash;
}

document.querySelectorAll(".sidebar a[data-page]").forEach(link => {
    link.addEventListener("click", function(e) {
        e.preventDefault();
        const page = this.getAttribute("data-page");
        const title = this.getAttribute("data-title");
        const hash = this.getAttribute("data-hash");
        loadPage(page, title, hash);

        // close sidebar on mobile after selecting
        if (window.innerWidth <= 768) {
            document.getElementById("sidebar").classList.remove("active");
        }
    });
});

window.addEventListener("load", () => {
    const hash = window.location.hash.substring(1) || "dashboard";
    const link = document.querySelector(`.sidebar a[data-hash="${hash}"]`);
    if (link) {
        loadPage(link.getAttribute("data-page"), link.getAttribute("data-title"), hash, true);
    }
});
</script>

<!-- 📊 Chart logic -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.49.0/apexcharts.min.js"></script>
<script>
function renderDashboard(topClientsData = {}, topDestinationsData = {}) {
    if (window.barChart) window.barChart.destroy();
    if (window.pieChart) window.pieChart.destroy();

    const clientValues = Object.values(topClientsData).map(v => Number(v) || 0);
    const clientLabels = Object.keys(topClientsData);

    const destValues = Object.values(topDestinationsData).map(v => Number(v) || 0);
    const destLabels = Object.keys(topDestinationsData);

    if (clientLabels.length === 0 || clientValues.every(v => v === 0)) {
        console.warn("⚠️ No client data available for bar chart.");
        return;
    }
    if (destLabels.length === 0 || destValues.every(v => v === 0)) {
        console.warn("⚠️ No destination data available for pie chart.");
        return;
    }

    const barChartOptions = {
        series: [{ data: clientValues, name: 'Spending ($)' }],
        chart: { type: 'bar', height: 350, toolbar:{ show:false }, background:'transparent' },
        plotOptions:{ bar:{ distributed:true, borderRadius:4, horizontal:true, columnWidth:'40%' } },
        colors: ['#2563eb','#f89413','#01b50a','#bd0404','#583cb3'],
        dataLabels:{ enabled:false },
        xaxis:{ categories: clientLabels, labels:{ style:{ colors:'#fff' } } },
        yaxis:{ labels:{ style:{ colors:'#fff' } } },
        tooltip:{ theme:'dark' },
    };
    window.barChart = new ApexCharts(document.querySelector('#bar-chart'), barChartOptions);
    window.barChart.render();

    const pieChartOptions = {
        series: destValues,
        chart: { type:'pie', height:400, background:'transparent' },
        labels: destLabels,
        legend:{ labels:{ colors:'#fff' }, show:true, position:'top' },
        tooltip:{ theme:'dark' },
    };
    window.pieChart = new ApexCharts(document.querySelector('#pie-chart'), pieChartOptions);
    window.pieChart.render();
}

function checkDashboardCharts() {
    const barChartDiv = document.querySelector("#bar-chart");
    const pieChartDiv = document.querySelector("#pie-chart");

    if (!barChartDiv || !pieChartDiv) {
        console.warn("Chart containers not found in DOM.");
        return;
    }
    if (!window.topClientsData || !window.topDestinationsData) {
        console.warn("Chart data not loaded yet.");
        return;
    }

    renderDashboard(window.topClientsData, window.topDestinationsData);
}

document.addEventListener("DOMContentLoaded", checkDashboardCharts);
</script>

</body>
</html>
