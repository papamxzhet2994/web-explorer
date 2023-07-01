<?php
require_once 'C:\Users\Вдадимир Павленко\Desktop\wed-explorer\index.php';
use PHPUnit\Framework\TestCase;

class FileExplorerTest extends TestCase
{
    public function testFormatFileSize()
    {
        $size1 = 1023;
        $expectedResult1 = '1023 B';
        $this->assertEquals($expectedResult1, formatFileSize($size1));

        $size2 = 1024;
        $expectedResult2 = '1 KB';
        $this->assertEquals($expectedResult2, formatFileSize($size2));

        $size3 = 1048576;
        $expectedResult3 = '1 MB';
        $this->assertEquals($expectedResult3, formatFileSize($size3));

        $size4 = 1099511627776;
        $expectedResult4 = '1 TB';
        $this->assertEquals($expectedResult4, formatFileSize($size4));
    }

    public function testCreateFolder()
    {
        // Тестирование создания папки
        $folderName = 'test_folder';
        $dir = 'test_dir';

        // Создаем папку
        $_POST['folder_name'] = $folderName;
        $_GET['dir'] = $dir;

        $this->assertTrue(mkdir('uploads/' . $dir . '/' . $folderName, 0777, true));
    }

    public function testCreateFile()
    {
        // Тестирование создания файла
        $fileName = 'test_file.txt';
        $fileContent = 'Test content';
        $dir = 'test_dir';

        // Создаем файл
        $_POST['file_name'] = $fileName;
        $_POST['file_content'] = $fileContent;
        $_GET['dir'] = $dir;

        $this->assertTrue(file_put_contents('uploads/' . $dir . '/' . $fileName, $fileContent) !== false);
    }

    public function testDeleteFile()
    {
        // Тестирование удаления файла
        $fileName = 'test_file.txt';
        $dir = 'test_dir';

        // Удаляем файл
        $_GET['action'] = 'delete';
        $_GET['file'] = $fileName;
        $_GET['dir'] = $dir;

        $filePath = 'uploads/' . $dir . '/' . $fileName;

        if (is_dir($filePath)) {
            // Удаляем папку и ее содержимое
            deleteDirectory($filePath);
        } elseif (is_file($filePath)) {
            // Удаляем файл
            unlink($filePath);
        }

        $this->assertFalse(file_exists($filePath));
    }
}
