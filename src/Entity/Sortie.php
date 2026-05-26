<?php

namespace App\Entity;

use App\Enum\EtatSortie;
use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PhpParser\Builder\Enum_;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom de la sortie est obligatoire")]
    #[Assert\Length(min:5, max:100,
        minMessage: 'Le titre du film est trop court',
        maxMessage: 'Le titre du film est trop long',)]
    private ?string $nom = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTime $dateHeureDebut = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?int $duree = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTime $dateLimiteInscription = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?int $nbInscriptionsMax = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site = null;

    #[ORM\ManyToOne(inversedBy: 'listeSortiesCrees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $organisateur = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'listeSorties')]
    private Collection $listeParticipants;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    private ?Lieu $lieu = null;
    #[ORM\Column(enumType: EtatSortie::class)]
    private ?EtatSortie $etatSortie = null;

    public function __construct()
    {
        $this->listeParticipants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTime
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTime $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTime
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTime $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): static
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getListeParticipants(): Collection
    {
        return $this->listeParticipants;
    }

    public function addListeParticipant(Participant $listeParticipant): static
    {
        if (!$this->listeParticipants->contains($listeParticipant)) {
            $this->listeParticipants->add($listeParticipant);
        }

        return $this;
    }

    public function removeListeParticipant(Participant $listeParticipant): static
    {
        $this->listeParticipants->removeElement($listeParticipant);

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->lieu = $lieu;
        return $this;
    }
    public function getEtatSortie(): ?EtatSortie
    {
        return $this->etatSortie;
    }

    public function setEtatSortie(EtatSortie $etatSortie): static
    {
        $this->etatSortie = $etatSortie;

        return $this;
    }

}
