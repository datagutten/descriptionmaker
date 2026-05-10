<?php


use datagutten\descriptionMaker\utils;
use datagutten\tools\files\files;
use PHPUnit\Framework\TestCase;

class utilsTest extends TestCase
{
    public function testFile_path()
    {
        $file = utils::file_path(__FILE__, 'torrent');
        $this->assertEquals(files::path_join(__DIR__, 'utilsTest.torrent'), $file);
    }
    public function testFile_pathFolder()
    {
        $file = utils::file_path(__DIR__, 'torrent');
        $folder = realpath(__DIR__.'/..');
        $this->assertEquals(files::path_join($folder, 'tests.torrent'), $file);
    }

    public function testDescriptionFile()
    {
        $path = files::path_join(sys_get_temp_dir(), 'upload_file.H.264.mkv');
        $desc_file = utils::description_file($path);
        $this->assertEquals(files::path_join(sys_get_temp_dir(), 'upload_file.H.264.txt'), $desc_file);
    }

    public function testDescriptionFileFolder()
    {
        $path = files::path_join(sys_get_temp_dir(), 'upload_folder H.264');
        mkdir($path, 0777, true);
        $desc_file = utils::description_file($path);
        $this->assertEquals(files::path_join(sys_get_temp_dir(), 'upload_folder H.264.txt'), $desc_file);
    }
}
