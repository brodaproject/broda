<?php

namespace Broda\Tests;


abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new \RuntimeException($errstr . " on line " . $errline . " in file " . $errfile);
        });
    }

    protected function tearDown()
    {
        parent::tearDown();
        restore_error_handler();
    }
} 