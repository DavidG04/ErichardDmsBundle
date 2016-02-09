<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 01/02/2016
 * Time: 09:53
 */

namespace Erichard\DmsBundle\Service;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UploadManager
 *
 * @package Erichard\DmsBundle\Service
 */
class UploadManager
{
    /**
     * Container
     *
     * @var Container
     */
    protected $container;

    /**
     * constructor
     *
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * CreateResponse
     *
     * @return JsonResponse
     */
    public function createResponse()
    {
        $response = new JsonResponse();
        $response->expire();
        $response->setLastModified(new \DateTime());
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate', true);
        $response->headers->set('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    /**
     * getError
     *
     * @param string $errorCode
     *
     * @return array
     *
     * @SuppressWarnings("PMD")
     */
    public function getError($errorCode)
    {
        $messages = array(
            UPLOAD_ERR_INI_SIZE  => 'document.upload.error.filesize_bigger_than_allowed',
            UPLOAD_ERR_FORM_SIZE => 'document.upload.error.filesize_bigger_than_allowed',
            UPLOAD_ERR_PARTIAL => 'document.upload.error.file_partially_uploaded',
            UPLOAD_ERR_NO_FILE => 'document.upload.error.no_file_uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'document.upload.error.no_temporary_folder',
            UPLOAD_ERR_CANT_WRITE => 'document.upload.error.failed_to_write_to_disk',
        );
        $error = '';
        switch ($errorCode) {
            case 100:
                $error = 'Failed to open temp directory.';
                break;
            case 101:
                $error = 'Failed to open input stream.';
                break;
            case 102:
                $error = 'Failed to open output stream.';
                break;
            case 103:
                $error = $this->container->get('translator')->trans($messages[$_FILES['file']['error']]);
                break;
            case 105:
                $error = 'Failed to read filename from the request.';
                break;
        }

        return array(
            'jsonrpc' => '2.0',
            'id'      => 'id',
            'error'   => array(
                'code'    => $errorCode,
                'message' => $error,
            ),
        );
    }

    /**
     * getFilePath
     *
     * @param string  $origFileName
     * @param string  $targetDir
     * @param integer $chunks
     *
     * @return string
     */
    public function getFilePath($origFileName, $targetDir, $chunks)
    {
        $fileName = preg_replace('/[^\w\._]+/', '_', $origFileName);
        if ($chunks < 2 && is_file($targetDir.DIRECTORY_SEPARATOR.$fileName)) {
            $fileinfo = pathinfo($fileName);

            $count = 1;
            while (is_file($targetDir.DIRECTORY_SEPARATOR.$fileinfo['filename'].'_'.$count.'.'.$fileinfo['extension'])) {
                $count++;
            }

            $fileName = $fileinfo['filename'].'_'.$count.'.'.$fileinfo['extension'];
        }

        return $targetDir.DIRECTORY_SEPARATOR.$fileName;
    }

    /**
     * uploadFiles
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Exception
     *
     * @SuppressWarnings("PMD")
     */
    public function uploadFiles($request)
    {
        $response = $this->createResponse();
        $targetDir = $this->container->getParameter('dms.storage.tmp_path');
        $cleanupTargetDir = true;
        $maxFileAge = 5 * 3600;
        @set_time_limit(5 * 60);
        $chunk = $request->request->getInt('chunk', 0);
        $chunks = $request->request->getInt('chunks', 0);
        $origFileName = $request->request->get('name', '');
        if ('' === $origFileName) {
            $response->setData($this->getError(105));

            return $response;
        }
        $filePath = $this->getFilePath($origFileName, $targetDir, $chunks);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir);
        }

        if ($cleanupTargetDir) {
            if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
                while (($file = readdir($dir)) !== false) {
                    $tmpfilePath = $targetDir.DIRECTORY_SEPARATOR.$file;
                    if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
                        @unlink($tmpfilePath);
                    }
                }
                closedir($dir);
            } else {
                $response->setData($this->getError(100));

                return $response;
            }
        }
        $contentType = $request->headers->get('CONTENT_TYPE', $request->headers->get('HTTP_CONTENT_TYPE'));
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    $ins = @fopen($_FILES['file']['tmp_name'], "rb");
                    if ($ins) {
                        while ($buff = fread($ins, 4096)) {
                            fwrite($out, $buff);
                        }
                    } else {
                        $response->setData($this->getError(101));

                        return $response;
                    }
                    @fclose($ins);
                    @fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                } else {
                    $response->setData($this->getError(102));

                    return $response;
                }
            } else {
                $response->setData($this->getError(103));

                return $response;
            }
        } else {
            $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
            if ($out) {
                $ins = @fopen("php://input", "rb");
                if ($ins) {
                    while ($buff = fread($ins, 4096)) {
                        fwrite($out, $buff);
                    }
                } else {
                    $response->setData($this->getError(101));

                    return $response;
                }

                @fclose($ins);
                @fclose($out);
            } else {
                $response->setData($this->getError(102));

                return $response;
            }
        }
        if (!$chunks || $chunk == $chunks - 1) {
            rename("{$filePath}.part", $filePath);
        }
        $response->setData(array(
            'jsonrpc' => '2.0',
            'result'  => null,
            'id'      => 'id',
        ));

        return $response;
    }
}
