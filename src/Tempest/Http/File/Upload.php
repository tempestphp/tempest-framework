<?php

declare(strict_types=1);

namespace Tempest\Http\File;

use SplFileInfo;
use Tempest\Http\Exceptions\FileNotFoundException;

final class Upload extends SplFileInfo
{
    public function __construct(
        public string $path,
        public string $name,
        public ?string $mimeType = null,
        public ?int $error = null
    )
    {
        if(!file_exists($path)) {
            throw new FileNotFoundException($path);
        }

        parent::__construct($path);
        $this->mimeType ??= finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->getPathname());
        $this->error = $error ?? UPLOAD_ERR_OK;
    }

    public function move(string $directory, ?string $name = null): self
    {
        if(!is_dir($directory) && @mkdir($directory)) {
            throw new \Exception("Unable to create the {$directory} directory.");
        } elseif(!is_writable($directory)) {
            throw new \Exception("Unable to write in the {$directory} directory.");
        }
        $name ??= $this->getFilename();
        $dest = $directory . DIRECTORY_SEPARATOR . $name;

        return new self($dest, $name);
    }
}