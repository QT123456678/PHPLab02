<?php
require __DIR__ . '/lib/bootstrap.php';

// Read-only page: GET only, never writes to $_SESSION["bookings"].
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') { http_response_code(405); exit('Method not allowed'); }

$ref = trim((string)($_GET['ref'] ?? ''));
$index = find_booking_index($_SESSION['bookings'], $ref);
$booking = $index === null ? null : $_SESSION['bookings'][$index];
$workshop = $booking ? find_workshop_for_booking($booking, $workshops) : null;

if ($booking === null) {
    http_response_code(404);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Booking detail</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= e($theme) ?>">
<main class="wrap">
<header>
<h1>Booking Detail</h1>
<nav>
<a href="index.php">Catalog</a>
<a href="dashboard.php">My bookings</a>
</nav>
</header>

<?php if ($booking === null): ?>
<div class="panel">
<p class="alert">Booking not found.</p>
<p class="muted">The reference you opened does not exist in this session (it may have been cleared or mistyped).</p>
<a class="btn" href="dashboard.php">Back to My Bookings</a>
</div>
<?php else: ?>
<div class="panel">
<dl class="detail">
<dt>Reference</dt><dd><?= e($booking['ref']) ?></dd>
<dt>Full name</dt><dd><?= e($booking['full_name']) ?></dd>
<dt>Email</dt><dd><?= e($booking['email']) ?></dd>
<dt>Workshop</dt><dd><?= e($booking['workshop']) ?></dd>
<dt>Quantity</dt><dd><?= e($booking['quantity']) ?></dd>
<dt>Unit fee</dt><dd><?= $workshop ? number_format($workshop['fee']) . ' VND' : 'N/A' ?></dd>
<dt>Amount</dt><dd><strong><?= number_format($booking['amount']) ?> VND</strong></dd>
</dl>
</div>
<div class="actions">
<a class="btn btn-secondary" href="dashboard.php">Back to My Bookings</a>
<a class="btn" href="booking_edit.php?ref=<?= urlencode($booking['ref']) ?>">Edit This Booking</a>
</div>
<?php endif; ?>
</main>
</body>
</html>
