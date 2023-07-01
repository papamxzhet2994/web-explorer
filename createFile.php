<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_name']) && isset($_POST['file_content'])) {
    $fileName = $_POST['file_name'];
    $fileContent = $_POST['file_content'];

    $basePath = 'uploads/';
    $filePath = $basePath . $fileName;

    if (str_contains($filePath, '..')) {
        echo 'Неверный путь к файлу.';
        exit;
    }

    if (!file_exists($basePath)) {
        mkdir($basePath, 0777, true);
    }

    if (file_put_contents($filePath, $fileContent) !== false) {
        echo 'Файл успешно создан.';
    } else {
        echo 'Ошибка при создании файла.';
    }

    exit;
}

echo 'Неверный запрос.';
?>
