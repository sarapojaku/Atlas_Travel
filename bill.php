<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid destination ID");
}

$destinationId = intval($_GET['id']);

// ✅ Get client info from session
$clientName    = isset($_SESSION['clientName']) ? $_SESSION['clientName'] : '';
$clientSurname = isset($_SESSION['clientSurname']) ? $_SESSION['clientSurname'] : '';
$clientEmail   = isset($_SESSION['clientEmail']) ? $_SESSION['clientEmail'] : '';

// Fetch destination details
$sql = "SELECT DestinationName, DestinationPrice FROM destination WHERE DestinationID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $destinationId);
$stmt->execute();
$result = $stmt->get_result();
$dest = $result->fetch_assoc();

if (!$dest) die("Destination not found.");

$destName = $dest['DestinationName'];
$productPrice = $dest['DestinationPrice'];

// Tax + total
$tax = 0.2 * $productPrice;
$final_price = $productPrice + $tax;

$paymentNumber = '#' . rand(10000, 99999); // Moved here so can reuse in HTML
$paymentDate = date("M/d/Y");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bill</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="icon" href="images/logo.png" type="image/png" />
  <link rel="shortcut icon" href="images/logo.png" type="image/png" />
  <style>
    body {
      margin-top: 20px;
      background: #625d5d;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial,
        "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    .card-footer-btn {
      display: flex;
      align-items: center;
      border-top-left-radius: 0 !important;
      border-top-right-radius: 0 !important;
    }

    .text-uppercase-bold-sm {
      text-transform: uppercase !important;
      font-weight: 500 !important;
      letter-spacing: 2px !important;
      font-size: 0.85rem !important;
    }

    .hover-lift-light {
      transition: box-shadow 0.25s ease, transform 0.25s ease, color 0.25s ease,
        background-color 0.15s ease-in;
    }

    .justify-content-center {
      justify-content: center !important;
    }

    .btn-group-lg>.btn,
    .btn-lg {
      padding: 0.8rem 1.85rem;
      font-size: 1.1rem;
      border-radius: 0.3rem;
    }

    .btn-dark {
      color: #fff;
      background-color: #1e2e50;
      border-color: #1e2e50;
    }

    .card {
      position: relative;
      display: flex;
      flex-direction: column;
      min-width: 0;
      word-wrap: break-word;
      background-color: #fff;
      background-clip: border-box;
      border: 1px solid rgba(30, 46, 80, 0.09);
      border-radius: 0.25rem;
      box-shadow: 0 20px 27px 0 rgb(0 0 0 / 5%);
      max-width: 600px;
      margin: auto;
    }

    .p-5 {
      padding: 3rem !important;
    }

    .card-body {
      flex: 1 1 auto;
      padding: 1.5rem 1.5rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    tbody,
    td,
    tfoot,
    th,
    thead,
    tr {
      border-color: inherit;
      border-style: solid;
      border-width: 0;
    }

    .table td,
    .table th {
      border-bottom: 0;
      border-top: 1px solid #edf2f9;
      padding: 0.75rem 1rem;
    }

    .table> :not(caption)>*>* {
      background-color: var(--bs-table-bg);
      border-bottom-width: 1px;
      box-shadow: inset 0 0 0 9999px var(--bs-table-accent-bg);
    }

    .text-purple,
    .text-purple:hover {
      color: #000000;
    }
  </style>
</head>

<body>
  <div class="container mt-6 mb-7">
    <div class="row justify-content-center">
      <div class="col-lg-12 col-xl-7">
        <div class="card">
          <div class="card-body p-5">
            <h2>Hello <?php echo htmlspecialchars($clientName); ?>,</h2>
            <p class="fs-sm">
              This is the receipt for a payment of
              <strong><?php echo '€' . number_format($productPrice, 2); ?></strong> you made to Atlas Travel.
            </p>

            <div class="border-top border-gray-200 pt-4 mt-4">
              <div class="row">
                <div class="col-md-6">
                  <div class="text-muted mb-2">Payment No.</div>
                  <strong><?php echo $paymentNumber; ?></strong>
                </div>
                <div class="col-md-6 text-md-end">
                  <div class="text-muted mb-2">Payment Date</div>
                  <?php
                  $paymentDate = date("d/m/Y");
                  ?>
                  <strong><?php echo $paymentDate; ?></strong>
                </div>
              </div>
            </div>

            <div class="border-top border-gray-200 mt-4 py-4">
              <div class="row">
                <div class="col-md-6">
                  <div class="text-muted mb-2">Client</div>
                  <strong><?php echo htmlspecialchars($clientName . ' ' . $clientSurname); ?></strong>
                  <p class="fs-sm">
                    <br />
                    <a href="mailto:<?php echo htmlspecialchars($clientEmail); ?>" class="text-purple"><?php echo htmlspecialchars($clientEmail); ?></a>
                  </p>
                </div>
                <div class="col-md-6 text-md-end">
                  <div class="text-muted mb-2">Payment To</div>
                  <strong>Atlas Travel</strong>
                  <p class="fs-sm">
                    <br />
                    <a href="mailto:travelatlas24@gmail.com" class="text-purple">travelatlas24@gmail.com</a>
                  </p>
                </div>
              </div>
            </div>

            <table class="table border-bottom border-gray-200 mt-3">
              <thead>
                <tr>
                  <th scope="col" class="fs-sm text-dark text-uppercase-bold-sm px-0">
                    Description
                  </th>
                  <th scope="col" class="fs-sm text-dark text-uppercase-bold-sm text-end px-0">
                    DETAILS
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="px-0">Destination Name</td>
                  <td class="text-end px-0"><?php echo htmlspecialchars($destName); ?></td>
                </tr>
                <tr>
                  <td class="px-0">Destination Price</td>
                  <td class="text-end px-0"><?php echo '€' . number_format($productPrice, 2); ?></td>
                </tr>
                <tr>
                  <td class="px-0">Added Tax</td>
                  <td class="text-end px-0"><?php echo '€' . number_format($tax, 2); ?></td>
                </tr>
              </tbody>
            </table>

            <div class="mt-5">
              <div class="d-flex justify-content-end mt-3">
                <h5 class="me-3">Total:</h5>
                <h5 class="text-success"><?php echo '€' . number_format($final_price, 2); ?></h5>
              </div>
            </div>
          </div>

          <button class="btn btn-primary text-uppercase">
            <a href='index.php' style="color: inherit; text-decoration: none;">Home</a>
          </button>

        </div>
      </div>
    </div>
  </div>
</body>

</html>
