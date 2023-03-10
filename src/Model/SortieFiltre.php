<?php

namespace App\Model;

use App\Entity\Campus;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

class SortieFiltre
{
    #[Assert\NotNull()]
    private ?Campus $campus = null;

    private ?string $search = null;

    #[Assert\LessThan(
        propertyPath: 'dateMax'
    )]
    private ?DateTime $dateMin = null;

    #[Assert\GreaterThan(
        propertyPath: 'dateMin'
    )]
    private ?DateTime $dateMax = null;

    private ?bool $organisateurice = false;

    private ?bool $inscrite = false;

    private ?bool $noninscrite = false;

    private ?bool $passee = false;



    /**
     * Get the value of campus
     */
    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    /**
     * Set the value of campus
     */
    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * Get the value of search
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }

    /**
     * Set the value of search
     */
    public function setSearch(?string $search): self
    {
        $this->search = $search;

        return $this;
    }

    /**
     * Get the value of dateMin
     */
    public function getDateMin(): ?DateTime
    {
        return $this->dateMin;
    }

    /**
     * Set the value of dateMin
     */
    public function setDateMin(?DateTime $dateMin): self
    {
        $this->dateMin = $dateMin;

        return $this;
    }

    /**
     * Get the value of dateMax
     */
    public function getDateMax(): ?DateTime
    {
        return $this->dateMax;
    }

    /**
     * Set the value of dateMax
     */
    public function setDateMax(?DateTime $dateMax): self
    {
        $this->dateMax = $dateMax;

        return $this;
    }

    /**
     * Get the value of organisateurice
     */
    public function isOrganisateurice(): ?bool
    {
        return $this->organisateurice;
    }

    /**
     * Set the value of organisateurice
     */
    public function setOrganisateurice(?bool $organisateurice): self
    {
        $this->organisateurice = $organisateurice;

        return $this;
    }

    /**
     * Get the value of inscrite
     */
    public function isInscrite(): ?bool
    {
        return $this->inscrite;
    }

    /**
     * Set the value of inscrite
     */
    public function setInscrite(?bool $inscrite): self
    {
        $this->inscrite = $inscrite;

        return $this;
    }

    /**
     * Get the value of noninscrite
     */
    public function isNoninscrite(): ?bool
    {
        return $this->noninscrite;
    }

    /**
     * Set the value of noninscrite
     */
    public function setNoninscrite(?bool $noninscrite): self
    {
        $this->noninscrite = $noninscrite;

        return $this;
    }

    /**
     * Get the value of passee
     */
    public function isPassee(): ?bool
    {
        return $this->passee;
    }

    /**
     * Set the value of passee
     */
    public function setPassee(?bool $passee): self
    {
        $this->passee = $passee;

        return $this;
    }
}
