<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 *
 * @method Stock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stock[]    findAll()
 * @method Stock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    public function save(Stock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Stock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    

    public function findLatestStockForEachProduct()
    {
        $qb = $this->createQueryBuilder('s');

        $subquery = $this->_em->createQueryBuilder()
            ->select('MAX(s2.updatedAt)')
            ->from('App\Entity\Stock', 's2')
            ->where('s2.product = s.product')
            ->getQuery()
            ->getDQL();
    
        $qb->andWhere($qb->expr()->in('s.updatedAt', $subquery));
    
        return $qb->getQuery()->getResult();
    }

    public function findLatestStockForOneProduct(Product $product){
        return $this->createQueryBuilder('s')
            ->where('s.product = :product')
            ->setParameter('product', $product)
            ->orderBy('s.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return Stock[] Returns an array of Stock objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Stock
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
