<?php

namespace CHU\EtudeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;




/**
INSTRUCTIONS et AIDES.
  Cette entité gère les réponses à un formulaire.
  Elle contient :
    -Un ID : son identifiant.
    -Un contenu : les réponses au formulaire stockées dans une chaîne JSON comme suit :
        {{q_index: 'numéro de question', r_content : 'reponse(s) à la question' }{etc....}}
    -une etude : Il s'agit de lentité Etude à laquelle est reliée l'entité Answer.
        en relation ManyToOne (Answer -> Etude).
*/

/**
 * Answer
 *
 * @ORM\Table(name="answer")
 * @ORM\Entity(repositoryClass="CHU\EtudeBundle\Repository\AnswerRepository")
 */
class Answer
{
    /**
     * @ORM\ManyToOne(targetEntity="CHU\EtudeBundle\Entity\Etude")
     */
    public $etude;

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
     * @ORM\Column(name="contenu", type="string", length=255)
     */
    private $contenu;

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
     * Set contenu
     *
     * @param string $contenu
     *
     * @return Answer
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set etude
     *
     * @param \CHU\EtudeBundle\Entity\Etude $etude
     *
     * @return Answer
     */
    public function setEtude(\CHU\EtudeBundle\Entity\Etude $etude = null)
    {
        $this->etude = $etude;

        return $this;
    }

    /**
     * Get etude
     *
     * @return \CHU\EtudeBundle\Entity\Etude
     */
    public function getetude()
    {
        return $this->etude;
    }
}
