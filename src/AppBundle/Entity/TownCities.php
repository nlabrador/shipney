<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TownCities
 *
 * @ORM\Table(name="town_cities")
 * @ORM\Entity
 */
class TownCities
{
    /**
     * @var string
     *
     * @ORM\Column(name="town_city", type="string", length=200, nullable=false)
     */
    private $townCity;

    /**
     * @var string
     *
     * @ORM\Column(name="province", type="string", length=150, nullable=false)
     */
    private $province;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="town_cities_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;



    /**
     * Set townCity
     *
     * @param string $townCity
     *
     * @return TownCities
     */
    public function setTownCity($townCity)
    {
        $this->townCity = $townCity;

        return $this;
    }

    /**
     * Get townCity
     *
     * @return string
     */
    public function getTownCity()
    {
        return $this->townCity;
    }

    /**
     * Set province
     *
     * @param string $province
     *
     * @return TownCities
     */
    public function setProvince($province)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Get province
     *
     * @return string
     */
    public function getProvince()
    {
        return $this->province;
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
}
