<?php

use PHPUnit\Framework\TestCase;

class FileExplorerTest extends TestCase
{
    public function testFileExplorer()
    {
        // Создание временной директории для загрузки файла
        $uploadDir = __DIR__ . '/uploads/';
        $tempDir = sys_get_temp_dir() . 'FileExplorerTest.php/';
        $tempUploadDir = $tempDir . 'uploads/';
        mkdir($tempUploadDir);

        // Создание временного файла для загрузки
        $tempFile = tempnam($tempUploadDir, 'test');
        $uploadedFile = [
            'name' => 'test.txt',
            'tmp_name' => $tempFile,
        ];

        // Установка параметров GET запроса
        $_GET['action'] = 'add';
        $_GET['dir'] = '';

        // Установка параметров POST запроса
        $_FILES['file'] = $uploadedFile;

        // Вызов обработчика действия
        include 'index.php';

        // Проверка, что файл был перемещен в нужную директорию
        $this->assertFileExists($uploadDir . 'Grand Theft Auto San Andreas by Igruha.torrent');

        // Очистка временных файлов и директорий
        unlink($tempFile);
        rmdir($tempUploadDir);
    }

    public function testDeleteFile()
    {
        // Создание временной директории и файла для удаления
        $uploadDir = __DIR__ . '/uploads/';
        mkdir($uploadDir . 'test');
        touch($uploadDir . 'test/test.txt');

        // Установка параметров GET запроса
        $_GET['action'] = 'delete';
        $_GET['file'] = 'test/test.txt';

        // Вызов обработчика действия
        include 'index.php';

        // Проверка, что файл был удален
        $this->assertFileNotExists($uploadDir . 'test/test.txt');

        // Очистка временных файлов и директорий
        rmdir($uploadDir . 'test');
    }

    public function testMoveFile()
    {
        // Создание временных директорий и файла для перемещения
        $uploadDir = __DIR__ . '/uploads/';
        mkdir($uploadDir . 'source');
        mkdir($uploadDir . 'destination');
        touch($uploadDir . 'source/test.txt');

        // Установка параметров GET запроса
        $_GET['action'] = 'move';
        $_GET['file'] = 'source/test.txt';
        $_GET['destination'] = 'destination';

        // Вызов обработчика действия
        include 'index.php';

        // Проверка, что файл был перемещен в нужную директорию
        $this->assertFileExists($uploadDir . 'destination/test.txt');
        $this->assertFileNotExists($uploadDir . 'source/test.txt');

        // Очистка временных файлов и директорий
        rmdir($uploadDir . 'destination');
        rmdir($uploadDir . 'source');
    }


}