<?php

namespace CHU\gestionUtilisateurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * soignant
 *
 * @ORM\Table(name="soignant")
 * @ORM\Entity(repositoryClass="CHU\gestionUtilisateurBundle\Repository\soignantRepository")
 */
class soignant
{

    /**
     * @ORM\ManyToMany(targetEntity="CHU\gestionUtilisateurBundle\Entity\salarie")
     */
    private $salarie;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=255)
     */
    private $mail;

    /**
     * @var string
     *
     * @ORM\Column(name="structure", type="string", length=255)
     */
    private $structure;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=255)
     */
    private $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="fonction", type="string", length=255)
     */
    private $fonction;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return soignant
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return soignant
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set mail
     *
     * @param string $mail
     *
     * @return soignant
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set structure
     *
     * @param string $structure
     *
     * @return soignant
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get structure
     *
     * @return string
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Set ville
     *
     * @param string $ville
     *
     * @return soignant
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set fonction
     *
     * @param string $fonction
     *
     * @return soignant
     */
    public function setFonction($fonction)
    {
        $this->fonction = $fonction;

        return $this;
    }

    /**
     * Get fonction
     *
     * @return string
     */
    public function getFonction()
    {
        return $this->fonction;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->salarie = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add salarie
     *
     * @param \CHU\gestionUtilisateurBundle\Entity\salarie $salarie
     *
     * @return soignant
     */
    public function addSalarie(\CHU\gestionUtilisateurBundle\Entity\salarie $salarie)
    {
        $this->salarie[] = $salarie;

        return $this;
    }

    /**
     * Remove salarie
     *
     * @param \CHU\gestionUtilisateurBundle\Entity\salarie $salarie
     */
    public function removeSalarie(\CHU\gestionUtilisateurBundle\Entity\salarie $salarie)
    {
        $this->salarie->removeElement($salarie);
    }

    /**
     * Get salarie
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSalarie()
    {
        return $this->salarie;
    }
}