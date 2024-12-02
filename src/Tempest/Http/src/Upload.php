<?php

declare(strict_types=1);

namespace Tempest\Http;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Stringable;
use Tempest\Database\Casters\UploadCaster;
use Tempest\Mapper\CastWith;

#[CastWith(UploadCaster::class)]
final readonly class Upload implements Stringable
{
    public function __construct(
        private UploadedFileInterface $uploadedFile,
    ) {
    }

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

    public function __toString(): string
    {
        return (string) $this->getClientFilename();
    }
}
