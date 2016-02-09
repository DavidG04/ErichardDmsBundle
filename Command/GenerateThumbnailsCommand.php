<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateThumbnailsCommand
 *
 * @package Erichard\DmsBundle\Command
 */
class GenerateThumbnailsCommand extends ContainerAwareCommand
{
    /**
     * configure
     */
    public function configure()
    {
        $this
            ->setName('dms:thumbnails:generate')
            ->setDescription('Generate thumbnails for all documents.')
            ->addArgument('sizes', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'List of thumbnail to generated.')
        ;
    }

    /**
     * execute
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sizes = $input->getArgument('sizes');

        $iterator = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->createQuery(/** @lang text */'SELECT d FROM Erichard\DmsBundle\Entity\Document d')->iterate();

        $dmsManager = $this->getContainer()->get('dms.manager');

        foreach ($iterator as $row) {
            $document = $row[0];

            foreach ($sizes as $size) {
                $thumbnail = $dmsManager->generateThumbnail($document, $size);
                if (!empty($thumbnail)) {
                    $output->writeLn('<info> > </info>'.$thumbnail);
                } else {
                    $output->writeLn('<comment> > </comment>No thumbnail generated for '.$document->getSlug());
                }
            }
        }

    }
}
