<?php

namespace Phrity\DevKit;

use RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;

class FileHandler
{
    public function exists(string $path, bool $require = false): bool
    {
        $exists = file_exists($path);
        return $this->require($exists, $require, "File '{$path}' do not exist.");
    }

    public function isFile(string $path, bool $require = false): bool
    {
        $isFile = $this->exists($path, $require) && is_file($path);
        return $this->require($isFile, $require, "File '{$path}' exists but is not a regular file.");
    }

    public function isDirectory(string $path, bool $require = false): bool
    {
        $isDirectory = $this->exists($path, $require) && is_dir($path);
        return $this->require($isDirectory, $require, "File '{$path}' exists but is not a directory.");
    }

    public function isReadable(string $path, bool $require = false): bool
    {
        $isReadable = $this->exists($path, $require) && is_readable($path);
        return $this->require($isReadable, $require, "File '{$path}' is not readable.");
    }

    public function isWritable(string $path, bool $require = false): bool
    {
        $isWritable = is_writable($path);
        if (!$isWritable && $directory = dirname($path)) {
            $isWritable = $this->isWritable($directory);
        }
        return $this->require($isWritable, $require, "File '{$path}' is not writable.");
    }

    public function getContents(string $path, int $offset = 0): string
    {
        $this->isFile($path, true);
        $this->isReadable($path, true);
        $contents = @file_get_contents(filename: $path, offset: $offset);
        if ($contents === false) {
            throw new RuntimeException("Failed reading contents from '{$path}'.");
        }
        return $contents;
    }

    public function putContents(string $path, string $contents, bool $append = false): bool
    {
        $this->isWritable($path, true);
        $this->directory(dirname($path));
        $result = file_put_contents($path, $contents, $append ? FILE_APPEND : 0);
        $this->require($result !== false, true, "Failed writing contents to '{$path}'.");
        return true;
    }

    /**
     * @param array<string,string> $replacers
     */
    public function template(string $sourcePath, string $targetPath, array $replacers): string
    {
        $contents = $this->getContents($sourcePath);
        foreach ($replacers as $key => $value) {
            $contents = str_replace('{' . $key . '}', $value, $contents);
        }
        $this->putContents($targetPath, $contents);
        return realpath($targetPath) ?: $targetPath;
    }

    public function copy(string $sourcePath, string $targetPath): string
    {
        $this->isReadable($sourcePath, true);
        $this->isWritable($targetPath, true);
        $this->directory(dirname($targetPath));
        $copied = copy($sourcePath, $targetPath);
        $this->require($copied, true, "Failed copy file '{$sourcePath}' to '{$targetPath}'.");
        return realpath($targetPath) ?: $targetPath;
    }

    public function directory(string $path): string
    {
        if ($this->exists($path)) {
            $this->isDirectory($path, true);
            return realpath($path) ?: $path;
        }
        if (!@mkdir(directory: $path, recursive: true)) {
            throw new RuntimeException("Directory '{$path}' could not create directory.");
        }
        return realpath($path) ?: $path;
    }

    public function relative(string $path, string $basepath): string
    {
        return implode('/', array_filter(array_map(function ($path, $basepath) {
            return $path == $basepath ? null : $path;
        }, explode('/', $path), explode('/', $basepath))));
    }

    private function require(bool $result, bool $require, string $error): bool
    {
        return $result || !$require ? $result : throw new RuntimeException($error);
    }
}
