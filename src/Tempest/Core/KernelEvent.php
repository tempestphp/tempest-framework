<?php

namespace Tempest\Core;

enum KernelEvent
{
    case BOOTED;
    case SHUTDOWN;
}