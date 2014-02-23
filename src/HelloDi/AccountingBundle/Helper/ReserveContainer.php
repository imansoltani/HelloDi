<?php
namespace HelloDi\AccountingBundle\Helper;

use Doctrine\Common\Cache\ApcCache;
use HelloDi\AccountingBundle\Entity\Account;

/**
 * Class ReserveContainer
 * @package HelloDi\AccountingBundle\Helper
 */
class ReserveContainer
{
    /**
     * @var \Doctrine\Common\Cache\ApcCache
     */
    private $cache;

    /**
     *
     */
    public function __construct()
    {
        $this->cache = new ApcCache();

        $res_cache = $this->cache->fetch("reserves");
        if(!$res_cache || !is_array(unserialize($res_cache)))
        {
            $this->cache->save("reserves",serialize(array()));
        }
    }

    /**
     * @param Account $account
     * @return int|false
     */
    public function get(Account $account)
    {
        $reserves = unserialize($this->cache->fetch("reserves"));
        return array_key_exists($account->getId(),$reserves) ? $reserves[$account->getId()] : false;
    }

    /**
     * @param Account $account
     * @param $amount
     * @return bool
     */
    public function increase(Account $account, $amount)
    {
        $reserves = unserialize($this->cache->fetch("reserves"));
        if(array_key_exists($account->getId(),$reserves))
            $reserves[$account->getId()] += $amount;
        else
            $reserves[$account->getId()] = $amount;
        $this->cache->save("reserves",serialize($reserves));
        return true;
    }

    /**
     * @param Account $account
     * @param $amount
     * @return bool
     */
    public function decrease(Account $account, $amount)
    {
        $reserves = unserialize($this->cache->fetch("reserves"));
        if(array_key_exists($account->getId(),$reserves) && $reserves[$account->getId()] >= $amount)
        {
            $reserves[$account->getId()] -= $amount;
            $this->cache->save("reserves",serialize($reserves));
            return true;
        }
        return false;
    }
} 