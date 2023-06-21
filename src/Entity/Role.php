<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RoleRepository;
use App\Entity\Utilisateur;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;



#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
   
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(type: "integer")]
    #[Groups("roles")]
    private ?int $id_role = null;
   

   
    #[ORM\Column(length:255)]
    #[Assert\NotBlank(message: "You must complete all empty fields")]
    #[Groups("roles")]
    private ?string $libelle=null;

  
    #[ORM\ManyToOne(inversedBy:'roles')]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user")]
    private ?Utilisateur $id_user = null;


    #[ORM\OneToMany(targetEntity: 'App\Entity\Client', mappedBy: 'id_role')]
    private $clients;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Chauffeur', mappedBy: 'id_role')]
    private $chauffeurs;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Locateur', mappedBy: 'id_role')]
    private $locateurs;

  
  

    public function getIdRole(): ?int
    {
        return $this->id_role;
    }

    public function setIdRole(?int $id_role): self
    {
        $this->id_role = $id_role;

        return $this;
    }


    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getIdUser(): ?Utilisateur
    {
        return $this->id_user;
    }

    public function setIdUser(?Utilisateur $id_user): self
    {
        $this->id_user = $id_user;

        return $this;
    }

   


}
