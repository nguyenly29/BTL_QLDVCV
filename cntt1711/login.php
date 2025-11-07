<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup Form - Công viên giải trí</title>
    
    <link rel="stylesheet" href="assets/css/SignUp_LogIn_Form.css">
    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <style>
        .php-error {
            color: #D8000C; /* Màu đỏ đậm */
            background-color: #FFD2D2; /* Nền đỏ nhạt */
            border: 1px solid #D8000C;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
  <body>

      <div class="container">
          <div class="form-box login">
              <form action="handle/login.php" method="POST">
                  <h1>Login</h1>
                  
                  <?php
                  if (isset($_GET['error'])) {
                      echo '<div class="php-error">'.htmlspecialchars($_GET['error']).'</div>';
                  }
                  ?>

                  <div class="input-box">
                      <input type="text" name="user_name" placeholder="Username" required>
                      <i class='bx bxs-user'></i>
                  </div>
                  <div class="input-box">
                      <input type="password" name="pass_word" placeholder="Password" required>
                      <i class='bx bxs-lock-alt' ></i>
                  </div>
                  <div class="forgot-link">
                  </div>
                  <button type="submit" class="btn">Login</button>
              </form>
          </div>

          <div class="form-box register">
              <form action="handle/register.php" method="POST">
                  <h1>Registration</h1>
                  <div class="input-box">
                      <input type="text" name="user_name" placeholder="Username" required>
                      <i class='bx bxs-user'></i>
                  </div>
                  <div class="input-box">
                      <input type="password" name="pass_word" placeholder="Password" required>
                      <i class='bx bxs-lock-alt' ></i>
                  </div>
                  <button type="submit" class="btn">Register</button>
              </form>
          </div>

          <div class="toggle-box">
              <div class="toggle-panel toggle-left">
                  <h1>Hello, Welcome!</h1>
                  <p>Don't have an account?</p>
                  <button class="btn register-btn">Register</button>
              </div>

              <div class="toggle-panel toggle-right">
                  <h1>Welcome Back!</h1>
                  <p>Already have an account?</p>
                  <button class="btn login-btn">Login</button>
              </div>
          </div>
      </div>

      <script src="assets/js/SignUp_LogIn_Form.js"></script>
  </body>
</html>