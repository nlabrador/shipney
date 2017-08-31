<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SeaPorts
 *
 * @ORM\Table(name="sea_ports", indexes={@ORM\Index(name="IDX_28889FC285478676", columns={"town_city_id"})})
 * @ORM\Entity
 */
class SeaPorts
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=150, nullable=false)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="sea_ports_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \AppBundle\Entity\TownCities
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TownCities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="town_city_id", referencedColumnName="id")
     * })
     */
    private $townCity;



    /**
     * Set name
     *
     * @param string $name
     *
     * @return SeaPorts
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set townCity
     *
     * @param \AppBundle\Entity\TownCities $townCity
     *
     * @return SeaPorts
     */
    public function setTownCity(\AppBundle\Entity\TownCities $townCity = null)
    {
        $this->townCity = $townCity;

        return $this;
    }

    /**
     * Get townCity
     *
     * @return \AppBundle\Entity\TownCities
     */
    public function getTownCity()
    {
        return $this->townCity;
    }
}
