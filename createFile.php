<?php
$uploadDir = 'uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_name']) && isset($_POST['file_content'])) {
    $fileName = $_POST['file_name'];
    $fileContent = $_POST['file_content'];

    $filePath = $uploadDir . $fileName;

    if (file_put_contents($filePath, $fileContent) !== false) {
        echo 'Файл успешно создан.';
    } else {
        echo 'Ошибка при создании файла.';
    }

    exit;
}

echo 'Неверный запрос.';
?>
