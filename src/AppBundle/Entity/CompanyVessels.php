<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompanyVessels
 *
 * @ORM\Table(name="company_vessels", indexes={@ORM\Index(name="IDX_E31F26DA979B1AD6", columns={"company_id"}), @ORM\Index(name="IDX_E31F26DADA760B40", columns={"depart_port_id"}), @ORM\Index(name="IDX_E31F26DA73094C3D", columns={"arrive_port_id"})})
 * @ORM\Entity
 */
class CompanyVessels
{
    /**
     * @var string
     *
     * @ORM\Column(name="vessel_type", type="string", length=30, nullable=false)
     */
    private $vesselType;

    /**
     * @var string
     *
     * @ORM\Column(name="depart_time", type="string", length=35, nullable=false)
     */
    private $departTime;

    /**
     * @var string
     *
     * @ORM\Column(name="arrive_time", type="string", length=35, nullable=false)
     */
    private $arriveTime;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=155, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="sched_day", type="string", length=35, nullable=false)
     */
    private $schedDay;

    /**
     * @var string
     *
     * @ORM\Column(name="pass_price_range", type="string", length=150, nullable=true)
     */
    private $passPriceRange;

    /**
     * @var string
     *
     * @ORM\Column(name="vehi_price_range", type="string", length=150, nullable=true)
     */
    private $vehiPriceRange;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="company_vessels_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Companies
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Companies")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     * })
     */
    private $company;

    /**
     * @var \AppBundle\Entity\SeaPorts
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SeaPorts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depart_port_id", referencedColumnName="id")
     * })
     */
    private $departPort;

    /**
     * @var \AppBundle\Entity\SeaPorts
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SeaPorts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="arrive_port_id", referencedColumnName="id")
     * })
     */
    private $arrivePort;



    /**
     * Set vesselType
     *
     * @param string $vesselType
     *
     * @return CompanyVessels
     */
    public function setVesselType($vesselType)
    {
        $this->vesselType = $vesselType;

        return $this;
    }

    /**
     * Get vesselType
     *
     * @return string
     */
    public function getVesselType()
    {
        return $this->vesselType;
    }

    /**
     * Set departTime
     *
     * @param string $departTime
     *
     * @return CompanyVessels
     */
    public function setDepartTime($departTime)
    {
        $this->departTime = $departTime;

        return $this;
    }

    /**
     * Get departTime
     *
     * @return string
     */
    public function getDepartTime()
    {
        return $this->departTime;
    }

    /**
     * Set arriveTime
     *
     * @param string $arriveTime
     *
     * @return CompanyVessels
     */
    public function setArriveTime($arriveTime)
    {
        $this->arriveTime = $arriveTime;

        return $this;
    }

    /**
     * Get arriveTime
     *
     * @return string
     */
    public function getArriveTime()
    {
        return $this->arriveTime;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return CompanyVessels
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
     * Set schedDay
     *
     * @param string $schedDay
     *
     * @return CompanyVessels
     */
    public function setSchedDay($schedDay)
    {
        $this->schedDay = $schedDay;

        return $this;
    }

    /**
     * Get schedDay
     *
     * @return string
     */
    public function getSchedDay()
    {
        return $this->schedDay;
    }

    /**
     * Set passPriceRange
     *
     * @param string $passPriceRange
     *
     * @return CompanyVessels
     */
    public function setPassPriceRange($passPriceRange)
    {
        $this->passPriceRange = $passPriceRange;

        return $this;
    }

    /**
     * Get passPriceRange
     *
     * @return string
     */
    public function getPassPriceRange()
    {
        return $this->passPriceRange;
    }

    /**
     * Set vehiPriceRange
     *
     * @param string $vehiPriceRange
     *
     * @return CompanyVessels
     */
    public function setVehiPriceRange($vehiPriceRange)
    {
        $this->vehiPriceRange = $vehiPriceRange;

        return $this;
    }

    /**
     * Get vehiPriceRange
     *
     * @return string
     */
    public function getVehiPriceRange()
    {
        return $this->vehiPriceRange;
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
     * Set company
     *
     * @param \AppBundle\Entity\Companies $company
     *
     * @return CompanyVessels
     */
    public function setCompany(\AppBundle\Entity\Companies $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \AppBundle\Entity\Companies
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set departPort
     *
     * @param \AppBundle\Entity\SeaPorts $departPort
     *
     * @return CompanyVessels
     */
    public function setDepartPort(\AppBundle\Entity\SeaPorts $departPort = null)
    {
        $this->departPort = $departPort;

        return $this;
    }

    /**
     * Get departPort
     *
     * @return \AppBundle\Entity\SeaPorts
     */
    public function getDepartPort()
    {
        return $this->departPort;
    }

    /**
     * Set arrivePort
     *
     * @param \AppBundle\Entity\SeaPorts $arrivePort
     *
     * @return CompanyVessels
     */
    public function setArrivePort(\AppBundle\Entity\SeaPorts $arrivePort = null)
    {
        $this->arrivePort = $arrivePort;

        return $this;
    }

    /**
     * Get arrivePort
     *
     * @return \AppBundle\Entity\SeaPorts
     */
    public function getArrivePort()
    {
        return $this->arrivePort;
    }
}
