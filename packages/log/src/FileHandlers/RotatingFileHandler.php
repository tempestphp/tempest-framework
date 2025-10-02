<?php

declare(strict_types=1);

namespace Tempest\Log\FileHandlers;

use DateTimeImmutable;
use InvalidArgumentException;
use Monolog\Handler\RotatingFileHandler as MonoRotatingFileHandler;
use Tempest\DateTime\DateTime;

final class RotatingFileHandler extends MonoRotatingFileHandler
{
    public const string FILE_PER_WEEK = 'Y-W';

    protected function setDateFormat(string $dateFormat): void
    {
        if (preg_match('{^[Yy](([/_.-]?m([/_.-]?d)?)|([/_.-]?W))?$}', $dateFormat) === 0) {
            throw new InvalidArgumentException(
                'Invalid date format - format must be one of '
                . 'RotatingFileHandler::FILE_PER_DAY ("Y-m-d"), RotatingFileHandler::FILE_PER_WEEK ("Y-W"), '
                . 'RotatingFileHandler::FILE_PER_MONTH ("Y-m") or RotatingFileHandler::FILE_PER_YEAR ("Y"), '
                . 'or you can set one of the date formats using slashes, underscores and/or dots instead of dashes.',
            );
        }

        $this->dateFormat = $dateFormat;
    }

    protected function getNextRotation(): DateTimeImmutable
    {
        $datetime = match (str_replace(['/', '_', '.'], '-', $this->dateFormat)) {
            self::FILE_PER_WEEK => DateTime::now()->startOfWeek()->plusDays(7),
            self::FILE_PER_MONTH => DateTime::now()->startOfMonth()->plusMonth(),
            self::FILE_PER_YEAR => DateTime::now()->startOfYear()->plusYear(),
            default => DateTime::now()->plusDay()->startOfDay(),
        };

        return $datetime->toNativeDateTime();
    }
}
