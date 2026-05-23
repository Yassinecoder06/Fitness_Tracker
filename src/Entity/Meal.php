<?php

namespace App\Entity;

use App\Repository\MealRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MealRepository::class)]
#[ORM\Table(name: 'meals')]
class Meal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    private ?string $foodName = null;

    #[ORM\Column(length: 50)]
    private ?string $mealType = null;

    #[ORM\Column]
    private ?int $calories = null;

    #[ORM\Column]
    private ?int $protein = null;

    #[ORM\Column]
    private ?int $carbs = null;

    #[ORM\Column]
    private ?int $fat = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getFoodName(): ?string { return $this->foodName; }
    public function setFoodName(string $foodName): static { $this->foodName = $foodName; return $this; }

    public function getMealType(): ?string { return $this->mealType; }
    public function setMealType(string $mealType): static { $this->mealType = $mealType; return $this; }

    public function getCalories(): ?int { return $this->calories; }
    public function setCalories(int $calories): static { $this->calories = $calories; return $this; }

    public function getProtein(): ?int { return $this->protein; }
    public function setProtein(int $protein): static { $this->protein = $protein; return $this; }

    public function getCarbs(): ?int { return $this->carbs; }
    public function setCarbs(int $carbs): static { $this->carbs = $carbs; return $this; }

    public function getFat(): ?int { return $this->fat; }
    public function setFat(int $fat): static { $this->fat = $fat; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): static { $this->date = $date; return $this; }
}
