<?php

declare(strict_types=1);

namespace Tempest\Http;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

final readonly class Upload
{
    public function __construct(
        private UploadedFileInterface $uploadedFile,
    ) {}

    public function getStream(): StreamInterface
    {
        return $this->uploadedFile->getStream();
    }

    public function moveTo(string $targetPath): void
    {
        $this->uploadedFile->moveTo($targetPath);
    }

    public function getSize(): ?int
    {
        return $this->uploadedFile->getSize();
    }

    public function getError(): int
    {
        return $this->uploadedFile->getError();
    }

    public function getClientFilename(): ?string
    {
        return $this->uploadedFile->getClientFilename();
    }

    public function getClientMediaType(): ?string
    {
        return $this->uploadedFile->getClientMediaType();
    }
}
