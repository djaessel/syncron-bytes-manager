<?php

namespace App\Repository\Star;

use App\Entity\Star\Episode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Episode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Episode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Episode[]    findAll()
 * @method Episode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpisodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    /**
     * @return array
     */
    public function retrieveActualEpisodes()
    {
      $qb = $this->createQueryBuilder('e')
          ->where('e.isExtra = 0')
          ->orWhere('e.isExtra IS NULL')
      ;

      $query = $qb->getQuery();
      return $query->execute();
    }

    /**
     * @param int $videoId
     * @return int
     */
    public function findPreviousId($videoId)
    {
      $previousId = 0; // null
      $episodes = $this->retrieveActualEpisodes();

      if ($videoId > 1) {
          $curIndex = $this->indexOf($videoId, $episodes);
          $preIndex = $curIndex - 1;

          $previousId = $episodes[$preIndex]->getId();
      }

      return $previousId;
    }

    /**
     * @param int $videoId
     * @return int
     */
    public function findNextId($videoId)
    {
      $nextId = 0; // null
      $episodes = $this->retrieveActualEpisodes();
      $episodeCount = count($episodes);

      $lastEpisode = $episodes[$episodeCount - 1];
      if ($videoId < $lastEpisode->getId()) {
        $curIndex = $this->indexOf($videoId, $episodes);
        $nextIndex = $curIndex + 1;

        $nextId = $episodes[$nextIndex]->getId();
      }

      return $nextId;
    }

    /**
    * @param int $episodeId
    * @param array $episodes
    * @return int
    */
    private function indexOf($episodeId, $episodes)
    {
      $index = -1;
      foreach ($episodes as $key => $value) {
        if ($value->getId() == $episodeId) {
          $index = $key;
        }
      }
      return $index;
    }
}
