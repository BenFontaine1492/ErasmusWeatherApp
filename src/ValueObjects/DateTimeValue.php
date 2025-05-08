<?php

declare(strict_types=1);

class DateValue
{
    private string $value;

    public function __construct(string $date)
    {   
        $decodedDate = urldecode($date);
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $decodedDate);
        if (!$d || $d->format('Y-m-d H:i:s') !== $decodedDate) {
            throw new InvalidArgumentException("Invalid date format: $decodedDate");
        }
        $this -> value = $decodedDate;
    }

    public function __toString(): string
    {
        return $this -> value;
    }

    public function get(): string
    {
        return $this -> value;
    }
}

