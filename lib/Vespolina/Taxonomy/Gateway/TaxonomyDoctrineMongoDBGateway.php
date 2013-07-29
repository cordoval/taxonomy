<?php

namespace Vespolina\Taxonomy\Gateway;

use Vespolina\Entity\Taxonomy\TaxonomyNodeInterface;
use Vespolina\Exception\InvalidInterfaceException;
use Vespolina\Taxonomy\Gateway\TaxonomyGateway;
use Vespolina\Product\Specification\SpecificationInterface;

class TaxonomyDoctrineMongoDBGateway extends TaxonomyGateway
{
    protected $dm;
    protected $taxonomyNodeClass;

    public function __construct($documentManager, $taxonomyNodeClass)
    {
        parent::__construct($taxonomyNodeClass, 'DoctrineMongoDB');
        $this->dm = $documentManager;
    }

    public function createQuery()
    {
        return $this->dm->createQueryBuilder($this->taxonomyNodeClass);
    }

    /**
     * @param \Vespolina\Entity\Taxonomy\TaxonomyNodeInterface $taxonomyNode
     */
    public function deleteTaxonomyNode(TaxonomyNodeInterface $taxonomyNode, $andFlush = false)
    {
        $this->dm->remove($taxonomyNode);
    }

    protected function executeSpecification(SpecificationInterface $specification, $matchOne = false)
    {
        $queryBuilder = $this->createQuery();
        //$this->getSpecificationWalker()->walk($specification, $queryBuilder);
        $query = $queryBuilder->getQuery();

        if ($matchOne) {

            return $query->getSingleResult();
        } else {

            return $query->execute();
        }
    }

    public function matchTaxonomyNode($specification)
    {
        return $this->executeSpecification($specification, true);
    }

    public function matchAll($specification)
    {
        return $this->executeSpecification($specification);
    }

    /**
     * @return \Vespolina\Entity\Taxonomy\TaxonomyNodeInterface
     */
    public function findTaxonomyNodes(SelectQueryInterface $query)
    {
    }

    /**
     * @param \Vespolina\Entity\Taxonomy\TaxonomyNodeInterface $taxonomy
     */
    public function persistTaxonomyNode(TaxonomyNodeInterface $taxonomyNode, $andFlush = false)
    {
        $this->dm->persist($taxonomyNode);
        if ($andFlush) $this->flush();
    }

    /**
     * @param \Vespolina\Entity\Taxonomy\TaxonomyNodeInterface $taxonomy
     */
    public function updateTaxonomyNode(TaxonomyNodeInterface $taxonomyNode, $andFlush = false)
    {
        $level = $taxonomyNode->getLevel();

        if (null == $level) {
            $level = 0;

            if (null !== $parent = $taxonomyNode->getParent()) {
                $level = $parent->getLevel() + 1;
            }

            $rp = new \ReflectionProperty($taxonomyNode, 'level');
            $rp->setAccessible(true);
            $rp->setValue($taxonomyNode, $level);
            $rp->setAccessible(false);
        }

        $this->dm->persist($taxonomyNode);
        if ($andFlush) $this->flush();
    }

    public function flush()
    {
        $this->dm->flush();
    }
}
