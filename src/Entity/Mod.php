<?php

namespace App\Entity;

use App\Repository\ModRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ModRepository::class)]
#[ORM\Table(name: '`mod`')]
class Mod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Merci d\'entrer le nom du mod.')]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $sha1 = null;

    #[ORM\Column]
    private ?int $size = null;

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

    public function getSha1(): ?string
    {
        return $this->sha1;
    }

    public function setSha1(string $sha1): static
    {
        $this->sha1 = $sha1;

        return $this;
    }

    public function getDownloadURL(): ?string
    {
        return $this->downloadURL;
    }

    public function setDownloadURL(string $downloadURL): static
    {
        $this->downloadURL = $downloadURL;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }
}
