<?php

namespace Tempest\Console\Components;

interface HasStaticComponent
{
    public function getStaticComponent(): StaticComponent;
}