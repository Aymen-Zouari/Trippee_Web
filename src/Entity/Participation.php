<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Client;


#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    /**
     * @Assert\NotNull(message="The number of seats field is required.")
     */
    private ?int $nmbr_place_part = null;

    #[ORM\ManyToOne(inversedBy:'participations')]
    #[ORM\JoinColumn(name: "id_client", referencedColumnName: "id_client")]
    private ?Client $id_client = null;


    #[ORM\ManyToOne(inversedBy: 'participations')]
    private ?CoVoiturage $id_co = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNmbrPlacePart(): ?int
    {
        return $this->nmbr_place_part;
    }

    public function setNmbrPlacePart(int $nmbr_place_part): self
    {
        $this->nmbr_place_part = $nmbr_place_part;

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


    public function getIdCo(): ?CoVoiturage
    {
        return $this->id_co;
    }

    public function setIdCo(?CoVoiturage $id_co): self
    {
        $this->id_co = $id_co;

        return $this;
    }

      public function __toString(): string
{
    return $this->getIdClient();
}


    
}
