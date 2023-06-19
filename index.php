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
        $filePath = $uploadDir . $fileToDelete;

        if (is_dir($filePath)) {
            // Удаляем папку и ее содержимое
            deleteDirectory($filePath);
        } elseif (is_file($filePath)) {
            // Удаляем файл
            unlink($filePath);
        }
        if ($action === 'move' && isset($_GET['file']) && isset($_GET['destination'])) {
            $fileToMove = $_GET['file'];
            $destination = $_GET['destination'];
            $currentDir = isset($_GET['dir']) ? $_GET['dir'] : '';

            // Проверяем, существует ли целевая папка
            if (!is_dir($uploadDir . $destination)) {
                mkdir($uploadDir . $destination, 0777, true);
            }

            // Перемещаем файл
            if (rename($uploadDir . $currentDir . $fileToMove, $uploadDir . $destination . '/' . $fileToMove)) {
                header('Location: index.php?dir=' . urlencode($currentDir));
                exit;
            } else {
                echo 'Ошибка при перемещении файла.';
            }
        }
    } elseif ($action === 'create_folder' && isset($_POST['folder_name'])) {
        $folderName = $_POST['folder_name'];
        $currentDir = isset($_GET['dir']) ? $_GET['dir'] : '';
        $newFolderPath = $uploadDir . $currentDir . $folderName;

        // Проверяем, существует ли папка с таким именем
        if (!is_dir($newFolderPath)) {
            if (mkdir($newFolderPath, 0777, true)) {
                header('Location: index.php?dir=' . urlencode($currentDir));
                exit;
            } else {
                echo 'Ошибка при создании папки.';
            }
        } else {
            echo 'Папка с таким именем уже существует.';
        }
    }
}

// Функция для удаления папки и ее содержимого
function deleteDirectory($dirPath) {
    if (!is_dir($dirPath)) {
        return;
    }

    $files = scandir($dirPath);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = $dirPath . '/' . $file;
        if (is_dir($filePath)) {
            deleteDirectory($filePath);
        } else {
            unlink($filePath);
        }
    }

    rmdir($dirPath);
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
            max-width: 100%;
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

        .add-btn{
            display: inline-block;
            padding: 8px 16px;
            background-color: #000000;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
        }

        .add-btn:hover:hover {
            background-color: #656565;
        }

        input[type="text"] {
            width: 200px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        button {
            padding: 8px 16px;
            background-color: #000000;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            margin: 10px 0;
        }

        button:hover {
            background-color: #656565;
        }

        /* Модальное окно создания файла */
        #createFileModal {
            display: none;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }

        #createFileModal .modal-content {
            background-color: #fefefe;

            padding: 20px;
            width: 100%;
            height: 100%;
        }

        textarea {
            width: 95%;
            min-height: 200px;
            height: 300px;
            border: none;
            resize: vertical;
            outline: none;
            font-size: 16px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;

        }

        .create-folder-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #000000;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
        }

        .create-folder-btn:hover {
            background-color: #656565;
        }

        .create-folder-input,
        .cancel-btn {
            display: none;
        }

        .create-folder-input.show,
        .cancel-btn.show {
            display: inline-block;
        }

    </style>
</head>
<body>
<h1>File Explorer</h1>

