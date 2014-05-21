<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bridge\Doctrine\Validator\Constraints as Unique;

/**
 * @ORM\Entity
 * @ORM\Table(name="operator")
 * @ORM\HasLifecycleCallbacks()
 * @Unique\UniqueEntity(fields="carrierCode", message="This_operator_carrier_code_already_exist")
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
     * @ORM\Column(type="string", length=45, nullable=true, name="logo_extension")
     */
    private $logoExtension;

    /**
     * @ORM\OneToMany(targetEntity="HelloDi\CoreBundle\Entity\Item", mappedBy="operator")
     */
    private $item;

    /**
     * @var UploadedFile
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
     * Set logoExtension
     *
     * @param string $logoExtension
     * @return Operator
     */
    public function setLogoExtension($logoExtension)
    {
        $this->logoExtension = $logoExtension;

        return $this;
    }

    /**
     * Get logoExtension
     *
     * @return string
     */
    public function getLogoExtension()
    {
        return $this->logoExtension;
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

        if (null !== $this->file) {
            $this->logoExtension .= "?";
        }
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
        return null === rtrim($this->logoExtension,'?') ? null : $this->getUploadRootDir() . '/' . $this->id."." . rtrim($this->logoExtension,'?');
    }

    /**
     * @return string
     */
    public function getWebPath()
    {
        return $this->getUploadDir() . '/' . (null === $this->logoExtension ? "0.png" : $this->id.".".$this->logoExtension);
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
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function preUpload()
    {
        if ($this->file === null)
            return;

        if ($this->id && file_exists($this->getAbsolutePath()))
            unlink($this->getAbsolutePath());

        $this->logoExtension = $this->file->getClientOriginalExtension();
    }

    /**
     * @ORM\PostPersist
     * @ORM\PostUpdate
     */
    public function upload()
    {
        if ($this->file === null)
            return;

        $this->file->move(
            $this->getUploadRootDir(),
            $this->id . '.' . $this->file->getClientOriginalExtension()
        );

        $this->file = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeFile()
    {
        if (file_exists($this->getAbsolutePath())) {
            unlink($this->getAbsolutePath());
        }
    }

    /**
     * @return string
     */
    public function getNameCarrier()
    {
        return $this->name . ($this->carrierCode != null ? ' (' . $this->carrierCode . ')' : '');
    }
}