<?php
require_once '../app/models/models.php';
$model = new models();

$condition = $_GET['condition'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$records = $model->filterDevices($condition, $status, $category, $from_date, $to_date);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Device Records Report</title>
<link href="../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/pr.css" />
</head>

<body onload="window.print()">
<div class="container mt-4">
    <div class="text-center mb-4">
        <h3>Device Records Report</h3>
        <?php if (!empty($from_date) && !empty($to_date)): ?>
            <p class="text-muted mb-0">
                Filtered from <strong><?= htmlspecialchars($from_date) ?></strong> 
                to <strong><?= htmlspecialchars($to_date) ?></strong>
            </p>
        <?php endif; ?>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Model</th>
                <th>Brand</th>
                <th>Serial</th>
                <th>Category</th>
                <th>Condition</th>
                <th>Status</th>
                <th>PR</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($records): ?>
                <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= htmlspecialchars($r['model']) ?></td>
                        <td><?= htmlspecialchars($r['brand']) ?></td>
                        <td><?= htmlspecialchars($r['serial_number']) ?></td>
                        <td><?= htmlspecialchars($r['category']) ?></td>
                        <td><?= htmlspecialchars($r['device_condition']) ?></td>
                        <td><?= htmlspecialchars($r['current_status']) ?></td>
                        <td><?= htmlspecialchars($r['pr']) ?></td>
                        <td><?= htmlspecialchars($r['quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center text-muted">No matching records</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <footer>
        Generated on <?= date('F j, Y, g:i a') ?> by IMS System
    </footer>
</div>
</body>
</html>
