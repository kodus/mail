<?php

namespace Kodus\Mail\Test\Unit;

use Kodus\Mail\MIMEWriter;

/**
 * Overrides boundary name creation for testing
 */
class MockMIMEWriter extends MIMEWriter
{
    private $index = 0;

    protected function createMultipartBoundaryName($prefix)
    {
        $this->index += 1;

        return "===={$prefix}-{$this->index}====";
    }
}
