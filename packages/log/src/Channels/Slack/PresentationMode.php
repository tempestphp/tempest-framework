<?php

namespace Tempest\Log\Channels\Slack;

enum PresentationMode
{
    /**
     * Shows the log message in-line without any formatting.
     */
    case INLINE;

    /**
     * Shows the log message as a Slack block.
     */
    case BLOCKS;

    /**
     * Shows the log message as a Slack block, including any context information.
     */
    case BLOCKS_WITH_CONTEXT;
}
