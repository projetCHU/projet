<?php

namespace CHU\EtudeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Etude
 *
 * @ORM\Table(name="etude")
 * @ORM\Entity(repositoryClass="CHU\EtudeBundle\Repository\EtudeRepository")
 */
class Etude
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
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=255, unique=true)
     */
    private $name;


    /**
     * @var string
     * @ORM\Column(name="Content", type="text", nullable=false)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="Commentary", type="text", nullable=true)
     */
    private $commentary;


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
     * Set name
     *
     * @param string $name
     *
     * @return Etude
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set commentary
     *
     * @param string $commentary
     *
     * @return Etude
     */
    public function setCommentary($commentary)
    {
        $this->commentary = $commentary;

        return $this;
    }

    /**
     * Get commentary
     *
     * @return string
     */
    public function getCommentary()
    {
        return $this->commentary;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Etude
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
