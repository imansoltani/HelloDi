<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="operator")
 */
class Operator
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45, nullable=false, name="Name")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="carrier_code", unique = true)
     */
    private $carrierCode;

    /**
     * @ORM\Column(type="string", length=45, nullable=True, name="operator_logo")
     */
    private $Logo;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", mappedBy="operator")
     */
    private $Item;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Item = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Operator
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
     * Set Logo
     *
     * @param string $logo
     * @return Operator
     */
    public function setLogo($logo)
    {
        $this->Logo = $logo;
    
        return $this;
    }

    /**
     * Get Logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->Logo;
    }

    /**
     * Add Item
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $item
     * @return Operator
     */
    public function addItem(\HelloDi\DiDistributorsBundle\Entity\Item $item)
    {
        $this->Item[] = $item;
    
        return $this;
    }

    /**
     * Remove Item
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $item
     */
    public function removeItem(\HelloDi\DiDistributorsBundle\Entity\Item $item)
    {
        $this->Item->removeElement($item);
    }

    /**
     * Get Item
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItem()
    {
        return $this->Item;
    }

    public function getAbsolutePath()
    {
        return null === $this->Logo ? null : $this->getUploadRootDir().'/'.$this->Logo;
    }

    public function getWebPath()
    {
        return $this->getUploadDir().'/'. (null === $this->Logo ? "0.png" : $this->Logo);
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'uploads/logos';
    }

    /**
     * @Assert\File(maxSize="6000000")
     */
    private $file;

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    public function upload()
    {
        if($this->file === null) return;

        if(file_exists($this->getAbsolutePath()))
            unlink($this->getAbsolutePath());

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->id.'.'.$this->getFile()->getClientOriginalExtension()
        );

        // set the path property to the filename where you've saved the file
        $this->Logo = $this->id.'.'.$this->getFile()->getClientOriginalExtension();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    /**
     * Set carrierCode
     *
     * @param string $carrierCode
     * @return Operator
     */
    public function setCarrierCode($carrierCode)
    {
        $this->carrierCode = $carrierCode;
    
        return $this;
    }

    /**
     * Get carrierCode
     *
     * @return string 
     */
    public function getCarrierCode()
    {
        return $this->carrierCode;
    }

    public function getNameCarrier()
    {
        return $this->name.($this->carrierCode!=null?' ('.$this->carrierCode.')':'');
    }
}