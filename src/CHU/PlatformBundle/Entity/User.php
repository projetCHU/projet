<?php
// src/AppBundle/Entity/User.php

namespace CHU\PlatformBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
   
    /**
     * @ORM\ManyToMany(targetEntity="CHU\PlatformBundle\Entity\Salarie", mappedBy="users")
     */
    protected $patients;
    
    
    public function __construct()
    {
        parent::__construct();
        // your own logic
    }



    /**
     * Add patient
     *
     * @param \CHU\PlatformBundle\Entity\Salarie $patient
     *
     * @return User
     */
    public function addPatient(\CHU\PlatformBundle\Entity\Salarie $patient)
    {
        $this->patients[] = $patient;

        return $this;
    }

    /**
     * Remove patient
     *
     * @param \CHU\PlatformBundle\Entity\Salarie $patient
     */
    public function removePatient(\CHU\PlatformBundle\Entity\Salarie $patient)
    {
        $this->patients->removeElement($patient);
    }

    /**
     * Get patients
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPatients()
    {
        return $this->patients;
    }
}
