<?php

declare(strict_types=1);

namespace Kommai\Http;

use RuntimeException;

class Upload
{
    public string $name; // readonly
    public string $type; // readonly
    public string $temp; // readonly
    public int $error; // readonly
    public int $size; // readonly

    public function __construct(
        string $name,
        string $type,
        string $temp,
        int $error,
        int $size
    )
    {
        $this->name = $name;
        $this->type = $type;
        $this->temp = $temp;
        $this->error = $error;
        $this->size = $size;
    }

    public static function createFromGlobal(array $file): self
    {
        return new self($file['name'], $file['type'], $file['tmp_name'], $file['error'], $file['size']);
    }

    public function save(string $path): void
    {
        if (@move_uploaded_file($this->temp, $path) === false) {
            throw new RuntimeException('The uploaded file was refused');
        }

        chmod($path, 0644);
    }
}
