<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_name']) && isset($_POST['file_content'])) {
    $fileName = $_POST['file_name'];
    $fileContent = $_POST['file_content'];

    // Проверка имени файла
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $fileName)) {
        echo 'Недопустимое имя файла. Пожалуйста, используйте только латинские буквы, цифры, дефисы, подчеркивания и точки.';
        exit;
    }

    // Проверка содержимого файла
    if (preg_match('/<\?php/', $fileContent)) {
        echo 'Недопустимое содержимое файла. Файлы с PHP-кодом запрещены.';
        exit;
    }

    $filePath = 'uploads/' . $fileName;

    if (file_put_contents($filePath, $fileContent) !== false) {
        echo 'Файл успешно создан.';
    } else {
        echo 'Ошибка при создании файла.';
    }

    exit;
}

echo 'Неверный запрос.';
