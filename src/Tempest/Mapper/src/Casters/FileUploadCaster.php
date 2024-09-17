<?php

declare(strict_types=1);

namespace App\Casters;

use Tempest\Http\Upload;
use Tempest\Mapper\Caster;

class FileUploadCaster implements Caster
{
    public function cast(mixed $input): ?string
    {
        if (! $input instanceof Upload) {
            throw new InvalidArgumentException('Expected an instance of Tempest\Http\Upload');
        }

        if ($value->getError() === UPLOAD_ERR_OK) {
            return $input->getClientFilename();
        }

        return null;
    }
}
