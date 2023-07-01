<?php
$uploadDir = "uploads/";
$currentDir = $_GET['dir'] ?? '';
$currentPath = $uploadDir . $currentDir . '/';

// Обработка действий пользователя
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'add' && isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $currentDir = $_GET['dir'] ?? '';
        move_uploaded_file($fileTmpName, $currentPath . $fileName);
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
    } elseif ($action === 'move' && isset($_GET['file']) && isset($_GET['destination'])) {
        $fileToMove = $_GET['file'];
        $destination = $_GET['destination'];
        $currentDir = $_GET['dir'] ?? '';

        // Проверяем, существует ли целевая папка
        if (!is_dir($currentPath . $destination)) {
            mkdir($currentPath . $destination, 0777, true);
        }

        // Перемещаем файл или папку
        if (rename($uploadDir . $currentDir . '/' . $fileToMove, $uploadDir . $destination . '/' . $fileToMove)) {
            header('Location: index.php?dir=' . $destination);
            exit;
        } else {
            echo 'Ошибка при перемещении файла или папки.';
        }
    } elseif ($action === 'create_folder' && isset($_POST['folder_name'])) {
        $folderName = $_POST['folder_name'];
        $currentDir = $_GET['dir'] ?? '';
        $newFolderPath = $currentPath . $folderName;

        // Проверяем, существует ли папка с таким именем
        if (!is_dir($newFolderPath)) {
            if (mkdir($newFolderPath, 0777, true)) {
                header('Location: index.php?dir=' . $currentDir);
                exit;
            } else {
                echo 'Ошибка при создании папки.';
            }
        } else {
            echo 'Папка с таким именем уже существует.';
        }
    } elseif ($action === 'create_file' && isset($_POST['file_name']) && isset($_POST['file_content'])) {
        $fileName = $_POST['file_name'];
        $fileContent = $_POST['file_content'];
        $currentDir = $_GET['dir'] ?? '';
        $newFilePath = $currentPath . $fileName;

        // Проверяем, существует ли файл с таким именем
        if (file_put_contents($newFilePath, $fileContent) !== false) {
            header('Location: index.php?dir=' . $currentDir);
            exit;
        } else {
            echo 'Ошибка при создании файла.';
        }
    }
}

// Функция для удаления папки и ее содержимого
function deleteDirectory($dirPath): void
{
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
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    rmdir($dirPath);
}

// Получение списка файлов и папок в текущей директории
$currentDir = $_GET['dir'] ?? '';
$currentPath = $uploadDir . $currentDir;

$directories = array();
$files = array();

// Проверяем, существует ли директория
if (is_dir($currentPath)) {
    // Получаем список файлов и папок
    $items = scandir($currentPath);

    foreach ($items as $item) {
        $itemPath = $currentPath . '/' . $item;
        $item = basename($item, '.path: $itemPath');

        if (is_dir($itemPath)) {
            // Игнорируем "." и ".."
            if ($item === '.' || $item === '..') {
                continue;
            }

            // Добавляем папку в список
            $directories[] = $item;
        } else {
            // Добавляем файл в список
            $files[] = $item;
        }
    }
} else {
    echo 'Директория не существует.';
}
?>


<!DOCTYPE html>
<html lang="en-ru">
<head>
    <meta charset="utf-8">
    <title>File Explorer</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>
<h1>File Explorer</h1>

