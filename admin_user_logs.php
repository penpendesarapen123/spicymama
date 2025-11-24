<?php
require_once '_init.php';
require_once '_guards.php'; 

Guard::adminManagerCashierOnly();  // Allow access for Admins, Managers, and Cashiers

// Initialize flash message if it exists
$flashMessage = getFlashMessage('user_logs') ?? null;

// Get the current user's ID and role
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Fetch logs
$logs = UserLog::getUserLogs($userId, $userRole);
$inventoryLogs = Product::inventoryLogs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale System :: User Logs</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/admin.css">
    <link rel="stylesheet" href="./css/util.css">
    <link rel="stylesheet" href="./css/datatable.css">
    <link rel="stylesheet" type="text/css" href="./css/loader.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="./js/datatable.js"></script>
    <script src="./js/main.js"></script>
    <style>
        body {
            overflow: hidden;
        }
        .subtitle {
            font-size: 1.4rem;
            font-weight: bold;
            color: #495057;
        }
        .card {
            margin-bottom: 1.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        .card-body {
            padding: 20px;
            flex-grow: 1;
        }
        .table th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }
        .btn-blue {
            background-color: #2977ed;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn-blue:hover {
            background-color: #246BD5;
        }
        .btn-red {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn-red:hover {
            background-color: #B02A37;
        }

        .table-responsive {
            overflow-x: auto; /* Enable horizontal scrolling */
            margin-bottom: 0; /* Avoid extra spacing */
        }

        .dataTables_paginate {
            margin-top: 1rem;
            text-align: center;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 992px) {
            .table th, .table td {
                padding: 0.75rem; /* Reduce padding on smaller screens */
            }

            .card-body {
                padding: 1rem;
            }

            /* Make table more compact on small screens */
            .table-responsive {
                margin-bottom: 20px;
            }
        }

        @media (max-width: 576px) {
            .subtitle {
                font-size: 1.2rem;
            }

            .card-body {
                padding: 0.5rem;
            }

            /* Adjust pagination controls on very small screens */
            .dataTables_paginate {
                margin-top: 1rem;
                padding: 0.5rem;
            }

            /* Allow table to scroll horizontally */
            .table-responsive {
                padding-right: 15px; /* Ensure horizontal scroll doesn't overflow */
            }
        }
    </style>
</head>
<body>
    
    <?php require 'loader.php'; ?>
    
    <?php require 'templates/admin_header.php'; ?>

    <div class="d-flex flex-column flex-lg-row">
        <?php require 'templates/admin_navbar.php'; ?>
        <main class="flex-grow-1 p-3">
                    <?php if ($flashMessage): ?>
                        <div class="alert alert-info"><?= htmlspecialchars($flashMessage) ?></div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                <div class="subtitle">User Logs</div>
                                    <hr/>
                                    <div class="table-responsive">
                                    <table id="userLogsTable" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>User Name</th>
                                                <th>Role</th>
                                                <th>Login Time</th>
                                                <th>Logout Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($logs as $log): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($log->username) ?></td>
                                                    <td><?= htmlspecialchars($log->role) ?></td>
                                                    <td><?= htmlspecialchars($log->loginTime) ?></td>
                                                    <td><?= htmlspecialchars($log->logoutTime) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    </div> <!--end table-responsive-->
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div><!--end col-md-12-->
                    </div><!--end row-->
        </main>
    </div>
    
<script src="./js/loader.js"></script>

<script>
        // Example: Show loader on page load and hide it after content is loaded
        showLoader();

        window.addEventListener('load', () => {
            hideLoader();
        });
    </script>

    <script>
        // Initialize datatable with search functionality and fixed height
        var dataTable = new simpleDatatables.DataTable("#userLogsTable", {
            searchable: true,
        });

    </script>

</body>
</html>
