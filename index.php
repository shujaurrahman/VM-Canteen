<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .form.login {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        #login h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .inputContainer {
            position: relative;
            margin-bottom: 20px;
        }

        .inputLogin {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s;
        }

        .inputLogin:focus {
            border-color: rgba(135, 100, 255, 0.9);
        }

        .labelLogin {
            position: absolute;
            top: 50%;
            left: 10px;
            color: #aaa;
            font-size: 16px;
            pointer-events: none;
            transform: translateY(-50%);
            transition: all 0.3s;
        }

        .inputLogin:focus + .labelLogin,
        .inputLogin:not(:placeholder-shown) + .labelLogin {
            top: -10px;
            left: 5px;
            color: rgba(135, 100, 255, 0.9);
            font-size: 12px;
        }

        .field.button {
            text-align: center;
        }

        .submitButton {
            background-color: rgba(135, 100, 255, 0.9);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 100%;
        }

        .submitButton:hover {
            background-color: rgba(135, 100, 255, 0.8);
        }

        .error-text {
            color: red;
            text-align: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .register {
            text-align: center;
            margin-top: 20px;
        }

        .breaker {
            color: #999;
        }

        .links {
            color: rgba(135, 100, 255, 0.9);
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .links:hover {
            color: rgba(135, 100, 255, 0.8);
        }

        /* Responsive Styles */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .form.login {
                padding: 15px;
            }

            .inputLogin {
                font-size: 14px;
                padding: 8px;
            }

            .labelLogin {
                font-size: 14px;
            }

            #login h1 {
                font-size: 20px;
            }

            .submitButton {
                padding: 8px 16px;
                font-size: 14px;
            }

            .error-text {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<?php
session_start();

// Hardcoded credentials
$hardcoded_username = "canteen";
$hardcoded_password = "shujaurrahman";

// Check if the user is already logged in
if (isset($_SESSION['username']) && $_SESSION['username'] === $hardcoded_username) {
    header("Location: dashboard.php"); // Redirect to dashboard.php
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['email']; // 'email' field is used as the username
    $password = $_POST['password'];

    // Validate credentials
    if ($username === $hardcoded_username && $password === $hardcoded_password) {
        $_SESSION['username'] = $username; // Set session variable
        header("Location: dashboard.php"); // Redirect to dashboard.php
        exit();
    } else {
        echo '<div class="error-text">Invalid username or password!</div>';
    }
}
?>

<section class="form login">
    <div id="login">
        <div id="formLogin">
            <h1>Sign In →</h1>
            <form action="#" method="POST" enctype="multipart/form-data" autocomplete="off">
                <div class="error-text"></div>
                <div class="inputContainer">
                    <input type="text" class="inputLogin" placeholder=" " name="email" required>
                    <label class="labelLogin">Email</label>
                </div>
                <div class="inputContainer">
                    <input type="password" class="inputLogin" placeholder=" " name="password" required>
                    <label class="labelLogin">Password</label>
                </div>
                <div class="field button">
                    <input type="submit" class="submitButton" value="Sign In">
                </div>
            </form>
        </div>
    </div>
</section>
</body>
</html>
