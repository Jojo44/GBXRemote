<?php namespace tests;

require_once(__DIR__.'/../GBXRemote/GBXRemote.php');

use GBXRemote\GBXRemote;

/**
 * This class tests GBXRemote\GBXRemote class
 *
 * @author Jojo <jojo@zero-clan.org>
 */

class GBXRemoteTest extends \PHPUnit_Framework_TestCase {

    /** @test */
    public function connection()
    {
        $client = new GBXRemote();
        $this->assertTrue($client->connect("localhost", 5000));
        $this->assertTrue($client->query("Authenticate", "SuperAdmin", "SuperAdminPassword"));
        $this->assertNotEquals(false, $client->query('GetVersion'));
        $this->assertNotEquals(false, $client->GetStatus());
        $this->assertNotEquals(false, $client->GetMapList(10, 0));
        $client->close();
    }
}
 