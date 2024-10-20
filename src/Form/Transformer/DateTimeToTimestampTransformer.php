<?php

namespace App\Form\Transformer;

use DateTime;
use Exception;
use Symfony\Component\Form\DataTransformerInterface;

class DateTimeToTimestampTransformer implements DataTransformerInterface {

    public function transform($value): DateTime
    {
        if ($value == null) {
            return new DateTime();
        }

        return DateTime::createFromFormat('Y-m-d H:i:s', $value);
    }

    public function reverseTransform(mixed $value): int
    {
        /** @var DateTime $date */
        $date = $value;

        return $date->getTimestamp();
    }
}
