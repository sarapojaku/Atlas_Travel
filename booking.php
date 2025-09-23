<?php
// booking.php
// Shows destination info and booking form. AJAX submits to book_form.php

include 'db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid destination ID");
}

$destinationId = intval($_GET['id']);

// Fetch destination details
$sql = "SELECT DestinationID, DestinationName, DestinationInfo, DestinationPlaces, DestinationImage, DestinationPrice, StartDate, EndDate
        FROM Destination
        WHERE DestinationID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $destinationId);
$stmt->execute();
$result = $stmt->get_result();
$dest = $result->fetch_assoc();
$stmt->close();

if (!$dest) {
    die("Destination not found.");
}

$imagePath = "uploads/";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - <?php echo htmlspecialchars($dest['DestinationName']); ?></title>
    <link rel="icon" href="images/logo.png" type="image/png" />
    <link rel="shortcut icon" href="images/logo.png" type="image/png" />
    <style>
    :root {
        --bg: #625d5d;
        --fg: #000000;
        --muted: #767778;
        --border: #e5e7eb;
        --card: #ffffff;
        --primary: #2563eb;
        --primary-10: #e0ecff;
        --shadow: 0 0 15px #b3acac;
        --radius: 16px;
    }
    .book-form{
        margin-top: 1rem;
        display: grid;
        gap: 0.9rem;
        margin-left: 200px;
        margin-right: 200px;
    }
    h1 { text-align: center; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .book-form input, .book-form select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #ffffff;
        font-size: 15px;
    }
    .book-form button {
        background: #625d5d;
        color: #ffffff;
        border-radius: 10px;
        padding: 10px;
        font-size: 15px;
        font-weight: bold;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .book-form button:hover {
        background: #767778;
        transform: scale(1.03);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    #book-response { padding: 10px; border-radius: 5px; transition: opacity 0.3s ease; margin-top: 10px; }
    .first { display:flex; justify-content:center; align-items:flex-start; gap:50px; padding:40px 20px; flex-wrap:wrap; }
    .info { max-width:450px; text-align:center; }
    .info img { display:block; margin:0 auto; width:300px; height:300px; border-radius:20px; object-fit:cover; margin-bottom:10px; }
    .info p { text-align:left; margin-bottom:10px; }
    .info ul { text-align:left; list-style:none; padding-left:0; margin-bottom:10px; }
    .card { padding: 12px; border: 1px solid var(--border); border-radius: 12px; background: #fff; }
    @media screen and (max-width:900px){
        .first { flex-direction:column; align-items:center; gap:20px; padding:20px; }
        .book-form { margin-left:20px; margin-right:20px; }
        .form-row { grid-template-columns: 1fr; }
    }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<section id="infos" class="first">
    <div class="info">
        <h2><?php echo htmlspecialchars($dest['DestinationName']); ?></h2>
        <img src="<?php echo $imagePath . htmlspecialchars($dest['DestinationImage']); ?>" alt="<?php echo htmlspecialchars($dest['DestinationName']); ?>" />
        <p><?php echo nl2br(htmlspecialchars($dest['DestinationInfo'])); ?></p>

        <?php if (!empty($dest['DestinationPlaces'])): ?>
            <h3 style="text-align:left;">Top Places to Visit:</h3>
            <ul style="list-style:disc; padding-left:20px; margin-bottom:5px;">
                <?php $places = explode(',', $dest['DestinationPlaces']);
                foreach ($places as $place): ?>
                    <li><?php echo htmlspecialchars(trim($place)); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <ul style="text-align:left;">
            <li><strong>Date:</strong> <?php echo date("d/m/Y", strtotime($dest['StartDate'])) . " - " . date("d/m/Y", strtotime($dest['EndDate'])); ?></li>
            <li><strong>Price: €</strong><?php echo number_format($dest['DestinationPrice'], 2); ?></li>
        </ul>
    </div>
</section>

<section id="booking" class="second">
    <div class="container book">
        <div class="second-head">
            <h1>Book now!</h1>

            <form id="book-form" class="book-form" action="book_form.php?id=<?php echo $destinationId; ?>" method="post" autocomplete="off">
                <div class="form-row">
                    <input type="text" id="fname" name="ClientName" placeholder="First Name" required>
                    <input type="text" id="lname" name="ClientSurname" placeholder="Surname" required>
                </div>

                <div class="card">
                    <h2>Card Details</h2>
                    <!-- Note: type="month" returns YYYY-MM. We'll format server-side to MM/YYYY -->
                    <input type="text" id="card-number" name="number" inputmode="numeric" placeholder="Card Number (numbers only)" minlength="12" maxlength="19" required>
                    <input type="month" id="exDate" name="exDate" placeholder="MM/YYYY" required>
                    <input type="password" id="cvv" name="cvv" placeholder="CVV (will NOT be stored)" minlength="3" maxlength="4" required>
                </div>

                <button type="submit" id="submit-btn">Submit Payment</button>
                <div id="book-response" aria-live="polite"></div>
            </form>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<script>
document.getElementById('book-form').addEventListener('submit', async function(e){
    e.preventDefault();
    const respEl = document.getElementById('book-response');
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    respEl.style.opacity = 0.6;
    respEl.innerHTML = 'Processing...';

    // Collect form data
    const form = e.target;
    const formData = new FormData(form);

    // Convert FormData to JSON-friendly payload
    const payload = {};
    for (const [k,v] of formData.entries()) payload[k] = v;

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.status && data.status === 'success') {
            respEl.style.color = 'green';
            respEl.innerHTML = data.message;
            // Clear card fields (but keep names for convenience)
            document.getElementById('card-number').value = '';
            document.getElementById('exDate').value = '';
            document.getElementById('cvv').value = '';
        } else {
            respEl.style.color = 'red';
            respEl.innerHTML = (data && data.message) ? data.message : 'An error occurred.';
        }
    } catch (err) {
        respEl.style.color = 'red';
        respEl.innerHTML = 'Network or server error. Try again.';
        console.error(err);
    } finally {
        submitBtn.disabled = false;
        respEl.style.opacity = 1;
    }
});
</script>
</body>
</html>
