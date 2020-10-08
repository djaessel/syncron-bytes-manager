<?php

namespace App\Entity\Star;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Star\SeriesRepository")
 */
class Series
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
     * @ORM\Column(name="color", type="string", length=16, nullable=true)
     */
    private $color;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    // TODO: add constructor for easier import later
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setNumber(int $number)
    {
        $this->number = $number;
    }

    public function setColor(string $color)
    {
        $this->color = $color;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }
}
