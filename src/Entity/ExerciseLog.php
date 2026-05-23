<?php

namespace App\Entity;

use App\Repository\ExerciseLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExerciseLogRepository::class)]
#[ORM\Table(name: 'exercise_logs')]
class ExerciseLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    private ?string $exerciseName = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column]
    private ?int $caloriesBurned = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getExerciseName(): ?string { return $this->exerciseName; }
    public function setExerciseName(string $exerciseName): static { $this->exerciseName = $exerciseName; return $this; }

    public function getDuration(): ?int { return $this->duration; }
    public function setDuration(int $duration): static { $this->duration = $duration; return $this; }

    public function getCaloriesBurned(): ?int { return $this->caloriesBurned; }
    public function setCaloriesBurned(int $caloriesBurned): static { $this->caloriesBurned = $caloriesBurned; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): static { $this->date = $date; return $this; }
}
