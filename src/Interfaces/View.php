<?php

namespace Tempest\Interfaces;

use Tempest\AppConfig;
use Tempest\View\RenderedView;

interface View
{
    public function render(AppConfig $appConfig): RenderedView;

    public function data(...$params): self;
}