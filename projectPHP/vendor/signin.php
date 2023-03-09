<?php
session_start();

if (isset($_SESSION['user'])){
    header('Location: ../profile.php');
}

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die();
}
class Validator {
    private $errors = [];

    public function validate($data) {
        $valid = true;

        if (empty($data['login'])) {
            $this->errors['login'] = 'Введите логин';
            $valid = false;
        }

        if (empty($data['password'])) {
            $this->errors['password'] = 'Введите пароль';
            $valid = false;
        }

        return $valid;
    }

    public function getErrors() {
        return $this->errors;
    }
}

class Auth {
    private $users;

    public function __construct() {
        $this->users = json_decode(file_get_contents('../../users.json'), true);
    }

    public function login($login, $password) {
        foreach ($this->users as $user) {
            if ($user['login'] === $login && $user['password'] === md5($_SESSION['salt' . $login] . $password)) {
                $_SESSION['user'] = [
                    "id" => $user['id'],
                    "name" => $user['name'],
                    "email" => $user['email']
                ];

                return true;
            }
        }

        return false;
    }
}

$validator = new Validator();
$auth = new Auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    if ($validator->validate($_POST)) {
        if ($auth->login($login, $password)) {
            // Авторизация прошла успешно, возвращаем пустой ответ
            http_response_code(204);
            exit;
        } else {
            // Ошибка авторизации, добавляем ошибку в массив ошибок
            $errors['auth'] = 'Неверный логин или пароль';
        }
    } else {
        // Валидация не прошла, добавляем ошибки в массив ошибок
        $errors = $validator->getErrors();
    }

    // Выводим ошибки
    header('Content-Type: application/json', true, 422);
    echo json_encode($errors);
    exit;
}
