<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\ReclamationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("reclamations")]
    private ?int $id = null;


    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Type cannot be empty')]
    #[Assert\Regex(pattern: '/^[^0-9]*$/', message: 'Type cannot contain any numbers')]
    #[Groups("reclamations")]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Commentaire cannot be empty')]
    #[Assert\Length(min: 16, minMessage: 'Commentaire must be at least {{ limit }} characters long')]
    #[Groups("reclamations")]
    private ?string $commentaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("reclamations")]
    private ?string $etat = null;

    #[ORM\Column(nullable: false)]
    #[Groups("reclamations")]
    private ?int $id_user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotNull(message: 'Date creation cannot be empty')]
    #[Assert\EqualTo(value: 'today', message: 'Date creation must be the same as the PC date')]
    #[Groups("reclamations")]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("reclamations")]
    private ?string $image = null;

    #[ORM\OneToMany(mappedBy: 'id_rec', targetEntity: Reponse::class)]
    private Collection $reponses;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function setIdUser(?int $id_user): self
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): self
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setIdRec($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): self
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getIdRec() === $this) {
                $reponse->setIdRec(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->id;
    }
}
