<?php

namespace Tempest\Database\Serializers;

use Tempest\Core\KernelEvent;
use Tempest\Database\PrimaryKey;
use Tempest\EventBus\EventHandler;
use Tempest\Mapper\SerializerFactory;

final readonly class PrimaryKeySerializerProvider
{
    public function __construct(
        private SerializerFactory $serializerFactory,
    ) {}

    #[EventHandler(KernelEvent::BOOTED)]
    public function __invoke(KernelEvent $_event): void
    {
        $this->serializerFactory->addSerializer(PrimaryKey::class, PrimaryKeySerializer::class);
    }
}
