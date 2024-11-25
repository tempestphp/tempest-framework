<?php

declare(strict_types=1);

namespace Tempest\Database\Casters;

use function filesize;
use function file_exists;
use Laminas\Diactoros\UploadedFile;
use Tempest\Http\Upload;
use Tempest\Mapper\Caster;
use const UPLOAD_ERR_OK;

final readonly class UploadCaster implements Caster
{
    public function cast(mixed $input): Upload
    {
        if ($input instanceof Upload) {
            return $input;
        }

        return new UploadedFile($input, filesize($input), UPLOAD_ERR_OK);
    }
}
