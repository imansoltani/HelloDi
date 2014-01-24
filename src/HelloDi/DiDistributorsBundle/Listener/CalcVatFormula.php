<?php
namespace HelloDi\DiDistributorsBundle\Listener;

/**
 * Class CalcVatFormula
 * @package HelloDi\DiDistributorsBundle\Listener
 */
class CalcVatFormula {

    /**
     * @var float
     */
    private $VatFormula;

    /**
     * @param float $_VatFormula
     */
    public function __construct($_VatFormula)
    {
        $this->VatFormula = $_VatFormula;
    }

    /**
     * @param float $x
     * @param float $vat
     * @return float
     * @throws \Exception
     */
    public function Calc($x ,$vat)
    {
        if(!is_numeric($x) || !is_numeric($vat))
            throw new \Exception("Number or vat percent in not correct.");

        $formula = str_replace(array("x","vat"),array($x,$vat),$this->VatFormula);

        $result = null;
        try
        {
            eval("\$result= ".$formula.";");
        }
        catch(\Exception $e)
        {
            throw new \Exception("Error in calc vat: ".$e->getMessage());
        }

        return round($result,2);
    }
} 