<!DOCTYPE html>
<html style="font-size: 16px;" lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <title>OJT Web Portal</title>
    <link rel="shortcut icon" href="images/pupLogo.png">
    <link rel="stylesheet" href="css/nicepage.css" media="screen">
    <link rel="stylesheet" href="css/Page-1.css" media="screen">
    <script class="u-script" type="text/javascript" src="js/jquery.js" defer=""></script>
    <link id="u-theme-google-font" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i|Open+Sans:300,300i,400,400i,500,500i,600,600i,700,700i,800,800i">
    <link href="css/lib/sweetalert/sweetalert.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap');        
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            padding: 2rem;
            background: url(./images/landing.jpg);
            background-color: rgba(255, 255, 255, 0.39);
            background-blend-mode: overlay; 
            height: 100vh;
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        h5 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;  
            align-items: center;
            justify-content: center;
            display: flex;
            line-height: 0.5;
            color: #9B0C0C;
        }

        i {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;  
            align-items: center;
            justify-content: center;
            display: flex;
            line-height: 0.5;
            color: #000;
        }

        input {
            width: 100%;
            max-width: 500px;
            height: 50px;
            padding: 10px;
            margin: 10px 30px;
            border: 1px solid #000;
            border-radius: 5px;
            outline: none;
            color: #000;
            font-size: 14px;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            color: #000;
            margin-left: 30px;
        }

        .nav-1 {
            font-family: 'Source Serif 4', serif;
            background: linear-gradient(to left, rgba(155, 12, 12, 1), rgba(255, 255, 255, 1));
            color: #D11010;
            padding: 15px 0;
            text-align: left;
            font-size: 20px;
            font-weight: 400;
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            display: flex;
            align-items: center;
            margin: 0 auto;
            background-clip: padding-box;
        }

        .nav-logo {
            height: 50px;
            margin-left: 20px;
        }

        .nav-title-caption-container {
            display: flex;
            flex-direction: column;
            margin-left: 20px;
        }

        .nav-title {
            font-size: 24px;
            font-weight: bold;
        }

        .nav-caption {
            font-size: 16px;
            color: #000;
            font-weight: normal;
        }

        .login-section {
            background: #fff;
            width: 850px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 100px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1.5;
        }

        .login-button {
            background: #9B0C0C;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            margin-top: 10px;
            transition: 0.3s;
            width: 100%;
            max-width: 500px;
            height: 50px;
        }

        .forgot-password {
            font-size: 16px;
            color: #9B0C0C;
        }

        .register-here {
            font-size: 16px;
            color: #0C0C9B;
        }

        a {
            align-self: center;
            justify-self: center;
            display: flex;
        }

        .back-button {
            display: flex;
            align-items: center;
            background: none;
            color: rgba(128, 128, 128, 0.5);
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 400;
            transition: 0.3s;
            position: relative;
            right: 150px;
            bottom: 10px;
        }

        .back-button img {
            height: 40px;
            filter: grayscale(100%);
            opacity: 0.3;
            transition: filter 0.3s ease, opacity 0.3s ease;
        }

        .back-button:hover,
        .back-button:hover img {
            color: #9B0C0C;
            filter: grayscale(0%) sepia(100%) hue-rotate(330deg) saturate(500%);
            opacity: 1;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 50px;
            margin-right: 10rem;
        }

        .logo-container {
            margin-bottom: 20px;
        }

        @media (max-width: 950px) {
            .nav-1 {
                flex-direction: column;
                text-align: center;
                padding: 10px 0;
            }

            .nav-logo {
                margin: 0;
            }

            .nav-title-caption-container {
                margin: 10px 0;
            }

            .nav-title {
                font-size: 20px;
            }

            .nav-caption {
                font-size: 14px;
            }

            .login-section {
                margin-top: 150px;
                width: 95%;
                padding: 15px;
            }

            .header {
                flex-direction: column;
                gap: 20px;
                margin-right: 0;
            }

            .back-button {
                right: 0;
                bottom: 0;
                margin-bottom: 20px;
            }

            input, .login-button {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            h5 {
                font-size: 16px;
            }

            .nav-title {
                font-size: 18px;
            }

            .nav-caption {
                font-size: 12px;
            }

            .login-section {
                margin-top: 120px;
                width: 100%;
                padding: 10px;
            }

            .header {
                gap: 10px;
            }

            .back-button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<nav class="nav-1">
        <img src="images/pupLogo.png" alt="PUP Logo" class="nav-logo">
        <div class="nav-title-caption-container">
            <div class="nav-title">Polytechnic University of the Philippines-ITECH</div>
            <div class="nav-caption">On the Job Training Portal</div>
        </div>
    </nav>

    <section class="login-section">
        <div>
            <div class="header">
                <a href="/pup/index.php" class="back-button"><img src="images/less-than.png" alt="Back">Back</a>
                <div class="logo-container">
                    <h5>ADMINISTRATOR ACCOUNT</h5>
                </div>
            </div>
            <div>
                <form action="../php/admin_loginCode.php" method="POST">
                    <div class="u-form-group u-form-name">
                        <label for="adminEMAIL" class="u-label">Email</label><br>
                        <input type="email" placeholder="Enter your Email" id="adminEMAIL" name="adminEmail" required="">
                    </div>
                    <div class="u-form-email u-form-group">
                        <label for="pword" class="u-label">Password</label><br>
                        <input type="password" placeholder="Enter Password" id="pword" name="adminPword" required="">
                    </div>
                    <div class="u-align-center u-form-group u-form-submit">
                        <button name="LogIn" class="login-button">Log In</button>
                    </div>
                    <p><a href="../forgot_password.php" class="forgot-password">Forgotten Password?</a></p>
                    <p><a href="admin_register.php" class="register-here">Sign Up Here</a></p>
                </form>
            </div>
        </div>
    </section>

    <script src="js/lib/sweetalert/sweetalert.min.js"></script>
    <script src="js/lib/sweetalert/sweetalert.init.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/scripts.js"></script>

    <?php 
    if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
    ?>
    <script>
    sweetAlert("<?php echo $_SESSION['alert']; ?>", "<?php echo $_SESSION['status']; ?>", "<?php echo $_SESSION['status-code']; ?>");
    </script>
    <?php
    unset($_SESSION['status']);
    }
    ?>
</body>
</html>