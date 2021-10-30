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
}
