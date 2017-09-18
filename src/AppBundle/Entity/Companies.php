<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Companies
 *
 * @ORM\Table(name="companies")
 * @ORM\Entity
 */
class Companies
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="address1", type="string", length=255, nullable=true)
     */
    private $address1;

    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="string", length=200, nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=15, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="booksite", type="string", length=255, nullable=true)
     */
    private $booksite;

    /**
     * @var string
     *
     * @ORM\Column(name="booking_url", type="string", length=250, nullable=true)
     */
    private $bookingUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="promo_url", type="string", length=250, nullable=true)
     */
    private $promoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="offices_url", type="string", length=250, nullable=true)
     */
    private $officesUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="companies_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;



    /**
     * Set name
     *
     * @param string $name
     *
     * @return Companies
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
     * Set address1
     *
     * @param string $address1
     *
     * @return Companies
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address2
     *
     * @param string $address2
     *
     * @return Companies
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Companies
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Companies
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return Companies
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set booksite
     *
     * @param string $booksite
     *
     * @return Companies
     */
    public function setBooksite($booksite)
    {
        $this->booksite = $booksite;

        return $this;
    }

    /**
     * Get booksite
     *
     * @return string
     */
    public function getBooksite()
    {
        return $this->booksite;
    }

    /**
     * Set bookingUrl
     *
     * @param string $bookingUrl
     *
     * @return Companies
     */
    public function setBookingUrl($bookingUrl)
    {
        $this->bookingUrl = $bookingUrl;

        return $this;
    }

    /**
     * Get bookingUrl
     *
     * @return string
     */
    public function getBookingUrl()
    {
        return $this->bookingUrl;
    }

    /**
     * Set promoUrl
     *
     * @param string $promoUrl
     *
     * @return Companies
     */
    public function setPromoUrl($promoUrl)
    {
        $this->promoUrl = $promoUrl;

        return $this;
    }

    /**
     * Get promoUrl
     *
     * @return string
     */
    public function getPromoUrl()
    {
        return $this->promoUrl;
    }

    /**
     * Set officesUrl
     *
     * @param string $officesUrl
     *
     * @return Companies
     */
    public function setOfficesUrl($officesUrl)
    {
        $this->officesUrl = $officesUrl;

        return $this;
    }

    /**
     * Get officesUrl
     *
     * @return string
     */
    public function getOfficesUrl()
    {
        return $this->officesUrl;
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
