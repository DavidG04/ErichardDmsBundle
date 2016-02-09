<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:42
 */

namespace Erichard\DmsBundle\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class FileResponse
 *
 * @package Erichard\DmsBundle\Response
 */
class FileResponse extends Response
{
    /**
     * Filename
     *
     * @var string
     */
    private $filename;

    /**
     * setFilename
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * sendContent
     *
     * @return string
     */
    public function sendContent()
    {
        if (empty($this->filename)) {
            return '';
        }

        $file = fopen($this->filename, 'rb');
        $out = fopen('php://output', 'wb');

        stream_copy_to_stream($file, $out);

        fclose($out);
        fclose($file);
    }
}
