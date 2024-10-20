<?php

namespace App\Entity;

use App\Repository\NewsRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?NewsText $title = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?NewsText $description = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?NewsLayout $layout = null;

    #[ORM\Column]
    private ?int $epoch_millis = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?NewsText
    {
        return $this->title;
    }

    public function setTitle(NewsText $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?NewsText
    {
        return $this->description;
    }

    public function setDescription(NewsText $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLayout(): ?NewsLayout
    {
        return $this->layout;
    }

    public function setLayout(NewsLayout $layout): static
    {
        $this->layout = $layout;

        return $this;
    }

    public function getEpochMillis(): ?int
    {
        return $this->epoch_millis;
    }

    public function setEpochMillis(int $epoch_millis): static
    {
        $this->epoch_millis = $epoch_millis;

        return $this;
    }

    public function getDate(): string
    {
        return (new DateTime())->setTimestamp($this->epoch_millis)->format("d/m/Y H:i");
    }
}
