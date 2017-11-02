<?php
/**
 * @copyright C UAB NFQ Technologies 2015
 *
 * This Software is the property of NFQ Technologies
 * and is protected by copyright law – it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * Contact UAB NFQ Technologies:
 * E-mail: info@nfq.lt
 * http://www.nfq.lt
 *
 */

/**
 * Class Vendor_Pdftk
 */
class Vendor_Pdftk extends Pdftk
{
    /**
     * {@inheritdoc}
     */
    public function __construct($tmp = TMPPATH)
    {
        parent::__construct($tmp);
    }

    /**
     * {@inheritdoc}
     */
    protected function log_error($message)
    {
        Kohana::$log->add(Kohana_Log::CRITICAL, $message);
    }

    /**
     * {@inheritdoc}
     */
    protected function log_info($message)
    {
        Kohana::$log->add(Kohana_Log::INFO, $message);
    }

    /**
     * {@inheritdoc}
     */
    protected function is_dev_env()
    {
        return KOHANA_ENV != ENV_DEVELOPMENT;
    }
}
