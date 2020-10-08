<?php

namespace App\Entity\Star;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Star\SeasonRepository")
 */
class Season
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
     * @ORM\Column(name="series", type="integer", nullable=false)
     */
    private $series;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @ORM\Column(name="start_year", type="integer", nullable=true)
     */
    private $startYear;

    /**
     * @ORM\Column(name="end_year", type="integer", nullable=true)
     */
    private $endYear;

    // TODO: add constructor for easier import later
    // TODO: calculate first and last episode
    // TODO: multi lingual entity later

    // - - - - - auto generate - - - - -


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function getSeries(): ?int
    {
        return $this->series;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getStartYear(): ?int
    {
        return $this->startYear;
    }

    public function getEndYear(): ?int
    {
        return $this->endYear;
    }

    public function setNumber(int $number)
    {
        $this->number = $number;
    }

    public function setSeries(int $series)
    {
        $this->series = $series;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function setStartYear(?int $startYear)
    {
        $this->startYear = $startYear;
    }

    public function setEndYear(?int $endYear)
    {
        $this->endYear = $endYear;
    }
}
