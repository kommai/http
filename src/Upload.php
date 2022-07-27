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
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(match ($this->error) {
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
                default => sprintf('Unknown upload error #%d', $this->error),
            });
        }

        // TODO: more basic error checks

        if (@move_uploaded_file($this->temp, $path) === false) {
            throw new RuntimeException('The uploaded file was refused');
        }

        chmod($path, 0644);
    }
}
