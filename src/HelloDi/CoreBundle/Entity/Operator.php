<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\Column(type="string", length=45, nullable=false, name="name")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="carrier_code", unique = true)
     */
    private $carrierCode;

    /**
     * @ORM\Column(type="string", length=45, nullable=True, name="logo")
     */
    private $logo;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\Item", mappedBy="operator")
     */
    private $item;

    /**
     * @Assert\File(maxSize="6000000")
     */
    private $file;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->item = new ArrayCollection();
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

    /**
     * Set logo
     *
     * @param string $logo
     * @return Operator
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Add item
     *
     * @param Item $item
     * @return Operator
     */
    public function addItem(Item $item)
    {
        $this->item[] = $item;

        return $this;
    }

    /**
     * Remove item
     *
     * @param Item $item
     */
    public function removeItem(Item $item)
    {
        $this->item->removeElement($item);
    }

    /**
     * Get item
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItem()
    {
        return $this->item;
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
        return null === $this->logo ? null : $this->getUploadRootDir() . '/' . $this->logo;
    }

    /**
     * @return string
     */
    public function getWebPath()
    {
        return $this->getUploadDir() . '/' . (null === $this->logo ? "0.png" : $this->logo);
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
        return 'uploads/logos';
    }

    /**
     * Upload
     */
    public function upload()
    {
        if ($this->file === null)
            return;

        if (file_exists($this->getAbsolutePath()))
            unlink($this->getAbsolutePath());

        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->id . '.' . $this->getFile()->getClientOriginalExtension()
        );

        $this->logo = $this->id . '.' . $this->getFile()->getClientOriginalExtension();

        $this->file = null;
    }

    /**
     * @return string
     */
    public function getNameCarrier()
    {
        return $this->name . ($this->carrierCode != null ? ' (' . $this->carrierCode . ')' : '');
    }
}