<?php
require __DIR__ . '/lib/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET' && $method !== 'POST') { http_response_code(405); exit('Method not allowed'); }

// GET reads ref from the query string; POST reads it from the hidden field. Never $_REQUEST.
$ref = trim((string)($method === 'POST' ? ($_POST['ref'] ?? '') : ($_GET['ref'] ?? '')));
$index = find_booking_index($_SESSION['bookings'], $ref);
$booking = $index === null ? null : $_SESSION['bookings'][$index];
$workshop = $booking ? find_workshop_for_booking($booking, $workshops) : null;

$errors = [];
$old = null; // values to show in the form

if ($method === 'POST') {
    // E02: request gate — CSRF first.
    if (!csrf_valid($_POST)) { http_response_code(403); exit('Invalid CSRF token'); }

    // E08: unknown ref -> do not create or touch anything.
    if ($booking === null || $workshop === null) {
        http_response_code(404);
    } else {
        // E03: reuse validate_booking(). Workshop comes from the stored booking (catalog), NOT from POST.
        $result = validate_booking([
            'full_name'   => $_POST['full_name'] ?? '',
            'email'       => $_POST['email'] ?? '',
            'quantity'    => $_POST['quantity'] ?? null,
            'workshop_id' => $workshop['id'],
        ], $workshops);

        if ($result['errors']) {
            // E06: show errors, keep typed input, Session untouched.
            http_response_code(422);
            $errors = $result['errors'];
            $old = $result['data'];
        } else {
            $d = $result['data'];
            // E04: update only this element; E05: recalculate amount from catalog fee x validated qty.
            $_SESSION['bookings'][$index]['full_name'] = $d['full_name'];
            $_SESSION['bookings'][$index]['email'] = $d['email'];
            $_SESSION['bookings'][$index]['quantity'] = $d['quantity'];
            $_SESSION['bookings'][$index]['amount'] = $workshop['fee'] * $d['quantity'];
            // keep ref, workshop_id, workshop title untouched
            $_SESSION['flash'] = 'Booking ' . $booking['ref'] . ' updated successfully.';
            redirect_to('dashboard.php'); // E07: 303 Post/Redirect/Get
        }
    }
}

// GET (E01) or failed POST: values to prefill
if ($booking !== null && $old === null) {
    $old = [
        'full_name' => $booking['full_name'],
        'email'     => $booking['email'],
        'quantity'  => $booking['quantity'],
    ];
} elseif ($booking === null && $method === 'GET') {
    http_response_code(404);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit booking</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= e($theme) ?>">
<main class="wrap">
<header>
<h1>Edit Booking</h1>
<nav>
<a href="index.php">Catalog</a>
<a href="dashboard.php">My bookings</a>
</nav>
</header>

<?php if ($booking === null || $workshop === null): ?>
<div class="panel">
<p class="alert">Booking not found.</p>
<p class="muted">Nothing was changed. The reference does not exist in this session (it may have been cleared).</p>
<a class="btn" href="dashboard.php">Back to My Bookings</a>
</div>
<?php else: ?>
<form method="post" action="booking_edit.php?ref=<?= urlencode($booking['ref']) ?>" class="panel form-stack" novalidate>
<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
<input type="hidden" name="ref" value="<?= e($booking['ref']) ?>">

<label>Reference <input value="<?= e($booking['ref']) ?>" disabled></label>
<label>Workshop <input value="<?= e($workshop['title']) ?> — <?= number_format($workshop['fee']) ?> VND / ticket" disabled></label>
<small class="field-hint">Reference and workshop cannot be changed.</small>

<label>Full name <input name="full_name" value="<?= e($old['full_name'] ?? '') ?>"></label>
<small class="error"><?= e($errors['full_name'] ?? '') ?></small>

<label>Email <input name="email" value="<?= e($old['email'] ?? '') ?>"></label>
<small class="error"><?= e($errors['email'] ?? '') ?></small>

<label>Tickets (1–4) <input name="quantity" type="number" min="1" max="4" value="<?= e($old['quantity'] ?? 1) ?>"></label>
<small class="error"><?= e($errors['quantity'] ?? '') ?></small>

<div class="actions">
<button>Save Changes (POST)</button>
<a class="btn btn-secondary" href="booking_detail.php?ref=<?= urlencode($booking['ref']) ?>">Cancel</a>
</div>
</form>
<p class="muted">Amount is recalculated on the server from the catalog fee × tickets.</p>
<?php endif; ?>
</main>
</body>
</html>
