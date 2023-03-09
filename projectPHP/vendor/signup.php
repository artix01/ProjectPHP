<?php
session_start();
if (isset($_SESSION['user'])){
    header('Location: ../profile.php');
}

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die();
}

class UserRegistration {
    private $name;
    private $login;
    private $email;
    private $password;
    private $password_confirm;
    private $users_file = '../../users.json';

    public function __construct($name, $login, $email, $password, $password_confirm) {
        $this->name = $name;
        $this->login = $login;
        $this->email = $email;
        $this->password = $password;
        $this->password_confirm = $password_confirm;
    }

    //Валидация email(unique)
    public function validateEmail() {
        // Проверка на пустое значение
        if (empty($this->email)) {
            return 'Поле email не может быть пустым';
        }

        // Проверка на наличие двойного символа @
        if (substr_count($this->email, '@') !== 1) {
            return 'Некорректный email';
        }

        // Удаление пробелов из начала и конца строки
        $this->email = trim($this->email);

        // Проверка на наличие пробелов внутри строки
        if (preg_match('/\s/', $this->email)) {
            return 'Некорректный email';
        }

        // Разделение строки на логин и доменную часть
        list($login, $domain) = explode('@', $this->email);

        // Проверка на наличие доменной части
        if (empty($domain)) {
            return 'Некорректный email';
        }

        // Проверка на наличие точки в доменной части
        if (substr_count($domain, '.') < 1) {
            return 'Некорректный email';
        }

        // Проверка на уникальность email в БД
        $unique = json_decode(file_get_contents('../../users.json'), true);

        foreach ($unique as $email) {
            if ($email['email'] === $this->email) {
                return 'Данный email уже занят';
            }
        }
        return '';
    }

    // Валидация login(unique)
    public function validateLogin() {
        // Проверка на пустое значение
        if (empty($this->login)) {
            return 'Поле логин не может быть пустым';
        }

        // Удаление пробелов из начала и конца строки
        $this->login = trim($this->login);

        // Проверка на длину логина
        if (strlen($this->login) < 6) {
            return 'Логин должен содержать минимум 6 символов';
        }

        // Проверка на наличие пробелов внутри строки
        if (preg_match('/\s/', $this->login)) {
            return 'Логин не может содержать пробелы';
        }

        // Проверка на уникальность логина в БД
        $unique = json_decode(file_get_contents('../../users.json'), true);

        foreach ($unique as $user) {
            if ($user['login'] === $this->login) {
                return 'Данный логин уже занят';
            }
        }
        return '';
    }

    // Валидация password
    public function validatePassword() {
        // Проверка на пустое значение
        if (empty($this->password)) {
            return 'Поле пароля не может быть пустым';
        }

        // Проверка на наличие только пробелов
        if (trim($this->password) === '') {
            return 'Поле пароля не может состоять только из пробелов';
        }

        // Проверка на длину пароля
        if (strlen($this->password) < 6) {
            return 'Длина пароля должна быть не меньше 6 символов';
        }

        // Проверка на наличие только букв и цифр
        if (!preg_match('/^[a-zA-Z0-9]+$/', $this->password)) {
            return 'Пароль может состоять только из букв и цифр';
        }

        // Проверка на наличие хотя бы одной буквы и одной цифры
        if (!preg_match('/[a-zA-Z]/', $this->password) || !preg_match('/\d/', $this->password)) {
            return 'Пароль должен содержать хотя бы одну букву и одну цифру';
        }

        // Проверка на наличие пробелов в начале или в конце строки
        if ($this->password !== trim($this->password)) {
            return 'Пароль не может начинаться или заканчиваться пробелами';
        }

        // Проверка на наличие пробелов между символами
        if (preg_match('/\s/', $this->password)) {
            return 'Пароль не может содержать пробелы между символами';
        }

        return '';
    }

    //Валидация name
    public function validateName() {
        // Проверка на пустое значение
        if (empty($this->name)) {
            return 'Поле имя не может быть пустым';
        }

        // Удаление пробелов из начала и конца строки
        $this->name = trim($this->name);

        // Проверка на длину имени
        if (strlen($this->name) < 2) {
            return 'Имя должно содержать минимум 2 символа';
        }

        // Проверка на границы длины имени
        if (strlen($this->name) > 50) {
            return 'Имя должно содержать максимум 50 символов';
        }

        // Проверка на ввод имени с пробелами между/в начале /в конце букв
        if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ]+(\s+[a-zA-Zа-яА-ЯёЁ]+)*$/u', $this->name)) {
            return 'Имя должно состоять только из букв, пробелов и начинаться с буквы';
        }

        // Проверка на ввод имени только из пробелов
        if (preg_match('/^\s+$/', $this->name)) {
            return 'Имя не может состоять только из пробелов';
        }

        return '';
    }

    //Генерация "соли"
    public function generateSalt($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public $errors=[];
    public $messages=[];

    public function registerUser() {
        $password_error = $this->validatePassword();
        if ($password_error !== '') {
            $this->errors['password_message'] = $password_error;
        }

        $email_error = $this->validateEmail();
        if ($email_error !== '') {
            $this->errors['email_message'] = $email_error;
        }

        $login_error = $this->validateLogin();
        if ($login_error !== '') {
            $this->errors['login_message'] = $login_error;
        }

        $name_error = $this->validateName();
        if ($name_error !== '') {
            $this->errors['name_message'] = $name_error;
        }

        if ($this->password !== $this->password_confirm) {
            $this->errors['confirm_password_message'] = 'Пароли не совпадают';
        }

        // Выводим ошибки, если они есть
        if ($this->errors) {
            echo json_encode(['errors' => $this->errors]);
            exit();
        }

        $salt = $this->generateSalt();
        $_SESSION['salt' . $this->login]=$salt; // Передаем для каждого логина уникальную "соль"

        $this->password = md5($salt . $this->password);

        $users = json_decode(file_get_contents($this->users_file), true);

        $user = [
            'id' => count($users) + 1,
            'name' => $this->name,
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
        ];
        $users[] = $user;

        // Записываем данные пользователя в файл
        file_put_contents($this->users_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->messages[] = 'Регистрация прошла успешно!';
        echo json_encode(['success' => true, 'messages' => $this->messages]);
        exit();
    }
}

$registration = new UserRegistration($_POST['name'], $_POST['login'], $_POST['email'], $_POST['password'], $_POST['password_confirm']);
$registration->registerUser();