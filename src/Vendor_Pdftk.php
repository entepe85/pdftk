<?php

use Eleggua\Pdftk\Pdftk;

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

    /**
     * {@inheritdoc}
     */
    public static function get_instance($tmp = TMPPATH)
    {
        return new self($tmp);
    }
}
