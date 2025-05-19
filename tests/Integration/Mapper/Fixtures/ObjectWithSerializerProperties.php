<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use DateTime as NativeDateTime;
use DateTimeImmutable as NativeDateTimeImmutable;
use DateTimeInterface as NativeDateTimeInterface;
use Stringable;
use Tempest\DateTime\DateTime;
use Tempest\Mapper\SerializeWith;

// @mago-expect maintainability/too-many-properties
final class ObjectWithSerializerProperties
{
    public string $stringProp = 'a';

    public Stringable $stringableProp;

    public int $intProp = 1;

    public float $floatProp = 0.1;

    public bool $boolProp = true;

    public array $arrayProp = ['a'];

    #[SerializeWith(DoubleStringSerializer::class)]
    public string $serializeWithProp = 'a';

    public DoubleStringObject $doubleStringProp;

    public JsonSerializableObject $jsonSerializableObject;

    public SerializableObject $serializableObject;

    public NativeDateTimeImmutable $nativeDateTimeImmutableProp;

    public NativeDateTime $nativeDateTimeProp;

    public NativeDateTimeInterface $nativeDateTimeInterfaceProp;

    public DateTime $dateTimeProp;

    public function __construct()
    {
        $this->stringableProp = \Tempest\Support\str('a');
        $this->doubleStringProp = new DoubleStringObject('a');
        $this->jsonSerializableObject = new JsonSerializableObject();
        $this->serializableObject = new SerializableObject();
        $this->nativeDateTimeImmutableProp = new NativeDateTimeImmutable('2025-01-01');
        $this->nativeDateTimeProp = new NativeDateTime('2025-01-01');
        $this->nativeDateTimeInterfaceProp = new NativeDateTimeImmutable('2025-01-01');
        $this->dateTimeProp = DateTime::parse('2025-01-01');
    }
}
