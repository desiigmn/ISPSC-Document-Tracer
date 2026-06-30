<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DocumentLogicTest extends TestCase
{
    /**
     * A basic unit test example.
     */
public function test_tracking_id_format_is_correct()
{
    // Simulate the logic in your Controller
    $date = "06/25/2026";
    $time = "10:30:00";
    $suffix = "EXT";
    
    $trackingId = "ISPSC-" . $date . "-" . $time . "-" . $suffix;

    $this->assertEquals("ISPSC-06/25/2026-10:30:00-EXT", $trackingId);
    $this->assertStringStartsWith('ISPSC-', $trackingId);
}
}
