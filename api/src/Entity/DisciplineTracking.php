<?php

namespace App\Entity;

use App\Repository\DisciplineTrackingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DisciplineTrackingRepository::class)]
class DisciplineTracking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $goal = null;

    #[ORM\Column]
    private ?int $exp = null;

    #[ORM\Column(nullable: true)]
    private ?int $lpGoal = null;

    #[ORM\Column(nullable: true)]
    private ?int $lpStarting = null;

    #[ORM\Column(length: 5)]
    private ?string $rank = null;

    #[ORM\ManyToOne(inversedBy: 'disciplineTrackings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Discipline $discipline = null;

    /**
     * @var Collection<int, Quest>
     */
    #[ORM\OneToMany(targetEntity: Quest::class, mappedBy: 'disciplineTracking', orphanRemoval: true)]
    private Collection $quests;

    /**
     * @var Collection<int, LolMatch>
     */
    #[ORM\OneToMany(targetEntity: LolMatch::class, mappedBy: 'disciplineTracking', orphanRemoval: true)]
    private Collection $lolMatches;

    public function __construct()
    {
        $this->quests = new ArrayCollection();
        $this->lolMatches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoal(): ?string
    {
        return $this->goal;
    }

    public function setGoal(?string $goal): static
    {
        $this->goal = $goal;

        return $this;
    }

    public function getExp(): ?int
    {
        return $this->exp;
    }

    public function setExp(int $exp): static
    {
        $this->exp = $exp;

        return $this;
    }

    public function getLpGoal(): ?int
    {
        return $this->lpGoal;
    }

    public function setLpGoal(?int $lpGoal): static
    {
        $this->lpGoal = $lpGoal;

        return $this;
    }

    public function getLpStarting(): ?int
    {
        return $this->lpStarting;
    }

    public function setLpStarting(?int $lpStarting): static
    {
        $this->lpStarting = $lpStarting;

        return $this;
    }

    public function getRank(): ?string
    {
        return $this->rank;
    }

    public function setRank(string $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    /**
     * @return Collection<int, Quest>
     */
    public function getQuests(): Collection
    {
        return $this->quests;
    }

    public function addQuest(Quest $quest): static
    {
        if (!$this->quests->contains($quest)) {
            $this->quests->add($quest);
            $quest->setDisciplineTracking($this);
        }

        return $this;
    }

    public function removeQuest(Quest $quest): static
    {
        if ($this->quests->removeElement($quest)) {
            if ($quest->getDisciplineTracking() === $this) {
                $quest->setDisciplineTracking(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LolMatch>
     */
    public function getLolMatches(): Collection
    {
        return $this->lolMatches;
    }

    public function addLolMatch(LolMatch $lolMatch): static
    {
        if (!$this->lolMatches->contains($lolMatch)) {
            $this->lolMatches->add($lolMatch);
            $lolMatch->setDisciplineTracking($this);
        }

        return $this;
    }

    public function removeLolMatch(LolMatch $lolMatch): static
    {
        if ($this->lolMatches->removeElement($lolMatch)) {
            if ($lolMatch->getDisciplineTracking() === $this) {
                $lolMatch->setDisciplineTracking(null);
            }
        }

        return $this;
    }

}
