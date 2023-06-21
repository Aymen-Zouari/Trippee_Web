<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\LocateurRepository;
use App\Entity\Role;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Enum\Etat;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: LocateurRepository::class)]
class Locateur implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("locateurs")]
    private ?int $id_loc = null;


    #[ORM\Column(length: 200, nullable: true)]
    #[Groups("locateurs")]
    private ?string $img = null;



    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "You must complete all empty fields")]
    #[Groups("locateurs")]
    private ?string $nom_agence = null;



    #[ORM\Column(length: 8)]
    #[Assert\NotBlank(message: "You must complete all empty fields")]
    #[Assert\Regex(pattern: "/^\d{8}$/", message: "Gsm must be 8 numbers")]
    #[Groups("locateurs")]
    private ?int $gsm = null;


   #[ORM\Column(length: 150)]
   #[Assert\NotBlank(message: "You must complete all empty fields")]
   #[Assert\Email(message: "Missing @ or .")]
   #[Groups("locateurs")]
    private ?string $email = null;


    #[ORM\Column(length: 100)]
    #[Groups("locateurs")]
    private ?string $password = null;



    #[ORM\ManyToOne(inversedBy:'locateurs')]
    #[ORM\JoinColumn(name: "id_role", referencedColumnName: "id_role")]
    private ?Role $id_role;

    #[ORM\Column(options: ['default' => 'enabled'], columnDefinition: "ENUM('enabled', 'disabled')")]
    private ?string $etat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    public function getIdLoc(): ?int
    {
        return $this->id_loc;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(string $img): self
    {
        $this->img = $img;

        return $this;
    }

    public function getNomAgence(): ?string
    {
        return $this->nom_agence;
    }

    public function setNomAgence(string $nom_agence): self
    {
        $this->nom_agence = $nom_agence;

        return $this;
    }

    public function getGsm(): ?int
    {
        return $this->gsm;
    }

    public function setGsm(int $gsm): self
    {
        $this->gsm = $gsm;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getIdRole(): ?Role
    {
        return $this->id_role;
    }

    public function setIdRole(?Role $id_role): self
    {
        $this->id_role = $id_role;

        return $this;
    }


    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }


    public function getNom(): ?string
    {
        if ($this->id_role && $this->id_role->getIdUser()) {
            return $this->id_role->getIdUser()->getNom();
        }

        return null;
    }
    public function setNom(?string $nom): self
{
    if ($this->id_role && $this->id_role->getIdUser()) {
        $this->id_role->getIdUser()->setNom($nom);
    }

    return $this;
}

    public function getPrenom(): ?string
    {
        if ($this->id_role && $this->id_role->getIdUser()) {
            return $this->id_role->getIdUser()->getPrenom();
        }

        return null;
    }
    public function setPrenom(?string $prenom): self
{
    if ($this->id_role && $this->id_role->getIdUser()) {
        $this->id_role->getIdUser()->setPrenom($prenom);
    }

    return $this;
}

    public function getCin(): ?string
    {
        if ($this->id_role && $this->id_role->getIdUser()) {
            return $this->id_role->getIdUser()->getCin();
        }

        return null;
    }

    public function setCin(?string $cin): self
{
    if ($this->id_role && $this->id_role->getIdUser()) {
        $this->id_role->getIdUser()->setCin($cin);
    }

    return $this;
}

public function getResetToken(): ?string
{
    return $this->resetToken;
}

public function setResetToken(?string $resetToken): self
{
    $this->resetToken = $resetToken;

    return $this;
}

public function getRoles()
{
    // implement this method to return an array of roles
}

public function getUsername()
{
    return $this->email;
}

public function getSalt()
{
    // you can leave this method empty
}

public function eraseCredentials()
{
    // you can leave this method empty
}

   
}
