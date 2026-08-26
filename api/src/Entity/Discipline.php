<?php

namespace App\Entity;

use App\Repository\DisciplineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DisciplineRepository::class)]
class Discipline
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $icon = null;

    /**
     * @var Collection<int, QuestTemplate>
     */
    #[ORM\OneToMany(targetEntity: QuestTemplate::class, mappedBy: 'discipline', orphanRemoval: true)]
    private Collection $questTemplates;

    public function __construct()
    {
        $this->questTemplates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection<int, QuestTemplate>
     */
    public function getQuestTemplates(): Collection
    {
        return $this->questTemplates;
    }

    public function addQuestTemplate(QuestTemplate $questTemplate): static
    {
        if (!$this->questTemplates->contains($questTemplate)) {
            $this->questTemplates->add($questTemplate);
            $questTemplate->setDiscipline($this);
        }

        return $this;
    }

    public function removeQuestTemplate(QuestTemplate $questTemplate): static
    {
        if ($this->questTemplates->removeElement($questTemplate)) {
            if ($questTemplate->getDiscipline() === $this) {
                $questTemplate->setDiscipline(null);
            }
        }

        return $this;
    }
}
