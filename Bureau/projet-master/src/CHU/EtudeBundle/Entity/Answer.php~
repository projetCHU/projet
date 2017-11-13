<?php

namespace CHU\EtudeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;




/**
INSTRUCTIONS et AIDES.
  Cette entité gère les réponses à un formulaire.
  Elle contient :
    -Un ID : son identifiant.

    -Une etude : Il s'agit de lentité Etude à laquelle est reliée l'entité Answer.
        en relation ManyToOne (Answer -> Etude).-Un champ question : il rappelle l'intitulé de la question.
      REMARQUE: Les espaces sont remplacé par des underscore '_'.

    -Un champ reponses : contient la réponse au questions du questionnaire.
        Les reponses sont une chaine JSON comme suit :
          exemple : { "nom_quest1": "reponse1", "nom_quest2": "reponse2", "nom_quest3":["reponse3.1","reponse3.2"], etc...}
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
     * @var integer
     *
     * @ORM\Column(name="user", type="integer", nullable=false)
     */
    private $participant;

    /**
     * @var text
     *
     * @ORM\Column(name="reponses", type="text")
     */
    private $reponses;

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

    /**
     * Set reponses
     *
     * @param string $reponses
     *
     * @return Answer
     */
    public function setReponses($reponses)
    {
        $this->reponses = $reponses;

        return $this;
    }

    /**
     * Get reponses
     *
     * @return string
     */
    public function getReponses()
    {
        return $this->reponses;
    }

    /**
     * Set participant
     *
     * @param integer $participant
     *
     * @return Answer
     */
    public function setParticipant($participant)
    {
        $this->participant = $participant;

        return $this;
    }

    /**
     * Get participant
     *
     * @return integer
     */
    public function getParticipant()
    {
        return $this->participant;
    }
}
