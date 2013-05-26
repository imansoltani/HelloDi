<?php
namespace HelloDi\DiDistributorsBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/** 
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="fileNameIDX", columns={"file_name"})})
 */
class Input
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=255, nullable=false, name="file_name")
     */
    private $fileName;

    /** 
     * @ORM\Column(type="date", nullable=false, name="date_insert")
     */
    private $dateInsert;

    /** 
     * @ORM\Column(type="integer", nullable=true, name="batch")
     */
    private $batch;

    /** 
     * @ORM\Column(type="date", nullable=false, name="date_production")
     */
    private $dateProduction;

    /** 
     * @ORM\Column(type="date", nullable=false, name="date_expiry")
     */
    private $dateExpiry;

    /** 
     * @ORM\OneToMany(targetEntity="HelloDi\DiDistributorsBundle\Entity\Code", mappedBy="Input")
     */
    private $Codes;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Item", inversedBy="Inputs")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    private $Item;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\Account", inversedBy="Inputs")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $Account;

    /** 
     * @ORM\ManyToOne(targetEntity="HelloDi\DiDistributorsBundle\Entity\User", inversedBy="Inputs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $User;
	
	public function getAbsolutePath()
    {
        return null === $this->fileName ? null : $this->getUploadRootDir().'/'.$this->fileName;
    }

    public function getWebPath()
    {
        return null === $this->fileName ? null : $this->getUploadDir().'/'.$this->fileName;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'uploads/documents';
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
        if (null === $this->getFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getFile()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->name = $this->getFile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Codes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set fileName
     *
     * @param string $fileName
     * @return Input
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    
        return $this;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set dateInsert
     *
     * @param \DateTime $dateInsert
     * @return Input
     */
    public function setDateInsert($dateInsert)
    {
        $this->dateInsert = $dateInsert;
    
        return $this;
    }

    /**
     * Get dateInsert
     *
     * @return \DateTime 
     */
    public function getDateInsert()
    {
        return $this->dateInsert;
    }

    /**
     * Set batch
     *
     * @param integer $batch
     * @return Input
     */
    public function setBatch($batch)
    {
        $this->batch = $batch;
    
        return $this;
    }

    /**
     * Get batch
     *
     * @return integer 
     */
    public function getBatch()
    {
        return $this->batch;
    }

    /**
     * Set dateProduction
     *
     * @param \DateTime $dateProduction
     * @return Input
     */
    public function setDateProduction($dateProduction)
    {
        $this->dateProduction = $dateProduction;
    
        return $this;
    }

    /**
     * Get dateProduction
     *
     * @return \DateTime 
     */
    public function getDateProduction()
    {
        return $this->dateProduction;
    }

    /**
     * Set dateExpiry
     *
     * @param \DateTime $dateExpiry
     * @return Input
     */
    public function setDateExpiry($dateExpiry)
    {
        $this->dateExpiry = $dateExpiry;
    
        return $this;
    }

    /**
     * Get dateExpiry
     *
     * @return \DateTime 
     */
    public function getDateExpiry()
    {
        return $this->dateExpiry;
    }

    /**
     * Add Codes
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Code $codes
     * @return Input
     */
    public function addCode(\HelloDi\DiDistributorsBundle\Entity\Code $codes)
    {
        $this->Codes[] = $codes;
    
        return $this;
    }

    /**
     * Remove Codes
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Code $codes
     */
    public function removeCode(\HelloDi\DiDistributorsBundle\Entity\Code $codes)
    {
        $this->Codes->removeElement($codes);
    }

    /**
     * Get Codes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCodes()
    {
        return $this->Codes;
    }

    /**
     * Set Item
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Item $item
     * @return Input
     */
    public function setItem(\HelloDi\DiDistributorsBundle\Entity\Item $item)
    {
        $this->Item = $item;
    
        return $this;
    }

    /**
     * Get Item
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Item 
     */
    public function getItem()
    {
        return $this->Item;
    }

    /**
     * Set Account
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\Account $account
     * @return Input
     */
    public function setAccount(\HelloDi\DiDistributorsBundle\Entity\Account $account)
    {
        $this->Account = $account;
    
        return $this;
    }

    /**
     * Get Account
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\Account 
     */
    public function getAccount()
    {
        return $this->Account;
    }

    /**
     * Set User
     *
     * @param \HelloDi\DiDistributorsBundle\Entity\User $user
     * @return Input
     */
    public function setUser(\HelloDi\DiDistributorsBundle\Entity\User $user)
    {
        $this->User = $user;
    
        return $this;
    }

    /**
     * Get User
     *
     * @return \HelloDi\DiDistributorsBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->User;
    }
}