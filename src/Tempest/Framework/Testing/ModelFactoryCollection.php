<?php

namespace Tempest\Framework\Testing;

/** @template TModelClass */
final readonly class ModelFactoryCollection
{
    public function __construct(
        /** @var ModelFactory<TModelClass> */
        private ModelFactory $modelFactory,
        private int|array $items,
    ) {}

    /**
     * Make instances of the model class.
     *
     * @return TModelClass[]
     */
    public function make(): array
    {
        $items = [];

        if (is_int($this->items)) {
            for ($i = 0; $i < $this->items; $i++) {
                $items[] = $this->modelFactory->make();
            }
        } else {
            foreach ($this->items as $item) {
                $items[] = $this->modelFactory->with(...$item)->make();
            }
        }

        return $items;
    }

    /**
     *  Make instances of the model class and save them to the database.
     *
     * @return TModelClass[]
     */
    public function save(): array
    {
        $items = $this->make();

        foreach ($items as $item) {
            $item->save();
        }

        return $items;
    }
}
