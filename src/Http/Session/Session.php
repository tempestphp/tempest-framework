<?php

namespace Tempest\Http\Session;

interface Session
{
    public function getId(): string;

    public function setId(string $id): void;

    public function getName(): string;

    public function setName(string $name): void;

    public function start(): void;

    public function save(): void;

    public function get(string $value, mixed $default = null): mixed;
}