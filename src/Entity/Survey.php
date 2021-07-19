<?php

namespace App\Entity;

use App\Repository\SurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SurveyRepository::class)
 */
class Survey
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
    private $uniqid;

    /**
     * @ORM\OneToMany(targetEntity=Question::class, mappedBy="survey", orphanRemoval=true)
     */
    private $questions;

    /**
     * @ORM\Column(type="integer")
     */
    private $questionNumber = 1;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $isOver = false;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUniqid(): ?string
    {
        return $this->uniqid;
    }

    public function setUniqid(string $uniqid): self
    {
        $this->uniqid = $uniqid;

        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setSurvey($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getSurvey() === $this) {
                $question->setSurvey(null);
            }
        }

        return $this;
    }

    public function getQuestionNumber(): ?int
    {
        return $this->questionNumber;
    }

    public function setQuestionNumber(int $questionNumber): self
    {
        $this->questionNumber = $questionNumber;

        return $this;
    }

    public function getIsOver(): ?bool
    {
        return $this->isOver;
    }

    public function setIsOver(bool $isOver): self
    {
        $this->isOver = $isOver;

        return $this;
    }
}
