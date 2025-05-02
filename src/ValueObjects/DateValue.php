<?php

declare(strict_types=1);

class DateValue
{
    private string $value;

    public function __construct(string $date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException("Invalid date format: $date");
        }
        $this->value = $date;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function get(): string
    {
        return $this->value;
    }
}

