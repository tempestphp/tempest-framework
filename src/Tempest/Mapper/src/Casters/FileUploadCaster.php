<?php

declare(strict_types=1);

namespace App\Mapper\Casters;

use InvalidArgumentException;
use Tempest\Http\Upload;
use Tempest\Mapper\Caster;

final readonly class FileUploadCaster implements Caster
{
    public function cast(mixed $input): ?string
    {
        if (! $input instanceof Upload) {
            throw new InvalidArgumentException('Expected an instance of Tempest\Http\Upload');
        }

        if ($input->getError() === UPLOAD_ERR_OK) {
            return $input->getClientFilename();
        }

        return null;
    }
}
