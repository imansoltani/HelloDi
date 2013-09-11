<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="Exceptions")
 */
class Exceptions
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /** 
     * @ORM\Column(type="datetime", nullable=false, name="date")
     */
    private $Date;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Description;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $Username;


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
     * Set Date
     *
     * @param \DateTime $date
     * @return Exceptions
     */
    public function setDate($date)
    {
        $this->Date = $date;
    
        return $this;
    }

    /**
     * Get Date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->Date;
    }

    /**
     * Set Description
     *
     * @param string $description
     * @return Exceptions
     */
    public function setDescription($description)
    {
        $this->Description = $description;
    
        return $this;
    }

    /**
     * Get Description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->Description;
    }


    /**
     * Set Username
     *
     * @param string $username
     * @return Exceptions
     */
    public function setUsername($username)
    {
        $this->Username = $username;
    
        return $this;
    }

    /**
     * Get Username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->Username;
    }
}