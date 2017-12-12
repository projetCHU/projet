<?php

namespace CHU\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Salarie
 *
 * @ORM\Table(name="salarie")
 * @ORM\Entity(repositoryClass="CHU\PlatformBundle\Repository\SalarieRepository")
 */
class Salarie
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * One Product has One Shipment.
     * @ORM\ManyToMany(targetEntity="CHU\PlatformBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;
    
    /**
     * @ORM\OneToOne(targetEntity="CHU\PlatformBundle\Entity\Compte")
     * @ORM\JoinColumn(nullable=false)
    */
    private $compte;
    
    /**
     * @ORM\OneToOne(targetEntity="CHU\PlatformBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
    */
    private $userCompte;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add user
     *
     * @param \CHU\PlatformBundle\Entity\User $user
     *
     * @return Salarie
     */
    public function addUser(\CHU\PlatformBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \CHU\PlatformBundle\Entity\User $user
     */
    public function removeUser(\CHU\PlatformBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set compte
     *
     * @param \CHU\PlatformBundle\Entity\Compte $compte
     *
     * @return Salarie
     */
    public function setCompte(\CHU\PlatformBundle\Entity\Compte $compte)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get compte
     *
     * @return \CHU\PlatformBundle\Entity\Compte
     */
    public function getCompte()
    {
        return $this->compte;
    }

    /**
     * Set userCompte
     *
     * @param \CHU\PlatformBundle\Entity\User $userCompte
     *
     * @return Salarie
     */
    public function setUserCompte(\CHU\PlatformBundle\Entity\User $userCompte)
    {
        $this->userCompte = $userCompte;

        return $this;
    }

    /**
     * Get userCompte
     *
     * @return \CHU\PlatformBundle\Entity\User
     */
    public function getUserCompte()
    {
        return $this->userCompte;
    }
}
