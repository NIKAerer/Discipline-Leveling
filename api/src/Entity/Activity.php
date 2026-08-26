<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    private ?int $expWon = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DisciplineTracking $disciplineTracking = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getExpWon(): ?int
    {
        return $this->expWon;
    }

    public function setExpWon(int $expWon): static
    {
        $this->expWon = $expWon;

        return $this;
    }

    public function getDisciplineTracking(): ?DisciplineTracking
    {
        return $this->disciplineTracking;
    }

    public function setDisciplineTracking(?DisciplineTracking $disciplineTracking): static
    {
        $this->disciplineTracking = $disciplineTracking;

        return $this;
    }
}
