<?php
$uploadDir = 'uploads/';

// Обработка действий пользователя
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'add' && isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $currentDir = isset($_GET['dir']) ? $_GET['dir'] : '';
        move_uploaded_file($fileTmpName, $uploadDir . $currentDir . $fileName);
    } elseif ($action === 'delete' && isset($_GET['file'])) {
        $fileToDelete = $_GET['file'];
        unlink($uploadDir . $fileToDelete);
    } elseif ($action === 'move' && isset($_GET['file']) && isset($_GET['destination'])) {
        $fileToMove = $_GET['file'];
        $destination = $_GET['destination'];
        $currentDir = isset($_GET['dir']) ? $_GET['dir'] : '';

        // Проверяем, существует ли целевая папка
        if (!is_dir($uploadDir . $destination)) {
            mkdir($uploadDir . $destination, 0777, true);
        }

        // Перемещаем файл
        rename($uploadDir . $currentDir . $fileToMove, $uploadDir . $destination . '/' . $fileToMove);
    } elseif ($action === 'create_folder' && isset($_POST['folder_name'])) {
        $folderName = $_POST['folder_name'];
        $currentDir = isset($_GET['dir']) ? $_GET['dir'] : '';
        mkdir($uploadDir . $currentDir . $folderName);
    }
}

// Получение списка файлов и папок в текущей директории
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : '';
$currentPath = $uploadDir . $currentDir;

$directories = array();
$files = array();

$items = scandir($currentPath);
foreach ($items as $item) {
    if ($item !== '.' && $item !== '..') {
        if (is_dir($currentPath . '/' . $item)) {
            $directories[] = $item;
        } else {
            $files[] = $item;
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>File Explorer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        h1 {
            background-color: #333;
            color: #fff;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }

        table th {
            background-color: #f2f2f2;
        }

        .actions a {
            color: #333;
            text-decoration: none;
            margin-right: 10px;
        }

        .actions a:hover {
            color: #ff0000;
        }

        .file-forms {
            margin-top: 20px;
        }

        .file-forms input[type="file"],
        .file-forms input[type="text"] {
            margin-right: 10px;
        }

        .file-forms input[type="file"] {
            display: none;
        }

        .browse-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #000000;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
        }

        .browse-btn:hover {
            background-color: #656565;
        }

        .add-btn,
        .create-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #000000;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
        }

        .add-btn:hover,
        .create-btn:hover {
            background-color: #656565;
        }
    </style>
</head>
<body>
<h1>File Explorer</h1>

<div class="container">
    <h2><?php echo $currentDir; ?></h2>

    <form action="index.php?action=add&dir=<?php echo $currentDir; ?>" method="post" enctype="multipart/form-data" class="file-forms">
        <label for="file-upload" class="browse-btn">Обзор</label>
        <input type="file" id="file-upload" name="file" style="display: none;" onchange="updateFileName(this)">
        <span id="file-name"></span>
        <input type="submit" value="Добавить" class="add-btn">
    </form>

    <script>
        function updateFileName(input) {
            const fileName = input.files[0].name;
            document.getElementById('file-name').innerText = fileName;
        }
    </script>


    <form action="index.php?action=create_folder&dir=<?php echo $currentDir; ?>" method="post" class="file-forms">
        <input type="text" name="folder_name" placeholder="Имя папки" required>
        <input type="submit" value="Создать" class="create-btn">
    </form>

    <table>
        <tr>
            <th>Имя</th>
            <th>Тип</th>
            <th>Размер</th>
            <th>Действия</th>
        </tr>
        <?php if ($currentDir !== '') : ?>
            <?php
            $parentDir = substr($currentDir, 0, strrpos($currentDir, '/', -2));
            ?>
            <tr>
                <td><a href="index.php?dir=<?php echo urlencode($parentDir); ?>"><-</a></td>
                <td>Назад</td>
                <td>-</td>
                <td>-</td>
            </tr>
        <?php endif; ?>


        <?php foreach ($directories as $dir) : ?>
            <tr>
                <td><a href="index.php?dir=<?php echo urlencode($currentDir . $dir . '/'); ?>"><?php echo $dir; ?></a></td>
                <td>Папка</td>
                <td>-</td>
                <td class="actions">
                    <a href="index.php?action=delete&file=<?php echo urlencode($currentDir . $dir); ?>">Удалить</a>
                    <a href="index.php?action=move&file=<?php echo urlencode($currentDir . $dir); ?>">Переместить</a>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php foreach ($files as $file) : ?>
            <?php
            $filePath = $currentPath . '/' . $file;
            $fileSize = filesize($filePath);
            ?>
            <tr>
                <td><?php echo $file; ?></td>
                <td>Файл</td>
                <td><?php echo formatFileSize($fileSize); ?></td>
                <td class="actions">
                    <a href="index.php?action=delete&file=<?php echo urlencode($currentDir . $file); ?>">Удалить</a>
                    <a href="index.php?action=move&file=<?php echo urlencode($currentDir . $file); ?>">Переместить</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>


<?php
// Функция для форматирования размера файла
function formatFileSize($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $formattedSize = $size;
    $unitIndex = 0;

    while ($formattedSize >= 1024 && $unitIndex < count($units) - 1) {
        $formattedSize /= 1024;
        $unitIndex++;
    }

    return round($formattedSize, 2) . ' ' . $units[$unitIndex];
}
?>
