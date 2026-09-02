<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 5)]
    private ?string $rank = null;

    #[ORM\Column]
    private ?int $expTotal = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $avatar = null;

    /**
     * @var Collection<int, DisciplineTracking>
     */
    #[ORM\OneToMany(targetEntity: DisciplineTracking::class, mappedBy: 'user')]
    private Collection $disciplineTrackings;

    public function __construct()
    {
        $this->disciplineTrackings = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }   

    public function eraseCredentials(): void
    {
        // rien à effacer ici pour l'instant — cette méthode existe pour le cas où tu stockerais
        // temporairement une donnée sensible non hashée sur l'objet User pendant une requête,
        // et voudrais forcer Symfony à la nettoyer juste après l'authentification.
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

    public function getExpTotal(): ?int
    {
        return $this->expTotal;
    }

    public function setExpTotal(int $expTotal): static
    {
        $this->expTotal = $expTotal;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return Collection<int, DisciplineTracking>
     */
    public function getDisciplineTrackings(): Collection
    {
        return $this->disciplineTrackings;
    }

    public function addDisciplineTracking(DisciplineTracking $disciplineTracking): static
    {
        if (!$this->disciplineTrackings->contains($disciplineTracking)) {
            $this->disciplineTrackings->add($disciplineTracking);
            $disciplineTracking->setUser($this);
        }

        return $this;
    }

    public function removeDisciplineTracking(DisciplineTracking $disciplineTracking): static
    {
        if ($this->disciplineTrackings->removeElement($disciplineTracking)) {
            if ($disciplineTracking->getUser() === $this) {
                $disciplineTracking->setUser(null);
            }
        }

        return $this;
    }
}
