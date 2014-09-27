<?php

/**
 * RedisTest.php
 *
 * @author Dennis de Greef <github@link0.net>
 */
namespace Link0\Profiler\PersistenceHandler;
use Link0\Profiler\Profile;
use Predis\Client;

/**
 * Redis Test
 *
 * @package Link0\Profiler
 */
class RedisTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Redis $persistenceHandler
     */
    protected $persistenceHandler;

    /**
     * Setup objects for all tests
     */
    public function setUp()
    {
        $this->persistenceHandler = new Redis();
    }

    /**
     * Tests if the Redis PersistenceHandler can be instantiated
     */
    public function testCanBeInstantiated()
    {
        $persistenceHandler = new Redis();
        $this->assertInstanceOf('\Link0\Profiler\PersistenceHandler\Redis', $persistenceHandler);
        $this->assertInstanceOf('\Predis\Client', $persistenceHandler->getEngine());
    }

    /**
     * Tests whether the Engine can be injected
     */
    public function testSetEngine()
    {
        $predisClient = new Client();
        $this->persistenceHandler->setEngine($predisClient);
        $this->assertSame($predisClient, $this->persistenceHandler->getEngine());
    }
}