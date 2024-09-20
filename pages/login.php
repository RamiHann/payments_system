<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Bank System</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/toast/toast.min.css">
</head>

<body>
    <div class="auth-wrapper auth-cover">
        <div class="auth-inner row m-0">
            <div class="d-none d-lg-flex col-lg-8 align-items-center p-5">
                <div class="w-100 d-lg-flex align-items-center justify-content-center px-5">
                    <img class="img-fluid" src="/assets/images/login.png" alt="Login">
                </div>
            </div>
            <div class="d-flex col-lg-4 align-items-center auth-bg px-2 p-lg-5">
                <div class="col-12 col-sm-8 col-md-6 col-lg-12 px-xl-2 mx-auto">
                    <h2 class="card-title fw-bold mb-1">Bank Payment System</h2>
                    <form class="needs-validation auth-login-form mt-2" id="loginForm" method="POST" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="username">Username</label>
                            <input class="form-control" id="username" type="text" name="username" placeholder="John Doe"
                                autofocus required />
                            <div class="invalid-feedback">
                                Username is required.
                            </div>

                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control" id="password" type="password" name="password"
                                placeholder="············" required />
                            <div class="invalid-feedback">
                                Password is required.
                            </div>
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary w-100" type="button" id="login_btn">Login</button>
                        </div>
                    </form>
                    <p class="text-center mt-2">
                        <span>New on our platform?</span>
                        <a href="/pages/register.php"><span>&nbsp;Create an account</span></a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/toast/toast.min.js"></script>
    <script src="/assets/js/validations.js"></script>
    <script>
    $(document).ready(function() {
        $("#login_btn").on("click", function(e) {
            e.preventDefault();
            var form = $("#loginForm")[0];
            if (form[0].checkValidity() === false) {
                form[0].classList.add("was-validated");
                return;
            }

            var formData = {
                username: $("#username").val(),
                password: $("#password").val()
            };

            $.ajax({
                url: '/includes/login.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    var data = JSON.parse(response);
                    console.log(data)
                    if (data.status == "success") {
                        window.location.href = "/pages/dashboard.php";
                    } else {
                        $.toast({
                            heading: 'Error',
                            text: data.message,
                            showHideTransition: 'slide',
                            icon: 'error',
                            position: 'top-right',
                        });
                    }
                },
                error: function() {
                    $.toast({
                        heading: 'Error',
                        text: 'An error occurred while processing your request. Please try again later.',
                        showHideTransition: 'slide',
                        icon: 'error',
                        position: 'top-right',
                    });
                }
            });
        });
    });
    </script>
</body>

</html>
