<?php

namespace App\Entity;

use App\Repository\NewsLayoutRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsLayoutRepository::class)]
class NewsLayout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $text_percentage = null;

    #[ORM\Column]
    private ?int $text_alignment = null;

    #[ORM\Column]
    private ?int $min_height = null;

    #[ORM\Column]
    private ?int $max_width = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTextPercentage(): ?int
    {
        return $this->text_percentage;
    }

    public function setTextPercentage(int $text_percentage): static
    {
        $this->text_percentage = $text_percentage;

        return $this;
    }

    public function getTextAlignment(): ?int
    {
        return $this->text_alignment;
    }

    public function setTextAlignment(int $text_alignment): static
    {
        $this->text_alignment = $text_alignment;

        return $this;
    }

    public function getMinHeight(): ?int
    {
        return $this->min_height;
    }

    public function setMinHeight(int $min_height): static
    {
        $this->min_height = $min_height;

        return $this;
    }

    public function getMaxWidth(): ?int
    {
        return $this->max_width;
    }

    public function setMaxWidth(int $max_width): static
    {
        $this->max_width = $max_width;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }
}
