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
     * @ORM\Column(name="number", type="string", length=8, nullable=false)
     */
    private $number;

    /**
     * @ORM\Column(name="number_all", type="string", length=8, nullable=true)
     */
    private $numberAll;

    /**
     * @ORM\Column(name="season", type="integer", nullable=false)
     */
    private $season;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @ORM\Column(name="path", type="string", length=64, nullable=false)
     */
    private $path;

    /**
     * @ORM\Column(name="is_extra", type="boolean", nullable=true)
     */
    private $isExtra;

    // TODO: add constructor for easier import later
    // TODO: Path;Name_German;Name_English;Season;Episode;EpisodeAll;IsExtra
    // TODO: multi lingual entity later

    // - - - - - auto generate - - - - -


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function getNumberAll(): ?string
    {
        return $this->numberAll;
    }

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getIsExtra(): ?bool
    {
        return $this->isExtra;
    }

    public function setNumber(string $number)
    {
        $this->number = $number;
    }

    public function setNumberAll(?string $numberAll)
    {
        $this->numberAll = $numberAll;
    }

    public function setSeason(int $season)
    {
        $this->season = $season;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function setIsExtra(?bool $isExtra)
    {
        $this->isExtra = $isExtra;
    }
}
