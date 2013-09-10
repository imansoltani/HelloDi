<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="Log")
 */
class Log
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
     * @ORM\Column(type="string", nullable=true)
     */
    private $Controller;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $User;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $Path;



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
     * @return Log
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
     * Set Controller
     *
     * @param string $controller
     * @return Log
     */
    public function setController($controller)
    {
        $this->Controller = $controller;
    
        return $this;
    }

    /**
     * Get Controller
     *
     * @return string 
     */
    public function getController()
    {
        return $this->Controller;
    }

    /**
     * Set User
     *
     * @param string $user
     * @return Log
     */
    public function setUser($user)
    {
        $this->User = $user;
    
        return $this;
    }

    /**
     * Get User
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * Set Path
     *
     * @param string $path
     * @return Log
     */
    public function setPath($path)
    {
        $this->Path = $path;
    
        return $this;
    }

    /**
     * Get Path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->Path;
    }
}