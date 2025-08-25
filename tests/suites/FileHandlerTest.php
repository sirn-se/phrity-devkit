<?php

declare(strict_types=1);

namespace Phrity\DevKit\Test;

use PHPUnit\Framework\TestCase;
use Phrity\DevKit\FileHandler;
use RuntimeException;
use ValueError;

class FileHandlerTest extends TestCase
{
    public function tearDown(): void
    {
        $dir = escapeshellarg(dirname(__DIR__) . '/test-filehandler-area/');
        exec("rm -rf {$dir}");
    }

    public function testDirectory(): void
    {
        $handler = new FileHandler();
        $directory = dirname(__DIR__) . '/test-filehandler-area/directory';
        $this->assertFalse($handler->exists($directory));
        $this->assertFalse($handler->isFile($directory));
        $this->assertFalse($handler->isDirectory($directory));
        $this->assertFalse($handler->isReadable($directory));
        $this->assertTrue($handler->isWritable($directory));
        $this->assertEquals($directory, $handler->directory($directory));
        $this->assertEquals($directory, $handler->directory($directory));
        $this->assertTrue($handler->exists($directory));
        $this->assertFalse($handler->isFile($directory));
        $this->assertTrue($handler->isDirectory($directory));
        $this->assertTrue($handler->isReadable($directory));
        $this->assertTrue($handler->isWritable($directory));
    }

    public function testFile(): void
    {
        $handler = new FileHandler();
        $file = dirname(__DIR__) . '/test-filehandler-area/created/directory/file';
        $this->assertFalse($handler->exists($file));
        $this->assertFalse($handler->isFile($file));
        $this->assertFalse($handler->isDirectory($file));
        $this->assertFalse($handler->isReadable($file));
        $this->assertTrue($handler->isWritable($file));
        $this->assertTrue($handler->putContents($file, 'A'));
        $this->assertEquals('A', $handler->getContents($file));
        $this->assertTrue($handler->putContents($file, 'B'));
        $this->assertEquals('B', $handler->getContents($file));
        $this->assertTrue($handler->putContents($file, 'C', true));
        $this->assertEquals('BC', $handler->getContents($file));
        $this->assertTrue($handler->exists($file));
        $this->assertTrue($handler->isFile($file));
        $this->assertFalse($handler->isDirectory($file));
        $this->assertTrue($handler->isReadable($file));
        $this->assertTrue($handler->isWritable($file));
    }

    public function testCopy(): void
    {
        $handler = new FileHandler();
        $source = dirname(__DIR__) . '/test-filehandler-area/created/directory/source';
        $target = dirname(__DIR__) . '/test-filehandler-area/created/directory/target';
        $handler->putContents($source, 'A');
        $this->assertEquals($target, $handler->copy($source, $target));
        $this->assertEquals('A', $handler->getContents($target));
    }

    public function testTemplate(): void
    {
        $handler = new FileHandler();
        $source = dirname(__DIR__) . '/test-filehandler-area/created/directory/source';
        $target = dirname(__DIR__) . '/test-filehandler-area/created/directory/target';
        $handler->putContents($source, '{a}-{b}');
        $this->assertEquals($target, $handler->template($source, $target, ['a' => 'A', 'b' => 'B']));
        $this->assertEquals('A-B', $handler->getContents($target));
    }

    public function testRelative(): void
    {
        $handler = new FileHandler();
        $this->assertEquals('c/d', $handler->relative('a/b/c/d', 'a/b'));
    }

    public function testDirectoryFailure(): void
    {
        $directory = dirname(__DIR__) . '/test-filehandler-area/readonly';
        $handler = new FileHandler();
        $handler->directory($directory);
        chmod($directory, 0500);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Directory '{$directory}/sub' could not create directory.");
        $handler->directory("{$directory}/sub");
    }

    public function testFileFailure(): void
    {
        $file = dirname(__DIR__) . '/test-filehandler-area/file';
        $handler = new FileHandler();
        $handler->putContents($file, 'A');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed reading contents from '{$file}'.");
        $handler->getContents($file, -10);
    }
}
