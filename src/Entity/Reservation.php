<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Client;


#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("reservations")]
    private ?int $id = null;

    #[ORM\Column(type: "datetime")]
    #[Groups("reservations")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: "datetime")]
    #[Groups("reservations")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\ManyToOne(inversedBy:'reservations')]
    #[ORM\JoinColumn(name: "id_client", referencedColumnName: "id_client")]
    private ?Client $id_client = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Voiture $idVoiture = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getIdClient(): ?Client
    {
        return $this->id_client;
    }

    public function setIdClient(?Client $id_client): self
    {
        $this->id_client = $id_client;

        return $this;
    }

    public function getIdVoiture(): ?Voiture
    {
        return $this->idVoiture;
    }

    public function setIdVoiture(?Voiture $idVoiture): self
    {
        $this->idVoiture = $idVoiture;

        return $this;
    }


}
