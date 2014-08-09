<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use HelloDi\AccountingBundle\Entity\Transaction;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="input")
 */
class Input
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, name="file_name")
     */
    protected $fileName;

    /**
     * @ORM\Column(type="date", nullable=false, name="date_insert")
     */
    protected $dateInsert;

    /**
     * @ORM\Column(type="integer", nullable=true, name="batch")
     */
    protected $batch;

    /**
     * @ORM\Column(type="date", nullable=false, name="date_production")
     */
    protected $dateProduction;

    /**
     * @ORM\Column(type="date", nullable=false, name="date_expiry")
     */
    protected $dateExpiry;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\Code", mappedBy="input")
     */
    protected $codes;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Item", inversedBy="inputs")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    protected $item;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Provider", inversedBy="inputs")
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id", nullable=false)
     */
    protected $provider;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\User", inversedBy="inputs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\OneToOne(targetEntity="HelloDi\AccountingBundle\Entity\Transaction")
     * @ORM\JoinColumn(name="prov_trans_id", referencedColumnName="id", nullable=false)
     */
    protected $providerTransaction;

    /**
     * @ORM\Column(type="integer", nullable=false, name="`count`")
     */
    protected $count;

    /**
     * @Assert\File(maxSize="6000000")
     */
    protected $file;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->codes = new ArrayCollection();
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
     * Add codes
     *
     * @param Code $codes
     * @return Input
     */
    public function addCode(Code $codes)
    {
        $this->codes[] = $codes;

        return $this;
    }

    /**
     * Remove codes
     *
     * @param Code $codes
     */
    public function removeCode(Code $codes)
    {
        $this->codes->removeElement($codes);
    }

    /**
     * Get codes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCodes()
    {
        return $this->codes;
    }

    /**
     * Set item
     *
     * @param Item $item
     * @return Input
     */
    public function setItem(Item $item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set provider
     *
     * @param Provider $provider
     * @return Input
     */
    public function setProvider(Provider $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Input
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

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

    /**
     * @return string|null
     */
    public function getAbsolutePath()
    {
        return null === $this->fileName ? null : $this->getUploadRootDir() . '/' . $this->fileName;
    }

    /**
     * @return string|null
     */
    public function getWebPath()
    {
        return null === $this->fileName ? null : $this->getUploadDir() . '/' . $this->fileName;
    }

    /**
     * @return string
     */
    protected function getUploadRootDir()
    {
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    /**
     * @return string
     */
    protected function getUploadDir()
    {
        return 'uploads/documents';
    }

    /**
     * upload file
     */
    public function upload()
    {
        if (null === $this->getFile())
            return;

        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getFile()->getClientOriginalName()
        );
        $this->fileName = $this->getFile()->getClientOriginalName();
        $this->file = null;
    }

    /**
     * Set providerTransaction
     *
     * @param Transaction $providerTransaction
     * @return Input
     */
    public function setProviderTransaction(Transaction $providerTransaction)
    {
        $this->providerTransaction = $providerTransaction;
    
        return $this;
    }

    /**
     * Get providerTransaction
     *
     * @return Transaction
     */
    public function getProviderTransaction()
    {
        return $this->providerTransaction;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return Input
     */
    public function setCount($count)
    {
        $this->count = $count;
    
        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }
}