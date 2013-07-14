<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as CTRL;

/**
 * @ORM\Entity
 * @ORM\Table(name="Nothing")
 * @ORM\HasLifecycleCallbacks()
 */
class Nothing
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;



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