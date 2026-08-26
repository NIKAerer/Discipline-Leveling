<?php

namespace App\Entity;

use App\Repository\QuestTemplateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestTemplateRepository::class)]
class QuestTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $label = null;

    #[ORM\Column]
    private ?int $expValue = null;

    #[ORM\ManyToOne(inversedBy: 'questTemplates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Discipline $discipline = null;

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

    public function getDiscipline(): ?Discipline
    {
        return $this->discipline;
    }

    public function setDiscipline(?Discipline $discipline): static
    {
        $this->discipline = $discipline;

        return $this;
    }
}
