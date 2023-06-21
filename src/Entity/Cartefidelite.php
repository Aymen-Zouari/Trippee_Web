<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

// Add the use statement for the Range constraint
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;  
use ApiPlatform\Core\Annotation\ApiResource;

#[ApiResource]
#[ORM\Table(name: "cartefidelite")]
#[ORM\Entity]
class Cartefidelite
{
    #[ORM\Column(name: "id_cf", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $idCf;

    #[ORM\Column(name: "pointMerci", type: "string", length: 255, nullable: false)]
    #[Assert\NotBlank(message: "Point merci cannot be empty.")]
    private string $pointmerci;

    #[ORM\Column(name: "dateExpiration", type: "date", nullable: false)]
    private \DateTimeInterface $dateexpiration;

    #[ORM\OneToOne(targetEntity: "Abonnement", inversedBy: "cartefidelite", cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(name: "idA", referencedColumnName: "idA")]
    private ?Abonnement $abonnement;

    public function getAbonnement(): ?Abonnement
    {
        return $this->abonnement;
    }

    public function setAbonnement(?Abonnement $abonnement): self
    {
        $this->abonnement = $abonnement;
        return $this;
    }

    public function getIdCf(): ?int
    {
        return $this->idCf;
    }

    public function getPointmerci(): ?string
    {
        return $this->pointmerci;
    }

    public function getDateexpiration(): ?\DateTime
    {
        return $this->dateexpiration;
    }

    
    public function setPointmerci(string $pointmerci): self
    {
        $this->pointmerci = $pointmerci;
        return $this;
    }

    public function setDateexpiration(\DateTime $dateexpiration): self
    {
        $this->dateexpiration = $dateexpiration;
        return $this;
    }

    
}