<div class="container">
    <h2><?php echo $currentDir; ?></h2>

    <div class="file-forms">
        <form action="index.php?action=add&dir=<?php echo urlencode($currentDir); ?>" method="post" enctype="multipart/form-data">
            <button type="button" class="create-folder-btn" id="createFolderBtn">Создать папку</button>
            <button type="button" class="browse-btn" id="file-upload-btn" onclick="document.getElementById('file-upload').click()">Загрузить</button>
            <input type="file" id="file-upload" name="file" style="display: none;" onchange="updateFileName(this)">
            <span id="file-name"></span>
            <input type="submit" value="Добавить" class="add-btn" id="addBtn" style="display: none;">
        </form>
    </div>
    <script>
        function updateFileName(input) {
            const fileName = input.files[0].name;
            document.getElementById('file-name').innerText = fileName;
            const addButton = document.getElementById('addBtn');
            if (fileName) {
                addButton.style.display = 'inline-block';
            } else {
                addButton.style.display = 'none';
            }
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
                        closeCreateFileModal();
                        location.reload();

                        document.getElementById('fileNameInput').value = '';
                        document.getElementById('fileContentInput').value = '';
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
        }
    </script>

    <button onclick="openCreateFileModal()" class="create-folder-btn">Создать файл</button>
    <div id="createFileModal" style="display: none;">
        <form id="createFileForm" action="index.php?action=create_file" class="modal-content">
            <h3>Создать файл</h3>
            <input type="text" id="fileNameInput" placeholder="Имя файла">
            <textarea id="fileContentInput" placeholder="Содержимое файла"></textarea>
            <button type="button" onclick="createFileRequest()">Создать</button>
            <button type="button" onclick="closeCreateFileModal()">Отмена</button>
        </form>
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
                <td><a data-dir="<?php echo urlencode($currentDir); ?>" href="index.php?dir=<?php echo urlencode($currentDir . $dir . '/'); ?>"><span style="padding-right: 5px">&#128193;</span><?php echo $dir; ?></a></td>
                <td>Папка</td>
                <td>-</td>
                <td class="actions">
                    <a href="#" class="delete-btn" onclick="confirmDelete('<?php echo urlencode($currentDir . $dir); ?>')">Удалить</a>
                    <a href="#" onclick="openMoveModal('<?php echo urlencode($currentDir . $dir); ?>')">Переместить</a>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php foreach ($files as $file) : ?>
            <?php
            $filePath = $currentPath . '/' . $file;
            $fileSize = filesize($filePath);
            ?>
            <tr>
                <td><a href="<?php echo $currentPath . $file; ?>" download><span>&#128196;</span><?php echo $file; ?></a></td>
                <td>Файл</td>
                <td><?php echo formatFileSize($fileSize); ?></td>
                <td class="actions">
                    <a href="#" class="delete-btn" onclick="confirmDelete('<?php echo urlencode($currentDir . $file); ?>')">Удалить</a>
                    <a href="#" onclick="openMoveModal('<?php echo urlencode($currentDir . $file); ?>')">Переместить</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div class="modal" id="moveModal" style="display: none;">
        <div class="modal-content">
            <h3>Перемещение файла или папки</h3>
            <label for="destinationFolderSelect">Куда:</label>
            <select id="destinationFolderSelect">
                <?php foreach ($directories as $dir) : ?>
                    <?php if ($dir !== $currentDir) : ?>
                        <option value="<?php echo urlencode($dir); ?>"><?php echo $dir; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <button onclick="moveItem()">Переместить</button>
            <button onclick="cancelMove()">Отмена</button>
        </div>
    </div>

    <script>
        let moveModal = document.getElementById('moveModal');
        let moveFilePath = '';

        function openMoveModal(filePath) {
            moveFilePath = filePath;
            moveModal.style.display = 'block';
        }

        function moveItem() {
            if (moveFilePath) {
                const destinationFolder = document.getElementById('destinationFolderSelect').value;
                window.location.href = 'index.php?action=move&file=' + encodeURIComponent(moveFilePath) + '&destination=' + encodeURIComponent(destinationFolder);
            }
        }

        function cancelMove() {
            moveFilePath = '';
            moveModal.style.display = 'none';
        }
    </script>

    <div class="modal" id="deleteConfirmationModal" style="display: none;">
        <div class="modal-content1">
            <h3>Подтверждение удаления</h3>
            <p>Вы уверены, что хотите удалить файл или папку?</p>
            <button onclick="deleteItem()">Удалить</button>
            <button onclick="cancelDelete()">Отмена</button>
        </div>
    </div>

    <script>
        let deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
        let deleteConfirmationFilePath = '';

        function confirmDelete(filePath) {
            deleteConfirmationFilePath = filePath;
            deleteConfirmationModal.style.display = 'block';
        }

        function deleteItem() {
            if (deleteConfirmationFilePath) {
                let xhr = new XMLHttpRequest();
                xhr.open('GET', 'index.php?action=delete&file=' + deleteConfirmationFilePath, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            location.reload();
                        } else {
                            console.error('Ошибка при удалении файла или папки.');
                        }
                    }
                };
                xhr.send();
            }
        }

        function cancelDelete() {
            deleteConfirmationFilePath = '';
            deleteConfirmationModal.style.display = 'none';
        }
    </script>
</div>
</body>
</html>


<?php
// Функция для форматирования размера файла
function formatFileSize($size): string
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $formattedSize = $size;
    $unitIndex = 0;

    while ($formattedSize >= 1024 && $unitIndex < count($units) - 1) {
        $formattedSize /= 1024;
        $unitIndex++;
    }

    $formattedSize = iconv('UTF-8', 'UTF-8//IGNORE', $formattedSize);
    return round($formattedSize, 2) . ' ' . $units[$unitIndex];
}
?>