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
    private $user;
    


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

   
}
