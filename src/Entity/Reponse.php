<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Reponse cannot be empty')]
    #[Assert\Length(min: 16, minMessage: 'Reponse must be at least {{ limit }} characters long')]
    private ?string $reponse = null;

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reclamation $id_rec = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(?string $reponse): self
    {
        $this->reponse = $reponse;

        return $this;
    }

    public function getIdRec(): ?Reclamation
    {
        return $this->id_rec;
    }

    public function setIdRec(?Reclamation $id_rec): self
    {
        $this->id_rec = $id_rec;

        return $this;
    }
}
