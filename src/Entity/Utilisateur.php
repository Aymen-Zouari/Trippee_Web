<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;



#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(type: "integer")]
    #[Groups("utilisateurs")]
    private ?int $id_user = null;
   

  
    #[ORM\Column(length:8)]
    #[Assert\NotBlank(message: "You must complete all empty fields")]
    #[Assert\Regex(pattern: "/^\d{8}$/", message: "Cin must be 8 numbers")]
    #[Groups("utilisateurs")]
    private ?string $cin=null;



    #[ORM\Column(length:100)]
    #[Assert\NotBlank(message: "You must complete all empty fields")]
    #[Assert\Regex(pattern: "/^[A-Z][a-zA-Z]*$/", message: "The firstname must start with a capital letter")]
    #[Groups("utilisateurs")]
    private ?string $nom=null;

    
    #[ORM\Column(length:100)]
    #[Assert\NotBlank(message: "You must complete all empty fields")]
    #[Assert\Regex(pattern: "/^[A-Z][a-zA-Z]*$/", message: "The lastname must start with a capital letter")]
    #[Groups("utilisateurs")]
    private ?string $prenom=null;


    #[ORM\OneToMany(targetEntity: 'App\Entity\Role', mappedBy: 'id_user')]
    private $roles;


    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function setIdUser(int $id_user): self
    {
         $this->id_user=$id_user;

         return $this;
    }

    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(string $cin): self
    {
        $this->cin = $cin;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function __toString(): string
{
    return $this->getIdUser();
}


}
