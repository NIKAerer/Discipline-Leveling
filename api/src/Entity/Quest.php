<?php

namespace App\Entity;

use App\Repository\QuestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestRepository::class)]
class Quest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column]
    private ?int $expValue = null;

    #[ORM\ManyToOne(inversedBy: 'quests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DisciplineTracking $disciplineTracking = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getExpValue(): ?int
    {
        return $this->expValue;
    }

    public function setExpValue(int $expValue): static
    {
        $this->expValue = $expValue;

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
