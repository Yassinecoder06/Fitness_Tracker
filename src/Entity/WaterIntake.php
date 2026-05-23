<?php

namespace App\Entity;

use App\Repository\WaterIntakeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WaterIntakeRepository::class)]
#[ORM\Table(name: 'water_intake')]
#[ORM\UniqueConstraint(name: 'user_date_unique', columns: ['user_id', 'date'])]
class WaterIntake
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $glasses = 0;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): static { $this->date = $date; return $this; }

    public function getGlasses(): int { return $this->glasses; }
    public function setGlasses(int $glasses): static { $this->glasses = $glasses; return $this; }
}
