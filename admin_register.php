<?php
include '../connection/config.php';
error_reporting(0);

session_start();
?>

<!DOCTYPE html>
<html style="font-size: 16px;" lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <title>Register - OJT Web Portal</title>
    <link rel="stylesheet" href="css/nicepage.css" media="screen">
    <link rel="stylesheet" href="css/Page-2.css" media="screen">
    <script class="u-script" type="text/javascript" src="jquery.js" defer=""></script>
    <link id="u-theme-google-font" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i|Open+Sans:300,300i,400,400i,500,500i,600,600i,700,700i,800,800i">
    <link href="css/lib/sweetalert/sweetalert.css" rel="stylesheet">
    <link href="endorsement-css/endorsement-moa.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            height: 100vh;
            width: 100%;
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            background-color: #f4f4f4;
        }

        h5 {
            font-size: 18px;
            font-weight: 600;
            align-items: center;
            margin-top: -10px;
            justify-content: flex-start;
            display: flex;
            line-height: 1;
            color: #9B0C0C;
        }

        input,
        select {
            width: 100%;
            max-width: 100%;
            height: 60px;
            padding: 20px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 10px;
            outline: none;
            color: #000;
            font-size: 14px;
            box-sizing: border-box;
            background-color: #f9f9f9;
        }

        input[type="file"] {
            padding: 5px;
            height: auto;
        }

        label {
            font-size: 14px;
            font-weight: 400;
            color: #000;
            margin-left: 0;
            display: block;
            text-align: left;
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
            background-clip: padding-box;
            z-index: 1000;
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

        .register-section {
            background: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            line-height: 1.5;
            margin-top: 100px;
            width: 90%;
            max-width: 800px;
            box-sizing: border-box;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .register-button {
            background: #9B0C0C;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            margin-top: 20px;
            transition: 0.3s;
            width: 100%;
            height: 50px;
        }

        .header {
            position: absolute;
            top: 100px;
            left: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-left: 0;
            margin-bottom: 20px;
            gap: 10px;
            z-index: 10;
        }

        h5 img {
            margin-right: 10px;
            filter: invert(10%) sepia(88%) saturate(5144%) hue-rotate(356deg) brightness(97%) contrast(106%);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            width: 100%;
        }

        .form-grid > div {
            display: flex;
            flex-direction: column;
        }

        .form-grid .full-width {
            grid-column: span 3;
        }

        .required {
            color: red;
            font-weight: bold;
            margin-left: 5px;
        }


        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .nav-logo {
                height: 40px;
            }

            .nav-title {
                font-size: 20px;
            }

            .nav-caption {
                font-size: 14px;
            }

            .register-section {
                margin-top: 150px;
                width: 95%;
                padding: 15px;
            }

            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-grid .full-width {
                grid-column: span 2;
            }

            input,
            select {
                height: 38px;
                font-size: 13px;
            }

            label {
                font-size: 13px;
            }

            .register-button {
                height: 45px;
                font-size: 16px;
            }

            .header {
                top: 70px;
                left: 10px;
            }

            h5 {
                font-size: 16px;
            }

            h5 img {
                height: 20px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0.5rem;
            }

            .nav-logo {
                height: 35px;
            }

            .nav-title {
                font-size: 18px;
            }

            .nav-caption {
                font-size: 12px;
            }

            .register-section {
                margin-top: 130px;
                width: 100%;
                padding: 10px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-grid .full-width {
                grid-column: span 1;
            }

            input,
            select {
                height: 36px;
                font-size: 12px;
            }

            label {
                font-size: 12px;
            }

            .register-button {
                height: 40px;
                font-size: 14px;
            }

            .header {
                top: 60px;
                left: 5px;
            }

            h5 {
                font-size: 14px;
            }

            h5 img {
                height: 18px;
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

    <header class="header">
      
        <a href="index.php" class="back-button">
            <span class="back-icon"><img src="images/less-than.png" alt="Back"></span>
              Back
            </a>
        <h5>
            <img src="images/pencil.svg" alt="Edit Icon" height="27" >
            REGISTRATION
        </h5>
    </header>
    <section>
        <div>
            <div class="u-form u-form-1" style="top: 100px;">
                <form action="../php/stud_registerCode.php" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div>
                            <label for="text-sis-no">SIS No. / Username <span class="required">*</span></label>
                            <input type="text" placeholder="Enter SIS No." id="text-sis-no" name="student_id" required="true">
                        </div>
                        <div>
                            <label for="text-password">Password <span class="required">*</span></label>
                            <input type="password" placeholder="Enter Password" id="text-password" name="pword" required="true">
                        </div>
                        <div>
                            <label for="text-confirm-password">Confirm Password <span class="required">*</span></label>
                            <input type="password" placeholder="Enter Password" id="text-confirm-password" name="cpword" required="true">
                        </div>

                        <div>
                            <label for="text-first-name">First Name <span class="required">*</span></label>
                            <input type="text" placeholder="Enter Firstname" id="text-first-name" name="f_name" required="true">
                        </div>
                        <div>
                            <label for="text-middle-name">Middle Name <span class="required">*</span></label>
                            <input type="text" placeholder="Enter Middlename" id="text-middle-name" name="m_name" required="true">
                        </div>
                        <div>
                            <label for="text-last-name">Last Name <span class="required">*</span></label>
                            <input type="text" placeholder="Enter Lastname" id="text-last-name" name="l_name" required="true">
                        </div>

                        <div>
                            <label for="select-course">Course <span class="required">*</span></label>
                            <select id="select-course" name="student_course" required="true">
                                <option value="">Select Course</option>
                            </select>
                        </div>
                        <div>
                            <label for="select-year">Year <span class="required">*</span></label>
                            <select id="select-year" name="year_lvl" required="true">
                                <option value="">Select Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                            </select>
                        </div>
                        <div>
                            <label for="text-section">Section <span class="required">*</span></label>
                            <input type="text" placeholder="Enter Section" id="text-section" name="student_section" required="true">
                        </div>

                        <div class="full-width">
                            <label for="file-sis-document">Attach SIS Document <span class="required">*</span></label>
                            <input type="file" accept="application/pdf" id="file-sis-document" name="stud_pic" required="true">
                        </div>
                    </div>

                    <div>
                        <button name="register" class="register-button">Register</button>
                    </div>
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

    <script>
    $(document).ready(function() {
        var courseSelect = $('select[name="student_course"]');
        courseSelect.empty();
        courseSelect.append('<option value="">Select Course</option>');
        courseSelect.append('<option value="Diploma in Civil Engineering Technology (DCvET)">Diploma in Civil Engineering Technology (DCvET)</option>');
        courseSelect.append('<option value="Diploma in Computer Engineering Technology (DCET)">Diploma in Computer Engineering Technology (DCET)</option>');
        courseSelect.append('<option value="Diploma in Electrical Engineering Technology (DEET)">Diploma in Electrical Engineering Technology (DEET)</option>');
        courseSelect.append('<option value="Diploma in Electronics Engineering Technology (DECET)">Diploma in Electronics Engineering Technology (DECET)</option>');
        courseSelect.append('<option value="Diploma in Information Technology (DIT)">Diploma in Information Technology (DIT)</option>');
        courseSelect.append('<option value="Diploma in Mechanical Engineering Technology (DMET)">Diploma in Mechanical Engineering Technology (DMET)</option>');
        courseSelect.append('<option value="Diploma in Office Management Technology (DOMT)">Diploma in Office Management Technology (DOMT)</option>');
        courseSelect.append('<option value="Diploma in Railway Engineering Technology (DRET)">Diploma in Railway Engineering Technology (DRET)</option>');
    });
    </script>
</body>
</html>