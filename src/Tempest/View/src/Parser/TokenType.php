<?php

namespace Tempest\View\Parser;

enum TokenType
{
    case OPEN_TAG_START;
    case OPEN_TAG_END;
    case ATTRIBUTE_NAME;
    case ATTRIBUTE_VALUE;
    case SELF_CLOSING_TAG;
    case SELF_CLOSING_TAG_END;
    case CLOSING_TAG;
    case COMMENT;
    case PHP;
    case CONTENT;
    case DOCTYPE;
}
