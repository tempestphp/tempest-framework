<?php

namespace Tempest\Route;

enum Status
{
    case HTTP_200;
    case HTTP_400;
    case HTTP_404;
    case HTTP_500;
}
