<?php
namespace HelloDi\DiDistributorsBundle\Ogone;
use HelloDi\DiDistributorsBundle\Exception\OgoneException;
use Symfony\Component\HttpFoundation\Request;


class RoutesContainer {

    const RETAILER_RESULT_URL           = 'retailer_transactions_result';
    const RETAILER_CATALOG_URL          = 'retailer_transactions_new';
    const RETAILER_HOME_URL             = 'retailer_index';
    const RETAILER_VALIDATE_URL         = 'retailer_transactions_validate';

    const DISTRIBUTOR_RESULT_URL        = 'distributor_transactions_result';
    const DISTRIBUTOR_CATALOG_URL       = 'distributor_transactions_new';
    const DISTRIBUTOR_HOME_URL          = 'distributor_index';
    const DISTRIBUTOR_VALIDATE_URL      = 'distributor_transactions_validate';

    private $resultUrl;
    private $catalogUrl;
    private $homeUrl;
    private $validateUrl;

    public function __construct(Request $request)
    {
        $prefix = substr($request->getPathInfo(), 0, 7);

        if ($prefix === '/app/r/')
        {
            $this->resultUrl        = self::RETAILER_RESULT_URL;
            $this->catalogUrl       = self::RETAILER_CATALOG_URL;
            $this->homeUrl          = self::RETAILER_HOME_URL;
            $this->validateUrl      = self::RETAILER_VALIDATE_URL;
        }
        elseif($prefix === '/app/d/')
        {
            $this->resultUrl        = self::DISTRIBUTOR_RESULT_URL;
            $this->catalogUrl       = self::DISTRIBUTOR_CATALOG_URL;
            $this->homeUrl          = self::DISTRIBUTOR_HOME_URL;
            $this->validateUrl      = self::DISTRIBUTOR_VALIDATE_URL;
        }
        else
        {
            throw new OgoneException('Invalid prefix!');
        }
    }

    public function getResultUrl()
    {
        return $this->resultUrl;
    }

    public function getCatalogUrl()
    {
        return $this->catalogUrl;
    }

    public function getHomeUrl()
    {
        return $this->homeUrl;
    }


    public function getValidateUrl()
    {
        return $this->validateUrl;
    }

}