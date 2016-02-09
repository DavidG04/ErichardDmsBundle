<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 16:07
 */

namespace Erichard\DmsBundle\Tests\Entity;

use Erichard\DmsBundle\Entity\Document;
use Erichard\DmsBundle\Entity\DocumentNode;

/**
 * Class DocumentTest
 *
 * @package Erichard\DmsBundle\Tests\Entity
 */
class DocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * tesComputedFilenameWithException
     *
     * @expectedException \RuntimeException
     */
    public function tesComputedFilenameWithException()
    {
        $node = new DocumentNode();
        $document = new Document($node);

        $document->getComputedFilename();
    }

    /**
     * testComputedFilename
     *
     * @param mixed $document
     * @param mixed $filename
     *
     * @dataProvider getDocuments
     */
    public function testComputedFilename($document, $filename)
    {
        $this->assertEquals($filename, $document->getComputedFilename());
    }

    /**
     * getDocuments
     *
     * @return array
     */
    public function getDocuments()
    {
        $node = new DocumentNode();
        $document1 = new Document($node);
        $document1->setId(1)->setOriginalName('test.jpg');

        $document2 = new Document($node);
        $document2->setId(1234)->setOriginalName('test.doc');

        $document3 = new Document($node);
        $document3->setId(123400)->setOriginalName('test');

        return array(
            array(
                $document1,
                '00/00/00/00000001.jpg',
            ),
            array(
                $document2,
                '00/00/12/00001234.doc',
            ),
            array(
                $document3,
                '00/12/34/00123400.noext',
            ),
        );
    }
}
