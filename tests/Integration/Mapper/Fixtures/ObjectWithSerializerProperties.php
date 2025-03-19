<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Stringable;
use Tempest\Mapper\SerializeWith;

final class ObjectWithSerializerProperties
{
    public string $stringProp = 'a';

    public Stringable $stringableProp;

    public int $intProp = 1;

    public float $floatProp = 0.1;

    public bool $boolProp = true;

    public array $arrayProp = ['a'];

    #[SerializeWith(DoubleStringSerializer::class)] public string $serializeWithProp = 'a';

    public DoubleStringObject $doubleStringProp;

    public JsonSerializableObject $jsonSerializableObject;

    public SerializableObject $serializableObject;

    public DateTimeImmutable $dateTimeImmutableProp;

    public DateTime $dateTimeProp;

    public DateTimeInterface $dateTimeInterfaceProp;

    public function __construct()
    {
        $this->stringableProp = \Tempest\Support\str('a');
        $this->doubleStringProp = new DoubleStringObject('a');
        $this->jsonSerializableObject = new JsonSerializableObject();
        $this->serializableObject = new SerializableObject();
        $this->dateTimeImmutableProp = new DateTimeImmutable('2025-01-01');
        $this->dateTimeProp = new DateTime('2025-01-01');
        $this->dateTimeInterfaceProp = new DateTimeImmutable('2025-01-01');
    }
}