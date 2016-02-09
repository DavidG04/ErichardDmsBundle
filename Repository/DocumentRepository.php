<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class DocumentRepository
 *
 * @package Erichard\DmsBundle\Repository
 */
class DocumentRepository extends EntityRepository
{
    /**
     * TokenStorage
     *
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * setSecurityContext
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function setSecurityTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * getSecurityContext
     *
     * @return TokenStorageInterface
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     * findOneBySlugAndNode
     *
     * @param string $documentSlug
     * @param string $nodeSlug
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySlugAndNode($documentSlug, $nodeSlug)
    {
        return $this
            ->createQueryBuilder('d')
            ->addSelect('d', 'm', 'p')
            ->innerJoin('d.node', 'n')
            ->leftJoin('d.parent', 'p')
            ->leftJoin('d.metadatas', 'm')
            ->where('d.slug = :document AND n.slug = :node')
            ->setParameter('document', $documentSlug)
            ->setParameter('node', $nodeSlug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * findByMetadatas
     *
     * @param null  $node
     * @param array $metadatas
     * @param array $sortBy
     * @param int   $limit
     *
     * @return array
     */
    public function findByMetadatas($node = null, array $metadatas = array(), array $sortBy = array(), $limit = 10)
    {
        $qbd = $this
            ->createQueryBuilder('d')
            ->innerJoin('d.metadatas', 'dm')
            ->innerJoin('dm.metadata', 'm')
        ;

        if (null !== $node) {
            $descendants = $this
                ->getEntityManager()
                ->createQuery(/** @lang text */ "SELECT n.id FROM Erichard\\DmsBundle\\Entity\\DocumentNodeClosure c INNER JOIN c.descendant n WHERE c.ancestor = :ancestor")
                ->setParameter('ancestor', $node)
                ->getScalarResult()
            ;

            $descendants = array_map(
                function ($row) {
                    return $row['id'];
                },
                $descendants
            );

            $qbd
                ->andWhere('d.node IN (:parents)')
                ->setParameter('parents', $descendants)
            ;
        }

        $idx = 0;
        foreach ($metadatas as $metaName => $metaValue) {
            $qbd
                ->andWhere("m.name = :meta_$idx AND dm.value = :value_$idx")
                ->setParameter('meta_'.$idx, $metaName)
                ->setParameter('value_'.$idx, $metaValue)
            ;
            $idx++;
        }

        foreach ($sortBy as $key => $value) {
            $qbd->addOrderBy($qbd->getRootAliases().'.'.$key, $value);
        }

        $qbd->setMaxResults($limit);

        return $qbd->getQuery()->getResult();
    }
}
