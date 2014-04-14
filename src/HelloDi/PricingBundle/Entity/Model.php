<?php

namespace HelloDi\PricingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HelloDi\AccountingBundle\Entity\Account;

/**
 * Model
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Model
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=3, nullable=true, name="currency")
     */
    private $currency;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\AccountingBundle\Entity\Account", inversedBy="models")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true)
     */
    private $account;

    /**
     * @var String
     */
    private $json;

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
     * @return Model
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
     * Set currency
     *
     * @param string $currency
     * @return Model
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    
        return $this;
    }

    /**
     * Get currency
     *
     * @return string 
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set account
     *
     * @param Account $account
     * @return Model
     */
    public function setAccount(Account $account = null)
    {
        $this->account = $account;
    
        return $this;
    }

    /**
     * Get account
     *
     * @return \HelloDi\AccountingBundle\Entity\Account 
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set json
     *
     * @param string|array $json
     * @throws \Exception
     * @return Model
     */
    public function setJson($json)
    {
        if(is_array($json)) $json = json_encode($json);
        $this->json = $json;

        if($this->id)
        {
            if(!file_put_contents($this->getUploadRootDir().$this->id.".json",$this->json))
                throw new \Exception('unable to save model in file');
        }

        return $this;
    }

    /**
     * Get json
     *
     * @return string
     */
    public function getJson()
    {
        if($this->id && (!$this->json || $this->json==""))
            $this->json = file_get_contents($this->getUploadRootDir().$this->id.".json");

        return $this->json?$this->json:"[]";
    }

    /**
     * @return string
     */
    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/uploads/models/';
    }

    /**
     * @ORM\postPersist
     */
    public function createJsonFile()
    {
        if(!file_put_contents($this->getUploadRootDir().$this->id.".json",$this->json))
            throw new \Exception('unable to save model in file');
    }

    /**
     * @ORM\preRemove
     */
    public function removeJsonFile()
    {
        if(!unlink($this->getUploadRootDir().$this->id.".json"))
            throw new \Exception('unable to delete model file.');
    }
}