<?php
require_once '_guards.php';
require_once '_init.php';
require_once '_helper.php';
require_once 'models/Attendance.php';

date_default_timezone_set('Asia/Manila'); // Set the timezone to Philippines

// Handle flash messages
$flashMessage = getFlashMessage('add_attendance');

// Get all attendance records
$attendanceRecords = Attendance::all();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Attendance</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link rel="stylesheet" type="text/css" href="./css/datatable.css">
    <script src="./js/datatable.js"></script>
    <script src="./js/main.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{
            overflow: hidden;
            
        }
        .btn-blue {
            background-color: #2977ed;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        .btn-red {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        .btn-red:hover{
            background-color: #B02A37;
        }
        
        .btn-blue:hover {
            background-color: #246BD5;
        }

        .subtitle {
            font-size: 1.4rem;
            font-weight: bold;
        }

        .card-body {
            padding: 20px;
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>
<body>
    <?php require 'templates/admin_header.php'; ?>

    <div class="flex">
        <?php require 'templates/admin_navbar.php'; ?>
        <main>
        <div class="wrapper">
                <div class="w-100">
                    <div class="subtitle">Attendance</div>
                    <hr/>
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="subtitle">Record Attendance</div>
                            <hr/>
                            <?php if ($flashMessage): ?>
                                <div class="alert alert-info"><?= htmlspecialchars($flashMessage) ?></div>
                            <?php endif; ?>
                            <form method="post" action="api/attendance_controller.php" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="employeeName" class="form-label" style="font-size: 1.2rem;">Employee Name</label>
                                    <input name="name" type="text" class="form-control" id="employeeName" placeholder="Employee Name" required>
                                </div>
                                <button type="submit" name="action" value="timein" class="btn btn-primary btn-blue">Time In</button>
                                <button type="submit" name="action" value="timeout" class="btn btn-primary btn-red">Time Out</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <?php if ($currentUser && ($currentUser->role === ROLE_ADMIN || $currentUser->role === ROLE_MANAGER)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="subtitle">Attendance Records</div>
                            <hr/>
                            <table id="attendanceTable"class="table">
                                <thead>
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendanceRecords as $record): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($record->name) ?></td>
                                            <td><?= htmlspecialchars($record->date) ?></td>
                                            <td><?= htmlspecialchars($record->timein) ?></td>
                                            <td><?= htmlspecialchars($record->timeout) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            </div>
        </div>
        </main>
    </div>
</body>
</html>
<script>
    var dataTable = new simpleDatatables.DataTable("#attendanceTable", {
            searchable: true
        });

</script>