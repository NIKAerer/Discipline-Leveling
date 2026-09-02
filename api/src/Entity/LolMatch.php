<?php

namespace App\Entity;

use App\Repository\LolMatchRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LolMatchRepository::class)]
class LolMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $playedAt = null;

    #[ORM\Column(length: 100)]
    private ?string $champion = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $matchup = null;

    #[ORM\Column]
    private ?int $kills = null;

    #[ORM\Column]
    private ?int $deaths = null;

    #[ORM\Column]
    private ?int $assists = null;

    #[ORM\Column(nullable: true)]
    private ?int $gameDurationMinutes = null;

    #[ORM\Column(nullable: true)]
    private ?int $cs = null;

    #[ORM\Column]
    private ?bool $win = null;

    #[ORM\Column]
    private ?int $lpChange = null;

    #[ORM\ManyToOne(inversedBy: 'lolMatches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DisciplineTracking $disciplineTracking = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayedAt(): ?\DateTimeImmutable
    {
        return $this->playedAt;
    }

    public function setPlayedAt(\DateTimeImmutable $playedAt): static
    {
        $this->playedAt = $playedAt;

        return $this;
    }

    public function getChampion(): ?string
    {
        return $this->champion;
    }

    public function setChampion(string $champion): static
    {
        $this->champion = $champion;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getMatchup(): ?string
    {
        return $this->matchup;
    }

    public function setMatchup(?string $matchup): static
    {
        $this->matchup = $matchup;

        return $this;
    }

    public function getKills(): ?int
    {
        return $this->kills;
    }

    public function setKills(int $kills): static
    {
        $this->kills = $kills;

        return $this;
    }

    public function getDeaths(): ?int
    {
        return $this->deaths;
    }

    public function setDeaths(int $deaths): static
    {
        $this->deaths = $deaths;

        return $this;
    }

    public function getAssists(): ?int
    {
        return $this->assists;
    }

    public function setAssists(int $assists): static
    {
        $this->assists = $assists;

        return $this;
    }

    public function getGameDurationMinutes(): ?int
    {
        return $this->gameDurationMinutes;
    }

    public function setGameDurationMinutes(?int $gameDurationMinutes): static
    {
        $this->gameDurationMinutes = $gameDurationMinutes;

        return $this;
    }

    public function getCs(): ?int
    {
        return $this->cs;
    }

    public function setCs(?int $cs): static
    {
        $this->cs = $cs;

        return $this;
    }

    public function isWin(): ?bool
    {
        return $this->win;
    }

    public function setWin(bool $win): static
    {
        $this->win = $win;

        return $this;
    }

    public function getLpChange(): ?int
    {
        return $this->lpChange;
    }

    public function setLpChange(int $lpChange): static
    {
        $this->lpChange = $lpChange;

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
