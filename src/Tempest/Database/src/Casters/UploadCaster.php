<?php

declare(strict_types=1);

namespace Tempest\Database\Casters;

use function filesize;
use Laminas\Diactoros\UploadedFile;
use Tempest\Http\Upload;
use Tempest\Mapper\Caster;
use function Tempest\root_path;
use const UPLOAD_ERR_OK;

final readonly class UploadCaster implements Caster
{
    public function cast(mixed $input): Upload
    {
        if ($input instanceof Upload) {
            $movePath = root_path((string) $input);
            $input->moveTo($movePath);

            return $input;
        }

        $movePath = root_path($input);

        $uploadedFile = new Upload(
            new UploadedFile(
                $movePath,
                filesize($movePath),
                UPLOAD_ERR_OK
            )
        );

        $uploadedFile->moveTo(root_path($movePath));

        return $uploadedFile;
    }
}
