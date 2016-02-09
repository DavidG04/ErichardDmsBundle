<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:25
 */

namespace Erichard\DmsBundle\Faker;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class DmsProvider
 *
 * @package Erichard\DmsBundle\Faker
 */
class DmsProvider
{
    /**
     * const IMAGE_PROVIDER
     */
    const IMAGE_PROVIDER = "http://lorempixel.com/%d/%d";

    /**
     * Container
     *
     * @var Container
     */
    protected $container;

    /**
     * DmsProvider constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * slug
     *
     * @param string $text
     * @param string $glue
     *
     * @return string
     */
    public static function slug($text, $glue = '-')
    {
        return Urlizer::urlize($text, $glue);
    }

    /**
     * imageLink
     *
     * @param integer $width
     * @param integer $height
     *
     * @return string
     */
    public static function imageLink($width, $height)
    {
        return sprintf(self::IMAGE_PROVIDER, $width, $height);
    }

    /**
     * image
     *
     * @param string       $dir
     * @param null|integer $width
     * @param null|integer $height
     *
     * @return mixed|string
     */
    public function image($dir, $width = null, $height = null)
    {
        $width = $width ? : rand(100, 300);
        $height = $height ? : rand(100, 300);

        $imageName = sprintf('%s/%s/%s.png', $this->container->getParameter('dms.storage.path'), $dir, uniqid("image_{$width}x{$height}_"));
        $image = self::imageLink($width, $height);

        if (!is_dir(dirname($imageName))) {
            mkdir(dirname($imageName), 0777, true);
        }

        file_put_contents($imageName, file_get_contents($image));

        $imageName =  str_replace($this->container->getParameter('dms.storage.path'), '', $imageName);
        $imageName = trim($imageName, '/');

        return $imageName;
    }
}
