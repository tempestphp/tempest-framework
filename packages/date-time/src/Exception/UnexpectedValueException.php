<?php

declare(strict_types=1);

namespace Tempest\DateTime\Exception;

use UnexpectedValueException as PhpUnexpectedValueException;

final class UnexpectedValueException extends PhpUnexpectedValueException implements DateTimeException
{
    /**
     * Exception for a mismatching year value, indicating an unexpected discrepancy between provided and expected values.
     *
     * @param int $provided_year The year value provided by the user.
     * @param int $calendar_year The year value as determined by calendar calculations.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @internal
     */
    public static function forYear(int $provided_year, int $calendar_year): self
    {
        return new self(sprintf(
            'Unexpected year value encountered. Provided "%d", but the calendar expects "%d". Check the year for accuracy and ensure it\'s within the supported range.',
            $provided_year,
            $calendar_year,
        ));
    }

    /**
     * Exception for a mismatching month value, suggesting the provided month does not match calendar expectations.
     *
     * @param int $provided_month The month value provided by the user.
     * @param int $calendar_month The month value as determined by calendar calculations.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @internal
     */
    public static function forMonth(int $provided_month, int $calendar_month): self
    {
        return new self(sprintf(
            'Unexpected month value encountered. Provided "%d", but the calendar expects "%d". Ensure the month is within the 1-12 range and matches the specific year context.',
            $provided_month,
            $calendar_month,
        ));
    }

    /**
     * Exception for a mismatching day value, highlighting a conflict between the provided and calendar-validated day.
     *
     * @param int $provided_day The day value provided by the user.
     * @param int $calendar_day The day value as confirmed by calendar validation.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @internal
     */
    public static function forDay(int $provided_day, int $calendar_day): self
    {
        return new self(sprintf(
            'Unexpected day value encountered. Provided "%d", but the calendar expects "%d". Ensure the day is valid for the given month and year, considering variations like leap years.',
            $provided_day,
            $calendar_day,
        ));
    }

    /**
     * Exception for a mismatching hours value, indicating the provided hours do not match the expected calendar value.
     *
     * @param int $provided_hours The hours value provided by the user.
     * @param int $calendar_hours The hours value as determined by calendar calculations.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @internal
     */
    public static function forHours(int $provided_hours, int $calendar_hours): self
    {
        return new self(sprintf(
            'Unexpected hours value encountered. Provided "%d", but the calendar expects "%d". Ensure the hour falls within a 24-hour day.',
            $provided_hours,
            $calendar_hours,
        ));
    }

    /**
     * Exception for a mismatching minutes value, noting a divergence between provided and expected minute values.
     *
     * @param int $provided_minutes The minutes value provided by the user.
     * @param int $calendar_minutes The minutes value as per calendar validation.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @internal
     */
    public static function forMinutes(int $provided_minutes, int $calendar_minutes): self
    {
        return new self(sprintf(
            'Unexpected minutes value encountered. Provided "%d", but the calendar expects "%d". Check the minutes value for errors and ensure it\'s within the 0-59 range.',
            $provided_minutes,
            $calendar_minutes,
        ));
    }

    /**
     * Exception for a mismatching seconds value, indicating a difference between the provided and the calendar-validated second.
     *
     * @param int $provided_seconds The seconds value provided by the user.
     * @param int $calendar_seconds The seconds value as validated by the calendar.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @internal
     */
    public static function forSeconds(int $provided_seconds, int $calendar_seconds): self
    {
        return new self(sprintf(
            'Unexpected seconds value encountered. Provided "%d", but the calendar expects "%d". Ensure the seconds are correct and within the 0-59 range.',
            $calendar_seconds,
            $provided_seconds,
        ));
    }
}
