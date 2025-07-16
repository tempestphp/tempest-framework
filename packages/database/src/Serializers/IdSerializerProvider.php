<?php

namespace Tempest\Database\Serializers;

use Tempest\Core\KernelEvent;
use Tempest\Database\Id;
use Tempest\EventBus\EventHandler;
use Tempest\Mapper\SerializerFactory;

final readonly class IdSerializerProvider
{
    public function __construct(
        private SerializerFactory $serializerFactory,
    ) {}

    #[EventHandler(KernelEvent::BOOTED)]
    public function __invoke(KernelEvent $_event): void
    {
        $this->serializerFactory->addSerializer(Id::class, IdSerializer::class);
    }
}
