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
$sql = "SELECT DestinationName, DestinationPrice, StartDate, EndDate FROM destination WHERE DestinationID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $destinationId);
$stmt->execute();
$result = $stmt->get_result();
$dest = $result->fetch_assoc();

if (!$dest) die("Destination not found.");

$destName = $dest['DestinationName'];
$productPrice = $dest['DestinationPrice'];
$StartDate = $dest['StartDate'];
$EndDate = $dest['EndDate'];

// ✅ Total is just product price now
$final_price = $productPrice;

$paymentNumber = '#' . rand(10000, 99999);
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

    .main-card {
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
      width: 100%;
    }

    .main-body {
      flex: 1 1 auto;
      padding: 1.5rem 1.5rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    .table td,
    .table th {
      border-bottom: 0;
      border-top: 1px solid #edf2f9;
      padding: 0.75rem 1rem;
    }

    .text-purple,
    .text-purple:hover {
      color: #000000;
    }

    /* ✅ Mobile fixes */
    @media (max-width: 576px) {
      .btn {
        display: block;
        width: 100%;
        text-align: center;
        margin: 1rem auto;
      }

      .text-md-end {
        text-align: left !important;
      }

      .main-body {
        padding: 1rem;
      }

      .details-table td,
      .details-table th {
        font-size: 14px;
        word-break: break-word;
      }

      .main-card {
        max-width: 100%;
        margin: 0 10px;
      }
    }
  </style>
</head>

<body>
  <div class="main-container mt-6 mb-7">
    <div class="row justify-content-center">
      <div class="col-lg-12 col-xl-7">
        <div class="main-card">
          <div class="main-body p-5">
            <h2>Hello <?php echo htmlspecialchars($clientName); ?>,</h2>
            <p class="fs-sm">
              This is the receipt for a payment of
              <strong><?php echo '€' . number_format($productPrice, 2); ?></strong> you made to Atlas Travel.
            </p>

            <div class="payment-info border-top border-gray-200 pt-4 mt-4">
              <div class="row">
                <div class="col-md-6">
                  <div class="text-muted mb-2">Payment No.</div>
                  <strong><?php echo $paymentNumber; ?></strong>
                </div>
                <div class="col-md-6 text-md-end">
                  <div class="text-muted mb-2">Payment Date</div>
                  <?php $paymentDate = date("d/m/Y"); ?>
                  <strong><?php echo $paymentDate; ?></strong>
                </div>
              </div>
            </div>

            <div class="client-info border-top border-gray-200 mt-4 py-4">
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

            <table class="details-table table border-bottom border-gray-200 mt-3">
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
                  <td class="px-0">Start Date</td>
                  <td class="text-end px-0"><?php echo htmlspecialchars($StartDate); ?></td>
                </tr>
                <tr>
                  <td class="px-0">End Date</td>
                  <td class="text-end px-0"><?php echo htmlspecialchars($EndDate); ?></td>
                </tr>
                <tr>
                  <td class="px-0">Destination Price</td>
                  <td class="text-end px-0"><?php echo '€' . number_format($productPrice, 2); ?></td>
                </tr>
              </tbody>
            </table>

            <div class="total mt-5">
              <div class="d-flex justify-content-end mt-3">
                <h5 class="me-3">Total:</h5>
                <h5 class="text-success"><?php echo '€' . number_format($final_price, 2); ?></h5>
              </div>
            </div>
          </div>

          <!-- ✅ Button is centered on desktop & full-width on mobile -->
          <div class="text-center my-3 px-3">
            <a href="index.php" class="btn btn-primary text-uppercase w-100 w-md-auto">Home</a>
          </div>

          <!-- Download bill as PDF -->
          <div class="text-center my-3 px-3">
            <a href="generatePDF.php?id=<?php echo $destinationId; ?>" class="btn btn-success text-uppercase w-100 w-md-auto">Download PDF</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>

</html>
