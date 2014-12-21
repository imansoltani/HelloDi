<?php

namespace HelloDi\AggregatorBundle\Helper;

/**
 * Class SoapClientTimeout
 * @package HelloDi\AggregatorBundle\Helper
 */
class SoapClientTimeout extends \SoapClient
{
    /**
     * @var int
     */
    private $timeout;

    /**
     * @param int $timeout
     * @throws \Exception
     */
    public function __setTimeout($timeout)
    {
        if (!is_int($timeout) && !is_null($timeout))
        {
            throw new \Exception("Invalid timeout value");
        }

        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc }
     */
    public function __soapCall($function_name, $arguments, $options = NULL, $input_headers = NULL, &$output_headers = NULL)
    {
        return parent::__soapCall($function_name, array($arguments), $options, $input_headers, $output_headers);
    }

    /**
     * {@inheritdoc }
     */
    public function __doRequest($request, $location, $action, $version, $one_way = FALSE)
    {
        if (!$this->timeout)
        {
            // Call via parent because we require no timeout
            $response = parent::__doRequest($request, $location, $action, $version, $one_way);
        }
        else
        {
            // Call via Curl and use the timeout
            $curl = curl_init($location);

            curl_setopt($curl, CURLOPT_VERBOSE, FALSE);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            curl_setopt($curl, CURLOPT_HEADER, FALSE);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

            $response = curl_exec($curl);

            if (curl_errno($curl))
            {
                throw new \Exception("timeout",-99);
            }

            curl_close($curl);
        }

        // Return?
        if (!$one_way)
        {
            return ($response);
        }
    }
}