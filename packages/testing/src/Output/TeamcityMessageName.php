<?php

namespace Tempest\Testing\Output;

enum TeamcityMessageName: string
{
    case TEST_SWEET_STARTED = 'testSuiteStarted';
    case TEST_SWEET_FINISHED = 'testSuiteFinished';
    case TEST_STARTED = 'testStarted';
    case TEST_FINISHED = 'testFinished';
    case TEST_IGNORED = 'testIgnored';
    case TEST_FAILED = 'testFailed';
}
