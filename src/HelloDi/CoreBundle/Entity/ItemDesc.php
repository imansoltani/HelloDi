<?php
namespace HelloDi\CoreBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints as Unique;

/**
 * @ORM\Entity
 * @ORM\Table(name="item_desc")
 * @Assert\Callback(methods={"isDescriptionValid"})
 * @Unique\UniqueEntity(fields={"language","item"}, message="language is duplicate.")
 */
class ItemDesc
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=false, name="description")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=2, nullable=false, name="language")
     */
    private $language;

    /**
     * @ORM\ManyToOne(targetEntity="HelloDi\CoreBundle\Entity\Item", inversedBy="descriptions")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    private $item;

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
     * Set description
     *
     * @param string $description
     * @return ItemDesc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return ItemDesc
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set item
     *
     * @param Item $item
     * @return ItemDesc
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

    public function isDescriptionValid(ExecutionContextInterface $context)
    {
        $tags = $this->getItem()->getType() == "imtu" ?
            array("tran_id", "receiver_number", "print_date") :
            array("pin", "serial", "expire", "duplicate", "print_date")
        ;

        foreach ($tags as $tag)
        {
            $find = strpos($this->getDescription(),"{{".$tag."}}");
            if(!$find)
                $context->addViolationAt('description', '%item%_not_exist', array('item'=>$tag));
        }

        $twig = new \Twig_Environment(new \Twig_Loader_String());
        try{
            if($this->getItem()->getType() == "imtu")
                $twig->render($this->getDescription(),array(
                        "print_date"=>"2013/13/13",
                        "entity_name"=>'Entity Name',
                        "operator"=>'Operator Name',
                        "entity_address1"=>'Address Line 1',
                        "entity_address2"=>'Address Line 2',
                        "entity_address3"=>'Address Line 3',
                        "tran_id"=>'1234',
                        "receiver_number"=>'+12345678',
                        "value_sent"=>'1 CHF',
                        "value_paid"=>'2 USD',
                    ));
            else
                $twig->render($this->getItem()->getType(),array(
                        "pin"=>1234,
                        "serial"=>4321,
                        "expire"=>"2012/12/12",
                        "print_date"=>"2013/13/13",
                        "duplicate"=>"duplicate",
                        "entity_name"=>'Entity Name',
                        "operator"=>'Operator Name',
                        "entity_address1"=>'Address Line 1',
                        "entity_address2"=>'Address Line 2',
                        "entity_address3"=>'Address Line 3'
                    ));
        }catch (\Exception $e){
            $context->addViolationAt('description', 'You_entered_an_invalid', array());
            return;
        }
        return;
    }
}