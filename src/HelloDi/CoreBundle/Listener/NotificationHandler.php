<?php
namespace HelloDi\CoreBundle\Listener;

use Doctrine\ORM\EntityManager;
use HelloDi\CoreBundle\Entity\Notification;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class NotificationHandler
 * @package HelloDi\CoreBundle\Listener
 */
class NotificationHandler
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * @param EntityManager $em
     * @param SecurityContext $sc
     */
    public function __construct(EntityManager $em, SecurityContext $sc)
    {
        $this->em = $em;
        $this->securityContext = $sc;
    }

    /**
     * @param integer|null $id
     * @param integer $type
     * @param string|null $value
     * @return bool
     */
    public function newNotification($id, $type, $value = null)
    {

        if ($id != null)
            $account = $this->em->getRepository('HelloDiAccountingBundle:Account')->find($id);
        else
            $account = null;

        $exist = $this->em->getRepository('HelloDiCoreBundle:Notification')->findBy(array(
            'account' => $account,
            'type' => $type,
            'value' => $value
        ));

        if (count($exist) == 0) {
            $Not = new Notification();
            if ($id != null)
                $Not->setAccount($this->em->getRepository('HelloDiAccountingBundle:Account')->find($id));
            $Not->setDate(new \DateTime('now'));
            $Not->setType($type);
            if ($value != null)
                $Not->setValue($value);

            $this->em->persist($Not);
            $this->em->flush();
        }

        return true;
    }

    /**
     * @param $id
     * @return bool
     */
    public function readNotification($id)
    {
        $notification = $this->em->getRepository('HelloDiCoreBundle:Notification')->find($id);
        $this->em->remove($notification);
        $this->em->flush();

        return true;
    }


    /**
     * @return integer
     */
    public function countNotification()
    {
        $Account = $this->securityContext->getToken()->getUser()->getAccount();

        $Notification = $this->em->getRepository('HelloDiCoreBundle:Notification')->findBy(array('account' => $Account));

        return count($Notification);
    }
}