<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
#[Index(columns: ["hash"], name: "hash")]
class Link
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true)]
    #[Assert\Length(max: 7)]
    private ?string $hash = null;

    #[ORM\Column(type: 'text')]
    #[Assert\Length(max: 2048)]
    private ?string $longUrl = null;

    #[ORM\Column(type: 'integer')]
    private int $clickCount = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createDate;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $dueDate;

    public function __construct()
    {
        $this->createDate = new \DateTime();
        $this->dueDate    = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): void
    {
        $this->hash = $hash;
    }

    public function getLongUrl(): ?string
    {
        return $this->longUrl;
    }

    public function setLongUrl(?string $longUrl): void
    {
        $this->longUrl = $longUrl;
    }

    public function getClickCount(): int
    {
        return $this->clickCount;
    }

    public function setClickCount(int $clickCount): void
    {
        $this->clickCount = $clickCount;
    }

    public function increaseClickCount(): void
    {
        $this->clickCount++;
    }

    public function getCreateDate(): \DateTime
    {
        return $this->createDate;
    }

    public function setCreateDate(\DateTime $createDate): void
    {
        $this->createDate = $createDate;
    }

    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTime $dueDate): void
    {
        $this->dueDate = $dueDate;
    }
}
