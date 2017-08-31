<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VesselAccomodations
 *
 * @ORM\Table(name="vessel_accomodations", indexes={@ORM\Index(name="IDX_76FE33F814AF1953", columns={"vessel_id"})})
 * @ORM\Entity
 */
class VesselAccomodations
{
    /**
     * @var string
     *
     * @ORM\Column(name="accomodation", type="string", length=150, nullable=false)
     */
    private $accomodation;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="features", type="text", nullable=true)
     */
    private $features;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vessel_accomodations_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \AppBundle\Entity\CompanyVessels
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CompanyVessels")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vessel_id", referencedColumnName="id")
     * })
     */
    private $vessel;



    /**
     * Set accomodation
     *
     * @param string $accomodation
     *
     * @return VesselAccomodations
     */
    public function setAccomodation($accomodation)
    {
        $this->accomodation = $accomodation;

        return $this;
    }

    /**
     * Get accomodation
     *
     * @return string
     */
    public function getAccomodation()
    {
        return $this->accomodation;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return VesselAccomodations
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set features
     *
     * @param string $features
     *
     * @return VesselAccomodations
     */
    public function setFeatures($features)
    {
        $this->features = $features;

        return $this;
    }

    /**
     * Get features
     *
     * @return string
     */
    public function getFeatures()
    {
        return $this->features;
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
     * Set vessel
     *
     * @param \AppBundle\Entity\CompanyVessels $vessel
     *
     * @return VesselAccomodations
     */
    public function setVessel(\AppBundle\Entity\CompanyVessels $vessel = null)
    {
        $this->vessel = $vessel;

        return $this;
    }

    /**
     * Get vessel
     *
     * @return \AppBundle\Entity\CompanyVessels
     */
    public function getVessel()
    {
        return $this->vessel;
    }
}
