<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Distances
 *
 * @ORM\Table(name="distances", indexes={@ORM\Index(name="IDX_81ADEE271712CB45", columns={"sea_port_id"}), @ORM\Index(name="IDX_81ADEE2765155527", columns={"target_town_city_id"})})
 * @ORM\Entity
 */
class Distances
{
    /**
     * @var string
     *
     * @ORM\Column(name="distance", type="decimal", precision=10, scale=0, nullable=false)
     */
    private $distance;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="distances_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \AppBundle\Entity\SeaPorts
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SeaPorts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sea_port_id", referencedColumnName="id")
     * })
     */
    private $seaPort;

    /**
     * @var \AppBundle\Entity\TownCities
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TownCities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="target_town_city_id", referencedColumnName="id")
     * })
     */
    private $targetTownCity;



    /**
     * Set distance
     *
     * @param string $distance
     *
     * @return Distances
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance
     *
     * @return string
     */
    public function getDistance()
    {
        return $this->distance;
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
     * Set seaPort
     *
     * @param \AppBundle\Entity\SeaPorts $seaPort
     *
     * @return Distances
     */
    public function setSeaPort(\AppBundle\Entity\SeaPorts $seaPort = null)
    {
        $this->seaPort = $seaPort;

        return $this;
    }

    /**
     * Get seaPort
     *
     * @return \AppBundle\Entity\SeaPorts
     */
    public function getSeaPort()
    {
        return $this->seaPort;
    }

    /**
     * Set targetTownCity
     *
     * @param \AppBundle\Entity\TownCities $targetTownCity
     *
     * @return Distances
     */
    public function setTargetTownCity(\AppBundle\Entity\TownCities $targetTownCity = null)
    {
        $this->targetTownCity = $targetTownCity;

        return $this;
    }

    /**
     * Get targetTownCity
     *
     * @return \AppBundle\Entity\TownCities
     */
    public function getTargetTownCity()
    {
        return $this->targetTownCity;
    }
}
