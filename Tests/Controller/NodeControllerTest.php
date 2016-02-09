<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 16:07
 */

namespace Erichard\DmsBundle\Tests\Controller;

use Erichard\DmsBundle\Tests\Controller\App\FunctionalTest;

/**
 * Class NodeControllerTest
 *
 * @package Erichard\DmsBundle\Tests\Controller
 */
class NodeControllerTest extends FunctionalTest
{
    /**
     * test_node_creation
     */
    public function testNodCreation()
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/en/video/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $saveButton = $crawler->selectButton('Save changes');
        $form = $saveButton->form([
            'dms_node[name]'    => 'New DocumentNode',
            'dms_node[enabled]' => true,
        ]);
        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/en/video', $client->getResponse()->headers->get('location'));
    }

    /**
     * testNodeUpdate
     */
    public function testNodeUpdate()
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/en/new-documentnode/edit');

        $saveButton = $crawler->selectButton('Save changes');
        $form = $saveButton->form([
            'dms_node[name]'    => 'My Document',
            'dms_node[enabled]' => true,
        ]);
        $client->submit($form);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/en/new-documentnode', $client->getResponse()->headers->get('location'));
    }

    /**
     * testNodeUpdateWithTranslation
     */
    public function testNodeUpdateWithTranslation()
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/en/new-documentnode/edit?_locale=en&_translation=fr');

        $saveButton = $crawler->selectButton('Save changes');
        $form = $saveButton->form([
            'dms_node[name]'    => 'Mon Document',
            'dms_node[enabled]' => true,
        ]);
        $client->submit($form);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/en/new-documentnode', $client->getResponse()->headers->get('location'));
    }

    /**
     * testNodeTranslations
     */
    public function testNodeTranslations()
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/en/new-documentnode');

        $this->assertEquals('My Document', $crawler->filter('.breadcrumb li.active')->text());

        $crawler = $client->request('GET', '/fr/new-documentnode');

        $this->assertEquals('Mon Document', $crawler->filter('.breadcrumb li.active')->text());
    }
}
