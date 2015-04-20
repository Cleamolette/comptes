<?php

namespace ComptesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use ComptesBundle\Entity\Mouvement;
use ComptesBundle\Entity\Compte;
use ComptesBundle\Entity\Categorie;

/**
 * MouvementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MouvementRepository extends EntityRepository
{
    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return $this->findBy(array(), array('date' => 'DESC'));
    }

    /**
     * Calcule le montant cumulé de tous les mouvements.
     *
     * @return float
     */
    public function getMontantTotal()
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder
            ->select('SUM(m.montant) AS total')
            ->from('ComptesBundle:Mouvement', 'm');

        $result = $queryBuilder->getQuery()->getSingleResult();

        $total = $result['total'] !== null ? $result['total'] : 0;

        return $total;
    }

    /**
     * Calcule le montant cumulé de tous les mouvements entre deux dates.
     *
     * @param \DateTime $dateStart Date de début, incluse.
     * @param \DateTime $dateEnd Date de fin, incluse.
     * @param string $order 'ASC' (par défaut) ou 'DESC'.
     * @return float
     */
    public function getMontantTotalByDate(\DateTime $dateStart, \DateTime $dateEnd, $order='ASC')
    {
        // Calcul du montant total des mouvements entre deux dates
        $queryBuilder = $this->_em->createQueryBuilder();
        $expressionBuilder = $this->_em->getExpressionBuilder();

        $and = $expressionBuilder->andX();
        $and->add($expressionBuilder->gte('m.date', ':date_start'));
        $and->add($expressionBuilder->lte('m.date', ':date_end'));

        $queryBuilder
            ->select('SUM(m.montant) AS total')
            ->from('ComptesBundle:Mouvement', 'm')
            ->where($and)
            ->setParameter('date_start', $dateStart)
            ->setParameter('date_end', $dateEnd)
            ->orderBy('m.date', $order);

        $result = $queryBuilder->getQuery()->getSingleResult();

        $total = $result['total'] !== null ? $result['total'] : 0;

        return $total;
    }

    /**
     * Récupère les mouvements d'un compte.
     *
     * @param Compte $compte
     * @param string $order 'ASC' (par défaut) ou 'DESC'.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByCompte(Compte $compte, $order='ASC')
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $compteID = $compte->getId();

        $queryBuilder
            ->select('m')
            ->from('ComptesBundle:Mouvement', 'm')
            ->where('m.compte = :compte_id')
            ->orderBy('m.date', $order)
            ->setParameter(':compte_id', $compteID);

        $mouvements = $queryBuilder->getQuery()->getResult();

        return $mouvements;
    }

    /**
     * Récupère les mouvements d'un compte,
     * depuis le début jusqu'à une date donnée (incluse).
     *
     * @todo Mutualiser avec self::findByCompteSinceDate()
     *
     * @param Compte $compte
     * @param \DateTime $date
     * @param string $order 'ASC' (par défaut) ou 'DESC'.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByCompteUntilDate(Compte $compte, \DateTime $date, $order='ASC')
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $expressionBuilder = $this->_em->getExpressionBuilder();

        $compteID = $compte->getId();

        $and = $expressionBuilder->andX();
        $and->add($expressionBuilder->eq('m.compte', ':compte_id'));
        $and->add($expressionBuilder->lte('m.date', ':date'));

        $queryBuilder
            ->select('m')
            ->from('ComptesBundle:Mouvement', 'm')
            ->where($and)
            ->orderBy('m.date', $order)
            ->setParameter(':compte_id', $compteID)
            ->setParameter(':date', $date);

        $mouvements = $queryBuilder->getQuery()->getResult();

        return $mouvements;
    }

    /**
     * Récupère les mouvements d'un compte,
     * depuis une date donnée jusqu'à aujourd'hui (inclus).
     *
     * @todo Mutualiser avec self::findByCompteUntilDate()
     *
     * @param Compte $compte
     * @param \DateTime $date
     * @param string $order 'ASC' (par défaut) ou 'DESC'.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByCompteSinceDate(Compte $compte, \DateTime $date, $order='ASC')
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $expressionBuilder = $this->_em->getExpressionBuilder();

        $compteID = $compte->getId();

        $and = $expressionBuilder->andX();
        $and->add($expressionBuilder->eq('m.compte', ':compte_id'));
        $and->add($expressionBuilder->gte('m.date', ':date'));

        $queryBuilder
            ->select('m')
            ->from('ComptesBundle:Mouvement', 'm')
            ->where($and)
            ->orderBy('m.date', $order)
            ->setParameter(':compte_id', $compteID)
            ->setParameter(':date', $date);

        $mouvements = $queryBuilder->getQuery()->getResult();

        return $mouvements;
    }

    /**
     * Récupère les mouvements d'un compte entre deux dates.
     *
     * @param Compte $compte Le compte bancaire.
     * @param \DateTime $dateStart Date de début, incluse.
     * @param \DateTime $dateEnd Date de fin, incluse.
     * @param string $order 'ASC' (par défaut) ou 'DESC'.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByCompteAndDate(Compte $compte, \DateTime $dateStart, \DateTime $dateEnd, $order='ASC')
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $expressionBuilder = $this->_em->getExpressionBuilder();

        $compteID = $compte->getId();

        $and = $expressionBuilder->andX();
        $and->add($queryBuilder->expr()->eq('m.compte', ':compte_id'));
        $and->add($expressionBuilder->gte('m.date', ':date_start'));
        $and->add($expressionBuilder->lte('m.date', ':date_end'));

        $queryBuilder
            ->select('m')
            ->from('ComptesBundle:Mouvement', 'm')
            ->where($and)
            ->setParameter('compte_id', $compteID)
            ->setParameter('date_start', $dateStart)
            ->setParameter('date_end', $dateEnd)
            ->orderBy('m.date', $order);

        $mouvements = $queryBuilder->getQuery()->getResult();

        return $mouvements;
    }

    /**
     * Récupère les mouvements,
     * depuis le début jusqu'à une date donnée (incluse).
     *
     * @todo Mutualiser avec self::findSinceDate()
     *
     * @param \DateTime $date
     * @param string $order 'ASC' (par défaut) ou 'DESC'.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findUntilDate(\DateTime $date, $order='ASC')
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder
            ->select('m')
            ->from('ComptesBundle:Mouvement', 'm')
            ->where('m.date <= :date')
            ->orderBy('m.date', $order)
            ->setParameter(':date', $date);

        $mouvements = $queryBuilder->getQuery()->getResult();

        return $mouvements;
    }

    /**
     * Récupère les mouvements,
     * depuis une date donnée jusqu'à aujourd'hui (inclus).
     *
     * @todo Mutualiser avec self::findUntilDate()
     *
     * @param \DateTime $date
     * @param string $order 'ASC' (par défaut) ou 'DESC'.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findSinceDate(\DateTime $date, $order='ASC')
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder
            ->select('m')
            ->from('ComptesBundle:Mouvement', 'm')
            ->where('m.date >= :date')
            ->orderBy('m.date', $order)
            ->setParameter(':date', $date);

        $mouvements = $queryBuilder->getQuery()->getResult();

        return $mouvements;
    }

    /**
     * Récupère les mouvements entre deux dates.
     *
     * @param \DateTime $dateStart Date de début, incluse.
     * @param \DateTime $dateEnd Date de fin, incluse.
     * @param string $order 'ASC' (par défaut) ou 'DESC'.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByDate(\DateTime $dateStart, \DateTime $dateEnd, $order='ASC')
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $expressionBuilder = $this->_em->getExpressionBuilder();

        $and = $expressionBuilder->andX();
        $and->add($expressionBuilder->gte('m.date', ':date_start'));
        $and->add($expressionBuilder->lte('m.date', ':date_end'));

        $queryBuilder
            ->select('m')
            ->from('ComptesBundle:Mouvement', 'm')
            ->where($and)
            ->setParameter('date_start', $dateStart)
            ->setParameter('date_end', $dateEnd)
            ->orderBy('m.date', $order);

        $mouvements = $queryBuilder->getQuery()->getResult();

        return $mouvements;
    }

    /**
     * Récupère les mouvements d'une catégorie, entre deux dates.
     *
     * @param Categorie $categorie La catégorie.
     * @param \DateTime $dateStart Date de début, incluse.
     * @param \DateTime $dateEnd Date de fin, incluse.
     * @param string $order 'ASC' (par défaut) ou 'DESC'.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByDateAndCategorie(Categorie $categorie, \DateTime $dateStart, \DateTime $dateEnd, $order='ASC')
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $expressionBuilder = $this->_em->getExpressionBuilder();

        // La liste des catégories de mouvements
        $categorieID = $categorie->getId();
        $categories = array($categorieID);
        $categoriesFilles = $categorie->getCategoriesFillesRecursive();

        foreach ($categoriesFilles as $categorieFille)
        {
            $categories[] = $categorieFille->getId();
        }

        $and = $expressionBuilder->andX();
        $and->add($expressionBuilder->in('m.categorie', ':categories'));
        $and->add($expressionBuilder->gte('m.date', ':date_start'));
        $and->add($expressionBuilder->lte('m.date', ':date_end'));

        $queryBuilder
            ->select('m')
            ->from('ComptesBundle:Mouvement', 'm')
            ->where($and)
            ->setParameter('date_start', $dateStart)
            ->setParameter('date_end', $dateEnd)
            ->setParameter('categories', $categories)
            ->orderBy('m.date', $order);

        $mouvements = $queryBuilder->getQuery()->getResult();

        return $mouvements;
    }

    /**
     * Récupère le mouvement le plus récent.
     *
     * @return Mouvement
     */
    public function findLatestOne()
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder
            ->select('m')
            ->from('ComptesBundle:Mouvement', 'm')
            ->orderBy('m.date', 'DESC')
            ->setMaxResults(1);

        try
        {
            $mouvement = $queryBuilder->getQuery()->getSingleResult();
        }
        catch (\Doctrine\ORM\NoResultException $exception)
        {
            $mouvement = null;
        }

        return $mouvement;
    }

    /**
     * Récupère les mouvements d'une catégorie.
     *
     * @param Categorie $categorie La catégorie.
     * @param string $order 'ASC' (par défaut) ou 'DESC'.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByCategorie(Categorie $categorie, $order='ASC')
    {
        // Récupération des mouvements de la catégorie
        $queryBuilder = $this->_em->createQueryBuilder();

        // La liste des catégories de mouvements
        $categorieID = $categorie->getId();
        $categories = array($categorieID);
        $categoriesFilles = $categorie->getCategoriesFillesRecursive();

        foreach ($categoriesFilles as $categorieFille)
        {
            $categories[] = $categorieFille->getId();
        }

        $queryBuilder
            ->select('m')
            ->from('ComptesBundle:Mouvement', 'm')
            ->where('m.categorie in (:categories)')
            ->orderBy('m.date', $order)
            ->setParameter('categories', $categories);

        $mouvements = $queryBuilder->getQuery()->getResult();

        return $mouvements;
    }

    /**
     * Récupère les mouvements du montant donné entre deux dates.
     *
     * @param float $montant
     * @param \DateTime $dateStart
     * @param \DateTime $dateEnd
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByMontantBetweenDates($montant, $dateStart, $dateEnd)
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $expressionBuilder = $this->_em->getExpressionBuilder();

        $and = $expressionBuilder->andX();
        $and->add($expressionBuilder->eq('m.montant', ':montant'));
        $and->add($expressionBuilder->gte('m.date', ':date_start'));
        $and->add($expressionBuilder->lte('m.date', ':date_end'));

        $queryBuilder
            ->select('m')
            ->from('ComptesBundle:Mouvement', 'm')
            ->where($and)
            ->setParameter(':montant', $montant)
            ->setParameter(':date_start', $dateStart)
            ->setParameter(':date_end', $dateEnd);

        $mouvements = $queryBuilder->getQuery()->getResult();

        return $mouvements;
    }
}
