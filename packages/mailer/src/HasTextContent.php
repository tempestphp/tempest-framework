<?php

namespace Tempest\Mail;

use Tempest\View\View;

interface HasTextContent
{
    public string|View|null $text { get; }
}