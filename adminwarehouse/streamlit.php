
<?php
include('../konekdb.php');
session_start();

// Check authorization
$username = $_SESSION['username'];
$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Adminwarehouse'");
$user = mysqli_fetch_array($cekuser);
if($user['jmluser'] == "0") {
    header("location:../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">

    <title>Streamlit Integration</title>
</head>
<body>
    <p>
        <a href="dashboard.php" style="display:inline-block; margin-right:15px;">
            <img width="50" height="50" src="https://img.icons8.com/ios-glyphs/30/home-page--v1.png" alt="home-page--v1" alt="Dashboard" style="vertical-align:middle; width:20px; height:20px; margin-right:5px;">
        </a>
        <a href="../logout.php" style="display:inline-block;">
            <img width="50" height="50" src="https://img.icons8.com/ios-filled/50/exit.png" alt="exit" alt="Logout" style="vertical-align:middle; width:20px; height:20px; margin-right:5px;">
        </a>
    </p>
    <iframe src=" http://localhost:8501" width="100%" height="800px" style="border_id:none;"></iframe>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h2 {
            color: #333;
        }
        p {
            color: #555;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
        iframe {
            border_id-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        @media (max-width: 600px) {
            iframe {
                height: 600px; /* Adjust height for smaller screens */
            }
        }
    </style>
</body>
</html>