<div class="container">
    <h2><?php echo $currentDir; ?></h2>


    <div class="file-forms">
        <form action="index.php?action=add&dir=<?php echo $currentDir; ?>" method="post" enctype="multipart/form-data">
            <p>Вы можете загрузить или создать свой текстовый файл или добавить папку с помощью кнопок которые распологаются ниже:</p>
        <button type="button" class="create-folder-btn" id="createFolderBtn">Создать папку</button>
            <label for="file-upload" class="browse-btn">Загрузить</label>
            <input type="file" id="file-upload" name="file" style="display: none;" onchange="updateFileName(this)">
            <span id="file-name"></span>
            <input type="submit" value="Добавить" class="add-btn">
        </form>
    </div>
    <script>
        function updateFileName(input) {
            const fileName = input.files[0].name;
            document.getElementById('file-name').innerText = fileName;
        }

        function openCreateFileModal() {
            document.getElementById('createFileModal').style.display = 'block';
        }

        function closeCreateFileModal() {
            document.getElementById('createFileModal').style.display = 'none';
        }

        function createFileRequest() {
            const fileName = document.getElementById('fileNameInput').value + '.txt';
            const fileContent = document.getElementById('fileContentInput').value;

            if (fileName && fileContent) {
                const formData = new FormData();
                formData.append('file_name', fileName);
                formData.append('file_content', fileContent);

                fetch('createFile.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(result => {
                        console.log(result);
                        // Обработка успешного создания файла
                        closeCreateFileModal();
                        location.reload(); // Обновляем страницу, чтобы обновить список файлов

                        // Очистка полей ввода
                        document.getElementById('fileNameInput').value = '';
                        document.getElementById('fileContentInput').value = '';
                    })
                    .catch(error => {
                        console.error(error);
                        // Обработка ошибки
                    });
            }
        }
    </script>

    <button onclick="openCreateFileModal()" class="create-folder-btn">Создать файл</button>
    <div id="createFileModal" style="display: none;">
        <div class="modal-content">
            <h3>Создать файл</h3>
        <input type="text" id="fileNameInput" placeholder="Имя файла">
        <textarea id="fileContentInput" placeholder="Содержимое файла"></textarea>
        <button onclick="createFileRequest()">Создать</button>
        <button onclick="closeCreateFileModal()">Отмена</button>
        </div>
    </div>




    <form action="index.php?action=create_folder&dir=<?php echo $currentDir; ?>" method="post" class="file-forms">
        <input type="text" name="folder_name" placeholder="Имя папки" class="create-folder-input" required>
        <input type="submit" value="Создать" class="create-folder-btn create-folder-input" id="createBtn">
        <button type="button" class="cancel-btn" id="cancelBtn">Отмена</button>
    </form>


    <script>
        let createFolderBtn = document.getElementById('createFolderBtn');
        let createFolderInput = document.getElementsByClassName('create-folder-input');
        let createBtn = document.getElementById('createBtn');
        let cancelBtn = document.getElementById('cancelBtn');

        createFolderBtn.addEventListener('click', function() {
            createFolderBtn.style.display = 'none';
            createFolderInput[0].classList.add('show');
            createBtn.classList.add('show');
            cancelBtn.classList.add('show');
        });

        cancelBtn.addEventListener('click', function() {
            createFolderInput[0].classList.remove('show');
            createBtn.classList.remove('show');
            cancelBtn.classList.remove('show');
            createFolderBtn.style.display = 'inline-block';
        });
    </script>

    <script>
        let dragItem; // Элемент, который перетаскивается

        function handleDragStart(e) {
            dragItem = this;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
        }

        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault(); // Разрешение операции перетаскивания
            }
            e.dataTransfer.dropEffect = 'move';
            return false;
        }

        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation(); // Предотвращение перенаправления
            }

            // Отправка запроса на сервер для перемещения файла или папки
            let destination = this.parentNode.getAttribute('data-dir');
            let fileToMove = dragItem.getElementsByTagName('a')[0].getAttribute('href');

            // Отправка AJAX-запроса для перемещения файла или папки
            let xhr = new XMLHttpRequest();
            xhr.open('GET', 'index.php?action=move&file=' + encodeURIComponent(fileToMove) + '&destination=' + encodeURIComponent(destination));
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // Обработка успешного перемещения файла или папки
                        location.reload(); // Обновляем страницу, чтобы обновить список файлов
                    } else {
                        console.error('Ошибка при перемещении файла или папки.');
                    }
                }
            };
            xhr.send();

            return false;
        }

        // Назначение обработчиков событий для элементов списка файлов и папок
        let items = document.querySelectorAll('tr[draggable="true"]');
        [].forEach.call(items, function(item) {
            item.addEventListener('dragstart', handleDragStart, false);
            item.addEventListener('dragover', handleDragOver, false);
            item.addEventListener('drop', handleDrop, false);
        });
    </script>



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
            <tr draggable="true">
                <td><a href="index.php?dir=<?php echo urlencode($currentDir . $dir . '/'); ?>"><span style="padding-right: 5px">&#128193;</span><?php echo $dir; ?></a></td>
                <td>Папка</td>
                <td>-</td>
                <td class="actions">
                    <a href="index.php?action=delete&file=<?php echo urlencode($currentDir . $dir); ?>">Удалить</a>
                    <!--<a href="index.php?action=move&file=<?php echo urlencode($currentDir . $dir); ?>">Переместить</a>-->
                </td>
            </tr>
        <?php endforeach; ?>

        <?php foreach ($files as $file) : ?>
            <?php
            $filePath = $currentPath . '/' . $file;
            $fileSize = filesize($filePath);
            ?>
            <tr>

                <td><a href="<?php echo $filePath; ?>" download><span style="padding-right: 5px">&#128196;</span><?php echo $file; ?></a></td>
                <td>Файл</td>
                <td><?php echo formatFileSize($fileSize); ?></td>
                <td class="actions">
                    <a href="index.php?action=delete&file=<?php echo urlencode($currentDir . $file); ?>">Удалить</a>
                    <!--<a href="index.php?action=move&file=<?php echo urlencode($currentDir . $dir); ?>">Переместить</a>-->
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