<?php
session_start();

// Если пользователь уже авторизован, перенаправляем его на страницу профиля
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

<!-- Форма регистрации -->

<form id="registration-form" method="post" onsubmit="return false;">
    <label>Имя</label>
    <div class="error">
        <p class="error_msg" id="name-error"></p>
    </div>
    <input type="text" id="name" name="name" placeholder="Введите имя">
    <label>Логин</label>
    <div class="error">
        <p class="error_msg" id="login-error"></p>
    </div>
    <input type="text" id="login" name="login" placeholder="Введите логин">
    <label>Почта</label>
    <div class="error">
        <p class="error_msg" id="email-error"></p>
    </div>
    <input type="email" id="email" name="email" placeholder="Введите адрес электронной почты">
    <label>Пароль</label>
    <div class="error">
        <p class="error_msg" id="password-error"></p>
    </div>
    <input type="password" id="password" name="password" placeholder="Введите пароль">
    <label>Подтверждение пароля</label>
    <div class="error">
        <p class="error_msg" id="confirm-password-error"></p>
    </div>
    <input type="password" id="password_confirm" name="password_confirm" placeholder="Подтвердите пароль" >
    <button type="submit">Зарегистрироваться</button>
    <!-- Место для вывода сообщений -->
    <div id="error">
        <p class="msg" id="success-msg"></p>
    </div>
    <p>
        У вас уже есть аккаунт? - <a href="../projectPHP/index.php">авторизуйтесь</a>!
    </p>

</form>

<noscript>
    <p>JavaScript отключен в вашем браузере. Для отправки формы необходимо включить JavaScript.</p>
</noscript>

<!-- Подключаем jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Обработчик события отправки формы -->
<script>
    $('#registration-form').on('submit', function (event) {
        // Проверяем, включен ли JS
        if (typeof event !== 'undefined') {
            // Отменяем стандартное поведение формы
            event.preventDefault();
        }

        // Отправляем данные формы через AJAX
        $.ajax({
            type: "POST",
            url: "../projectPHP/vendor/signup.php",
            data: $('#registration-form').serialize(),
            success: function (response) {
                // Парсим JSON-ответ
                var result = JSON.parse(response);
                var errors = result.errors;
                var messages = result.messages;

                // Очищаем сообщения об ошибках
                $('#name-error').html('');
                $('#login-error').html('');
                $('#email-error').html('');
                $('#password-error').html('');
                $('#confirm-password-error').html('');

                // Выводим сообщения об ошибках
                if (errors) {
                    if (errors.name_message) {
                        $('#name-error').html(errors.name_message);
                    }
                    if (errors.login_message) {
                        $('#login-error').html(errors.login_message);
                    }
                    if (errors.email_message) {
                        $('#email-error').html(errors.email_message);
                    }
                    if (errors.password_message) {
                        $('#password-error').html(errors.password_message);
                    }
                    if (errors.confirm_password_message) {
                        $('#confirm-password-error').html(errors.confirm_password_message);
                    }
                }

                // Выводим общие сообщения
                if (messages) {
                    $('#messages').html(messages.join('<br>'));
                }

                // Если регистрация прошла успешно, перенаправляем пользователя на страницу профиля
                if (result.success) {
                    $('#success-msg').html('Регистрация прошла успешно!');
                    setTimeout(function() {
                        window.location.href = "index.php";
                    }, 3000);

                }
            }
        });
    });
</script>

</body>
</html>