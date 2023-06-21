<?php

namespace App\Entity;

use App\Repository\CoVoiturageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Chauffeur;
use Symfony\Component\Validator\Constraints as Assert;
use Twilio\Rest\Client;
use Symfony\Component\Serializer\Annotation\Groups;



#[ORM\Entity(repositoryClass: CoVoiturageRepository::class)]
class CoVoiturage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("covs")]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "You must complete all empty fields")]
    #[Groups("covs")]
    private ?string $depart = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "You must complete all empty fields")]
    #[Groups("covs")]
    private ?string $destination = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "You must complete all empty fields")]
    #[Assert\GreaterThanOrEqual("today")]
    #[Groups("covs")]
    private ?\DateTimeInterface $date_dep = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "You must complete all empty fields")]
    #[Groups("covs")]
    private ?int $nmbr_place = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("covs")]
    private ?string $cov_img = null;

    #[ORM\ManyToOne(inversedBy:'id_co')]
    #[ORM\JoinColumn(name: "id_ch", referencedColumnName: "id_ch")]
    #[Groups("covs")]
    private ?Chauffeur $id_ch = null;

    #[ORM\OneToMany(mappedBy: 'id_co', targetEntity: Participation::class)]
    private Collection $participations;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepart(): ?string
    {
        return $this->depart;
    }

    public function setDepart(string $depart): self
    {
        $this->depart = $depart;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDateDep(): ?\DateTimeInterface
    {
        return $this->date_dep;
    }

    public function setDateDep(\DateTimeInterface $date_dep): self
    {
        $this->date_dep = $date_dep;

        return $this;
    }

    public function getNmbrPlace(): ?int
    {
        return $this->nmbr_place;
    }

    public function setNmbrPlace(int $nmbr_place): self
    {
        $this->nmbr_place = $nmbr_place;

        return $this;
    }

    public function getCovImg(): ?string
    {
        return $this->cov_img;
    }

    public function setCovImg(?string $cov_img): self
    {
        $this->cov_img = $cov_img;

        return $this;
    }

    public function getIdCh(): ?Chauffeur
    {
        return $this->id_ch;
    }

    public function setIdCh(?Chauffeur $id_ch): self
    {
        $this->id_ch = $id_ch;

        return $this;
    }

    public function toString(): string
    {
        return "VoitureRepository Object";
    }

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): self
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setIdCo($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): self
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getIdCo() === $this) {
                $participation->setIdCo(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->id;
    }


    function weather_temp(string $ville): float
    {
        // Url de l'API
        $url = "http://api.openweathermap.org/data/2.5/weather?q={$ville}&lang=fr&units=metric&appid=c254001f0f2a23d71745d80d4fd561bc";

        // On get les resultat
        $raw = file_get_contents($url);
        // Décode la chaine JSON
        $json = json_decode($raw);

        // Nom de la ville
        $name = $json->name;

        // Météo
        $weather = $json->weather[0]->main;
        $desc = $json->weather[0]->description;

        // Températures
        $temp = $json->main->temp;
        $feel_like = $json->main->feels_like;

        // Vent
        $speed = $json->wind->speed;
        $deg = $json->wind->deg;

        return $temp;
    }

    public function send_msg(String $num): void
    {

        $accountSid ='AC1ed373981440ff3b6ccefc4eb68223b7';
        $authToken = '0d8dc3f99ee3ae809e32d57b9b4ef3b7';
        $client = new Client($accountSid, $authToken);
        $message = $client->messages->create(
            $num, // recipient's phone number
            array(
                'from' => '+12766226509', // your Twilio phone number
                'body' => 'Participation added !'
            )
        );
    }
}