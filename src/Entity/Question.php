<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuestionRepository::class)
 */
class Question
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $speciesA;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $speciesB;

    /**
     * @ORM\Column(type="float")
     */
    private $rate;

    /**
     * @ORM\ManyToOne(targetEntity=Survey::class, inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $survey;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpeciesA(): ?string
    {
        return $this->speciesA;
    }

    public function setSpeciesA(string $speciesA): self
    {
        $this->speciesA = $speciesA;

        return $this;
    }

    public function getSpeciesB(): ?string
    {
        return $this->speciesB;
    }

    public function setSpeciesB(string $speciesB): self
    {
        $this->speciesB = $speciesB;

        return $this;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(float $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): self
    {
        $this->survey = $survey;

        return $this;
    }
}
