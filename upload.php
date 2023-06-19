<?php
$uploadDir = 'uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $files = $_FILES['file'];

        foreach ($files['name'] as $index => $name) {
            $tmpName = $files['tmp_name'][$index];

            if (move_uploaded_file($tmpName, $uploadDir . $name)) {
                echo "Файл $name успешно загружен. <br>";
            } else {
                echo "Ошибка при загрузке файла $name. <br>";
            }
}
    }
}
?>
