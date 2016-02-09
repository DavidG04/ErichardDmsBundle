<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Repository;

use Doctrine\ORM\Query;
use Erichard\DmsBundle\Entity\DocumentNode;
use Gedmo\Tree\Entity\Repository\ClosureTreeRepository;

/**
 * Class DocumentNodeRepository
 *
 * @package Erichard\DmsBundle\Repository
 */
class DocumentNodeRepository extends ClosureTreeRepository
{
    /**
     * findSortField
     *
     * @param string $slug
     *
     * @return null|string
     */
    public function findSortField($slug)
    {
        $ret = $this
            ->createQueryBuilder('n')
            ->select('partial n.{id}, partial m.{id, value}')
            ->innerJoin('n.metadatas', 'm')
            ->innerJoin('m.metadata', 'meta', 'WITH', 'meta.name = :meta')
            ->where('n.slug = :node')
            ->setParameter('node', $slug)
            ->setParameter('meta', 'sortBy')
            ->getQuery()
            ->getArrayResult()
        ;

        return count($ret) > 0 ? current($ret)['metadatas'][0]['value'] : null;
    }

    /**
     * findOneBySlugWithChildren
     *
     * @param string $slug
     * @param string $sortByField
     * @param string $sortByOrder
     *
     * @return DocumentNode
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySlugWithChildren($slug, $sortByField = 'name', $sortByOrder = 'ASC')
    {
        return $this
            ->createQueryBuilder('n')
            ->addSelect('nodes', 'd', 'p', 'm')
            ->leftJoin('n.nodes', 'nodes', 'nodes.id')
            ->leftJoin('n.documents', 'd', 'd.id')
            ->leftJoin('n.parent', 'p', 'd.id')
            ->leftJoin('n.metadatas', 'm', 'm.metadata.name')
            ->where('n.slug = :node')
            ->orderBy('nodes.'.$sortByField, $sortByOrder)
            ->addOrderBy('d.'.$sortByField, $sortByOrder)
            ->setParameter('node', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * findOneByIdWithChildren
     *
     * @param integer $idx
     *
     * @return DocumentNode
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByIdWithChildren($idx)
    {
        return $this
            ->createQueryBuilder('n')
            ->addSelect('nodes', 'd', 'p', 'm')
            ->leftJoin('n.nodes', 'nodes', 'nodes.id')
            ->leftJoin('n.documents', 'd', 'd.id')
            ->leftJoin('n.parent', 'p', 'd.id')
            ->leftJoin('n.metadatas', 'm', 'm.metadata.name')
            ->where('n.id = :node')
            ->setParameter('node', $idx)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


    /**
     * findOneByIdUniqId
     *
     * @param string $uniqRef
     *
     * @return DocumentNode
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByIdUniqRef($uniqRef)
    {
        return $this
            ->createQueryBuilder('n')
            ->where('n.uniqRef = :uniqRef')
            ->setParameter('uniqRef', $uniqRef)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


    /**
     * getRoots
     *
     * @return DocumentNode[]
     */
    public function getRoots()
    {
        return $this
            ->createQueryBuilder('n')
            ->where('n.parent IS NULL')
            ->orderBy('n.name')
            ->getQuery()
            ->getResult()
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
     * @return DocumentNode[]
     */
    public function findByMetadatas($node = null, array $metadatas = array(), array $sortBy = array(), $limit = 10)
    {
        $qbd = $this
            ->createQueryBuilder('n')
            ->innerJoin('n.metadatas', 'dm')
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
                ->andWhere('n.parent IN (:parents)')
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

        return $qbd
            ->getQuery()
            ->getResult()
        ;
    }
}
