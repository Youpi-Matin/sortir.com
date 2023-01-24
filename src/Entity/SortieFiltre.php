<?php

namespace App\Entity;

use DateTime;

class SortieFiltre
{
    private ?Campus $campus = null;

    private ?string $search = null;

    private ?DateTime $dateMin = null;

    private ?DateTime $dateMax = null;

    private ?bool $organisateurice = null;

    private ?bool $inscrite = null;

    private ?bool $noninscrite = null;



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
}
