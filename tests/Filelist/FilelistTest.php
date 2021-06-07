<?php declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Filelist;

use DavidLienhard\Database\QueryValidator\Filelist\Filelist;
use PHPUnit\Framework\TestCase;

class FilelistTestCase extends TestCase
{
    /**
     * @covers DavidLienhard\Database\QueryValidator\Filelist\Filelist
     * @test
     */
    public function testCanBeCreated(): void
    {
        $list = new Filelist;
        $this->assertInstanceOf(Filelist::class, $list);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Filelist\Filelist
     * @test
     */
    public function testCanGetFilelist(): void
    {
        $list = new Filelist;
        $this->assertEquals([], $list->getFiles());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Filelist\Filelist
     * @test
     */
    public function testCanGetFilecount(): void
    {
        $list = new Filelist;
        $this->assertEquals(0, $list->getFilecount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Filelist\Filelist
     * @test
     */
    public function testCanAddFilesWithContructor(): void
    {
        $data = [ new \SplFileinfo("file.php") ];
        $list = new Filelist($data);

        $this->assertEquals($data, $list->getFiles());
        $this->assertEquals(1, $list->getFilecount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Filelist\Filelist
     * @test
     */
    public function testCannotAddStringAsFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Filelist([ "file.php" ]);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Filelist\Filelist
     * @test
     */
    public function testCanAddFileWithAddFunction(): void
    {
        $file = new \SplFileinfo("file.php");

        $list = new Filelist;
        $list->addFile($file);

        $this->assertEquals([ $file ], $list->getFiles());
        $this->assertEquals(1, $list->getFilecount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Filelist\Filelist
     * @test
     */
    public function testCanAddMultipleFilesWithAddFunction(): void
    {
        $file1 = new \SplFileinfo("file.php");
        $file2 = new \SplFileinfo("test.php");

        $list = new Filelist;
        $list->addFile($file1);
        $list->addFile($file2);

        $this->assertEquals([ $file1, $file2 ], $list->getFiles());
        $this->assertEquals(2, $list->getFilecount());
    }
}
