<?php 
include 'db_connect.php';


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete My Account</title>
    <link rel="icon" href="images/logo.png" type="image/png" />
    <link rel="shortcut icon" href="images/logo.png" type="image/png" />
    <style>
        body {
            font-family: sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            background: #625d5d; 
        }
        form {
            background: #fff; 
            padding: 2rem; 
            border-radius: 12px; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.2); 
            width: 390px;
        }
        h1 {
            text-align: center; 
            margin-bottom: 15px; 
        }
        .buttons-grid {
            display: grid;
            justify-content: center;
            align-items: center;
        }
        .buttons-grid h4 {
            font-weight: lighter;
        }
        .buttons-row {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        button {
            padding: 11px 25px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            /* display: block;  */
            /* margin: 1rem auto;  */
            color: #fff; 
            font-size: 15px;
        }
        .btn-yes {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #d9534f;
        }
        .btn-yes:hover {
            transform: scale(1.05);
            background: #bb241fff;
        }
        .btn-no {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #5cb85c;
        }
        .btn-no:hover {
            transform: scale(1.05);
            background: #29b329ff;
        }

    </style>
</head>
<body>
    <form method="post">
        <h1>Delete my Account</h1>

        <?php 
        if (!empty($error)) echo "<div class='message' style='color:red;'>$error</div>";
        if (!empty($success)) echo "<div class='message' style='color:green;'>$success</div>";
        ?>

        <div class="buttons-grid">
            <h4>Are you sure you want to delete your account?</h4>
            <div class="buttons-row">
                <button type="submit" name="action" value="yes" class="btn-yes">Yes</button>

                <!-- <h4>Are you sure you want to delete your account?</h4> -->
                <button type="submit" name="action" value="no" class="btn-no">No</button>
            </div>
        </div>
    </form>
    
</body>
</html>