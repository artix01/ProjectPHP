<?php
session_start();


if (isset($_SESSION['user'])){
    header('Location: profile.php');
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Авторизация и регистрация</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<!-- Форма авторизации -->

<form id="login-form" method="post">
    <label>Логин</label>
    <div class="error">
        <p class="error_msg" id="login-error"></p>
    </div>
    <input type="text" name="login" placeholder="Введите свой логин">
    <label>Пароль</label>
    <div class="error">
        <p class="error_msg" id="password-error"></p>
    </div>
    <input type="password" name="password" placeholder="Введите пароль">
    <button type="submit">Войти</button>
    <div class="error">
        <p class="auth_msg" id="error-message"></p>
    </div>
    <p>
        У вас нет аккаунта? - <a href="../projectPHP/register.php">зарегистрируйтесь</a>!
    </p>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(function () {
        $('#login-form').on('submit', function (e) {
            e.preventDefault();

            $.ajax({
                url: '../projectPHP/vendor/signin.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                complete: function(xhr) {
                    if (xhr.status === 204) {
                        window.location.href = '../projectPHP/profile.php';
                    } else {
                        var errors = xhr.responseJSON;

                        if (errors.login) {
                            $('#login-error').text(errors.login);
                        } else {
                            $('#login-error').text('');
                        }

                        if (errors.password) {
                            $('#password-error').text(errors.password);
                        } else {
                            $('#password-error').text('');
                        }

                        if (errors.auth) {
                            $('#error-message').text(errors.auth);
                        } else {
                            $('#error-message').text('');
                        }
                    }
                }
            });
        });
    });
</script>

</body>
</html>