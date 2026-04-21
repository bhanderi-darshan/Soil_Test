<?php
session_start();
include "db.php";

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success_msg = '';
$error_msg = '';

// Process form submission to update hardware_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_hardware'])) {
    $user_id_to_update = (int)$_POST['user_id'];
    $hardware_id = mysqli_real_escape_string($conn, trim($_POST['hardware_id']));

    // Update query
    $update_sql = "UPDATE users SET hardware_id = '$hardware_id' WHERE id = $user_id_to_update AND role='user'";
    if (mysqli_query($conn, $update_sql)) {
        $success_msg = "Hardware ID successfully assigned to user.";
    } else {
        $error_msg = "Failed to update Hardware ID.";
    }
}

// Fetch all regular users
$query = "SELECT id, username, hardware_id FROM users WHERE role = 'user' ORDER BY id DESC";
$result = mysqli_query($conn, $query);

$users = [];
$total_users = 0;
$assigned_count = 0;

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
        $total_users++;
        if (!empty($row['hardware_id'])) {
            $assigned_count++;
        }
    }
}
$pending_count = $total_users - $assigned_count;

// Fetch all queries from index.php
$queries_result = mysqli_query($conn, "SELECT id, name, email, message, created_at FROM queries ORDER BY id DESC");
$contact_queries = [];
if ($queries_result) {
    while ($row = mysqli_fetch_assoc($queries_result)) {
        $contact_queries[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SmartSoil Analyzer</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e293b;
            --secondary: #64748b;
            --bg-body: #f8fafc;
            --bg-sidebar: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-hover: #1e293b;
            --accent: #10b981;
            --accent-hover: #059669;
            --accent-light: #d1fae5;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --card-bg: #ffffff;
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { 
            background: linear-gradient(rgba(248, 250, 252, 0.94), rgba(248, 250, 252, 0.94)), url('images/farm_bg.png') no-repeat center center fixed; 
            background-size: cover;
            color: var(--primary); 
            display: flex; 
            min-height: 100vh; 
            overflow-x: hidden; 
        }
        h1, h2, h3, h4, .brand { font-family: 'Outfit', sans-serif; }

        /* Sidebar Navigation */
        .sidebar {
            width: 280px; background-color: var(--bg-sidebar); color: var(--sidebar-text);
            display: flex; flex-direction: column; position: fixed; height: 100vh; top: 0; left: 0;
            box-shadow: 4px 0 25px rgba(0,0,0,0.1); z-index: 1000;
        }
        .brand {
            padding: 30px; font-size: 1.8rem; font-weight: 800; color: white;
            display: flex; align-items: center; gap: 10px; letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .brand span { color: var(--accent); }
        .nav-menu { padding: 30px 15px; flex: 1; display: flex; flex-direction: column; gap: 8px; }
        .nav-menu a {
            color: var(--sidebar-text); text-decoration: none; padding: 14px 20px;
            border-radius: 10px; font-weight: 500; font-size: 0.95rem; display: flex; align-items: center; gap: 12px;
            transition: all 0.3s ease;
        }
        .nav-menu a:hover, .nav-menu a.active {
            background-color: var(--sidebar-hover); color: white;
        }
        .nav-menu a.active {
            background-color: rgba(16, 185, 129, 0.15); color: var(--accent); border-right: 4px solid var(--accent);
        }
        .nav-menu a.logout { margin-top: auto; color: #ef4444; }
        .nav-menu a.logout:hover { background-color: rgba(239, 68, 68, 0.1); color: #f87171; }

        /* Main Content */
        .main-content {
            margin-left: 280px; flex: 1; padding: 40px; display: flex; flex-direction: column; gap: 35px;
        }

        /* Top Header */
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .page-title h2 { font-size: 2.2rem; color: var(--primary); font-weight: 800; line-height: 1.2; }
        .page-title p { color: var(--secondary); font-size: 1.05rem; margin-top: 6px; }

        .admin-profile { display: flex; align-items: center; gap: 15px; background: white; padding: 10px 20px; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--border); }
        .admin-avatar { width: 40px; height: 40px; background: linear-gradient(135deg, var(--accent), var(--accent-hover)); border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; }
        .admin-info strong { display: block; font-size: 0.95rem; color: var(--primary); }
        .admin-info span { font-size: 0.8rem; color: var(--secondary); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }

        /* Alerts */
        .alert { padding: 18px 25px; border-radius: 12px; font-weight: 600; display: flex; align-items: center; font-size: 0.95rem; animation: slideInFade 0.5s ease; }
        .alert-success { background-color: var(--accent-light); color: var(--accent-hover); border: 1px solid #a7f3d0; }
        .alert-error { background-color: var(--danger-light); color: #b91c1c; border: 1px solid #fecaca; }
        @keyframes slideInFade { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* Cards */
        .card { background: var(--card-bg); border-radius: 16px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04); border: 1px solid var(--border); overflow: hidden; display: flex; flex-direction: column; }
        .card-header { padding: 25px 30px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .card-header h3 { font-size: 1.35rem; color: var(--primary); font-weight: 700; display: flex; align-items: center; gap: 10px; }
        
        /* Query Grid */
        .query-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; padding: 25px; background: #fdfdfd; }
        .query-card { background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; gap: 12px; transition: all 0.3s; box-shadow: 0 2px 10px rgba(0,0,0,0.02); position: relative; overflow: hidden; }
        .query-card::before { content:''; position:absolute; top:0; left:0; width:4px; height:100%; background:var(--warning); border-radius: 4px 0 0 4px; }
        .query-card:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(0,0,0,0.06); border-color: #cbd5e1; }
        .q-head { display: flex; justify-content: space-between; align-items: flex-start; }
        .q-name { font-size: 1.1rem; font-weight: 700; color: var(--primary); }
        .q-time { font-size: 0.75rem; font-weight: 600; color: var(--secondary); background: #f1f5f9; padding: 4px 10px; border-radius: 20px; }
        .q-email { font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; color: var(--secondary); margin-bottom: 5px; }
        .q-email a { color: #3b82f6; text-decoration: none; font-weight: 500; }
        .q-email a:hover { text-decoration: underline; }
        .q-body { font-size: 0.95rem; color: #475569; line-height: 1.6; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9; }

        /* Table Design */
        .search-box { padding: 10px 20px; border: 1px solid var(--border); border-radius: 30px; font-size: 0.9rem; outline: none; width: 300px; transition: all 0.3s; background: #f8fafc; }
        .search-box:focus { border-color: var(--accent); background: white; box-shadow: 0 0 0 3px var(--accent-light); }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th { background: #f8fafc; color: var(--secondary); text-transform: uppercase; font-size: 0.78rem; letter-spacing: 1px; padding: 18px 30px; text-align: left; font-weight: 700; border-bottom: 1px solid var(--border); }
        td { padding: 20px 30px; border-bottom: 1px solid var(--border); vertical-align: middle; color: var(--primary); font-size: 0.95rem; background: white; transition: background 0.2s; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #fcfdfd; }

        .user-cell { display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 42px; height: 42px; border-radius: 12px; background: #f1f5f9; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; text-transform: uppercase; border: 1px solid var(--border); }
        .user-details strong { display: block; color: var(--primary); font-size: 1rem; font-weight: 700; }
        .user-details span { font-size: 0.8rem; color: var(--secondary); font-weight: 500; }

        .form-inline { display: flex; align-items: center; gap: 10px; }
        .input-hardware { padding: 10px 15px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; width: 220px; transition: all 0.3s; outline: none; background: #f8fafc; font-family: 'Inter', sans-serif; }
        .input-hardware:focus { border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px var(--accent-light); }

        .btn-update { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s; }
        .btn-update:hover { background: var(--accent); transform: translateY(-2px); box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3); }

        .badge-none { background: #f1f5f9; color: #64748b; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; border: 1px solid #e2e8f0; }
        .badge-assigned { background: var(--accent-light); color: var(--accent-hover); padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 6px; }
        .badge-assigned::before { content: ''; display:block; width:6px; height:6px; border-radius:50%; background: var(--accent-hover); }

        /* Empty State */
        .empty-state { text-align: center; padding: 50px; color: var(--secondary); font-size: 1.1rem; display: flex; flex-direction: column; align-items: center; gap: 15px; }
        .empty-icon { font-size: 3rem; opacity: 0.5; }

        @media(max-width: 1024px) {
            .sidebar { width: 80px; }
            .brand { padding: 25px 0; justify-content: center; font-size: 1.2rem; }
            .brand span { display: none; }
            .nav-menu a { justify-content: center; padding: 15px; }
            .nav-menu span { display: none; }
            .main-content { margin-left: 80px; }
        }
        @media(max-width: 768px) {
            .card-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .search-box { width: 100%; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            .top-header { flex-direction: column; align-items: flex-start; gap: 20px; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <span>Admin</span> SmartSoil
        </div>
        <div class="nav-menu">
            <a href="#" class="active"><span>Dashboard</span></a>
            <a href="index.php"><span>View Site</span></a>
            <!-- Spacing -->
            <a href="logout.php" class="logout"><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        
        <!-- Header -->
        <div class="top-header">
            <div class="page-title">
                <h2>Admin Control Center</h2>
                <p>Manage farmers, hardware assignments, and incoming support queries.</p>
            </div>
            <div class="admin-profile">
                <div class="admin-avatar">A</div>
                <div class="admin-info">
                    <strong>Administrator</strong>
                    <span>System Access</span>
                </div>
            </div>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success">✓ &nbsp; <?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-error">⚠ &nbsp; <?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <!-- Queries Section -->
        <div class="card">
            <div class="card-header">
                <h3>Incoming Support Queries</h3>
            </div>
            <div class="query-grid">
                <?php if (count($contact_queries) > 0): ?>
                    <?php foreach ($contact_queries as $q): ?>
                        <div class="query-card">
                            <div class="q-head">
                                <span class="q-name"><?php echo htmlspecialchars($q['name']); ?></span>
                                <span class="q-time"><?php echo date('M d, y • g:i A', strtotime($q['created_at'])); ?></span>
                            </div>
                            <div class="q-email">
                                Email: <a href="mailto:<?php echo htmlspecialchars($q['email']); ?>"><?php echo htmlspecialchars($q['email']); ?></a>
                            </div>
                            <div class="q-body">
                                <?php echo nl2br(htmlspecialchars($q['message'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1;">
                        <div class="empty-state">
                            <div>No incoming support queries at the moment.</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hardware Management Data Table -->
        <div class="card">
            <div class="card-header">
                <h3>Farmer Hardware Directory</h3>
                <input type="text" id="searchInput" class="search-box" placeholder="Search farmers by name or ID..." onkeyup="filterTable()">
            </div>
            
            <table id="usersTable">
                <thead>
                    <tr>
                        <th width="10%">Reg. ID</th>
                        <th width="30%">Farmer Identity</th>
                        <th width="20%">Hardware Network</th>
                        <th width="40%">Hardware Control Panel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $row): 
                            $initial = substr($row['username'], 0, 1);
                        ?>
                            <tr>
                                <td><strong style="color:var(--secondary);">#<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar"><?php echo htmlspecialchars($initial); ?></div>
                                        <div class="user-details">
                                            <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                                            <span>Active User</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($row['hardware_id'])): ?>
                                        <span class="badge-assigned"><?php echo htmlspecialchars($row['hardware_id']); ?></span>
                                    <?php else: ?>
                                        <span class="badge-none">No Link</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="form-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <input type="text" name="hardware_id" class="input-hardware" placeholder="Enter Module ID (e.g. SN-054)" value="<?php echo htmlspecialchars($row['hardware_id'] ?? ''); ?>">
                                        <button type="submit" name="assign_hardware" class="btn-update">Assign</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div>There are no farmers currently registered in the database.</div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

<script>
function filterTable() {
    var input, filter, table, tr, td, i;
    input = document.getElementById("searchInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("usersTable");
    tr = table.getElementsByTagName("tr");

    for (i = 1; i < tr.length; i++) {
        var tdId = tr[i].getElementsByTagName("td")[0];
        var tdProfile = tr[i].getElementsByTagName("td")[1];
        
        if (tdId || tdProfile) {
            var txtId = tdId.textContent || tdId.innerText;
            var txtProfile = tdProfile.textContent || tdProfile.innerText;
            
            if (txtId.toUpperCase().indexOf(filter) > -1 || txtProfile.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }       
    }
}
</script>
</body>
</html>
