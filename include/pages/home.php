<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../../app/models/models.php';
$model = new models();

$role = $_SESSION['role'] ?? '';
$get_archive_logs = $model->getArchiveLogs($role);
?>

<style>
.archive-log {
  font-size: 14px;
  background: #f8f9fa;
  border-left: 4px solid #0d6efd;
  padding: 10px 15px;
  margin-bottom: 8px;
  border-radius: 5px;
}
.archive-log i {
  color: #0d6efd;
}
</style>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <h5 class="mb-3 text-primary"><i class="bi bi-archive"></i> Archive Activity Summary</h5>

    <?php if (!empty($get_archive_logs)): ?>
      <?php foreach ($get_archive_logs as $log): ?>
        <div class="archive-log">
          <i class="bi bi-check-circle-fill"></i>
          <strong><?= htmlspecialchars($log['name'] ?? '—') ?></strong>,
          <?= htmlspecialchars($log['brand'] ?? '—') ?>,
          <?= htmlspecialchars($log['model'] ?? '—') ?>,
          <?= htmlspecialchars($log['serial_number'] ?? '—') ?>,
          <em><?= htmlspecialchars($log['category'] ?? '—') ?></em>
          — successfully moved to archive by
          <strong><?= htmlspecialchars($log['username'] ?? '—') ?></strong>
          on <span class="text-muted"><?= htmlspecialchars($log['archived_at'] ?? '—') ?></span>.
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-muted fst-italic">
        <i class="bi bi-inbox"></i> No archive records found for your role.
      </div>
    <?php endif; ?>
  </div>
</div>
