<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Spicy Mama - Login</title>
 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.css" rel="stylesheet"/>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1f2f6;
        }

        .gradient-form {
            background: #f5f5f1;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
        }

        .gradient-custom-2 {
            background: linear-gradient(to bottom right, #4c1a57, #3D3668, #2E5378, #1E6F89, #0F8C99, #00A8AA);
            color: #ffffff;
            border-radius: 0 9px 9px 0;
            text-align: center;
            padding: 3rem;
        }

        .text-black {
            color: #333;
        }

        .form-outline {
            position: relative;
        }

        .form-outline input {
            border-radius: 25px;
            padding: 1rem;
        }

        .form-outline label {
            font-size: 0.85rem;
            color: #aaa;
            padding-left: 15px;
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            transition: all 0.2s ease;
        }

        .form-outline input:focus + .form-label,
        .form-outline input:not(:placeholder-shown) + .form-label {
            top: -10px;
            font-size: 0.75rem;
            color: #777;
            background: #f1f2f6;
            padding: 0 5px;
        }

        button {
            background-image: linear-gradient(to right, #1F1C18, #d61c29,#d61c29,  #f1842aff);
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            font-size: 1rem; 
            background-size: 300%;
            background-position: left;
            color: white;
            border: none;
            transition: 300ms background-position ease-in-out;
        }

        button:hover {
            background-position: right;
            color: #fff;
        }

        .login-img {
            width: 150px;
            border-radius: 10px;
        }

        .small-text {
            font-size: 0.9rem;
            color: white;
        }
    </style>
</head>
<body>

<section class="gradient-form">
  <div class="container">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-lg-6">
        <div class="card rounded-3 text-black">
          <div class="row g-0">
            <div class="col-lg-12 p-5">
              <div class="text-center mb-5">
                <img src="images/logo.jpg" alt="logo" class="login-img">
                <h4 class="mt-3">POS Spicy Mama</h4>
              </div>

              <form method="POST" action="api/login_controller.php">
                <p class="mb-4">Login to your account</p>

                <div class="form-outline mb-4">
                  <input type="text" name="email" id="form2Example11" class="form-control" placeholder=" " required />
                  <label class="form-label" for="form2Example11">Username</label>
                </div>

                <div class="form-outline mb-4">
                  <input type="password" name="password" id="form2Example22" class="form-control" placeholder=" " required />
                  <label class="form-label" for="form2Example22">Password</label>
                </div>

                <?php
                session_start();

                if (isset($_SESSION['flash']['login'])) {
                  $flash = $_SESSION['flash']['login'];
                  $alertType = ($flash['type'] === 'error') ? 'danger' : 'success'; // Convert 'error' to Bootstrap 'danger'
                  echo '<div class="alert alert-' . htmlspecialchars($alertType) . ' alert-dismissible fade show mt-2" role="alert">';
                  echo htmlspecialchars($flash['message']);
                  echo '</div>';
                  unset($_SESSION['flash']['login']); // Clear the flash message after displaying
                }
                ?>

                <button class=" w-100" type="submit">Login</button>
              </form>

            </div>
            

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.umd.min.js"></script>
</body>
</html>
