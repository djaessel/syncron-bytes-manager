<?php

namespace App\Entity\Star;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Star\EpisodeRepository")
 */
class Episode
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="number", type="integer", nullable=false)
     */
    private $number;

    /**
     * @ORM\Column(name="season", type="integer", nullable=false)
     */
    private $season;

    /**
     * @ORM\Column(name="title", type="string", length="255", nullable=true)
     */
    private $title;

    // - - - - - auto generate - - - - -


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getSeason(): int
    {
        return $this->season;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
