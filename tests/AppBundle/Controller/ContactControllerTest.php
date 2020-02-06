<?php
/**
 * ContactControllerTest
 *
 * @copyright Copyright © 2020 TravelCo. All rights reserved.
 * @author    sanaeefar.saeed@gmail.com
 */

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ContactControllerTest
 * @package Tests\AppBundle\Controller
 */
class ContactControllerTest extends WebTestCase
{
    /**
     * Test Home Page
     */
    public function testHomeAction(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}