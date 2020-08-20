<?php

namespace Eleggua\Pdftk;

/**
 * Class Pdftk
 */
class Pdftk
{
    const FILENAME = 'pdftk-1.44';

    /**
     * @var array
     */
    protected $files;

    /**
     * @var array
     */
    protected $tmp;

    /**
     * Pdftk constructor.
     *
     * @param string $tmp
     * @throws \Exception
     */
    public function __construct($tmp)
    {


        if (empty($tmp)) {
            throw new \Exception('Missed tmp configuration option');
        }

        if (!is_readable($tmp)) {
            throw new \Exception("'{$tmp} is not readable");
        }

        $tmp = rtrim($tmp, "\\/") . DIRECTORY_SEPARATOR;

        $this->tmp = $tmp;
    }

    /**
     * Add an array of file to PDFTK
     *
     * @param array $files
     * @param bool $append
     */
    public function add_files($files, $append = false)
    {
        foreach ($files as $index => $file) {
            $this->add_file($append?null:$index, $file);
        }
    }

    /**
     * @param string $index
     * @param string $filename
     * @return Pdftk
     */
    public function add_file($index, $filename)
    {
        if (!is_null($index)) {
            $this->files[$index] = $filename;
        } else {
            $this->files[] = $filename;
        }
        return $this;
    }

    /**
     * @param string $dir
     * @param string $out_file
     * @return bool|string
     * @throws \Exception
     */
    public function merge($dir = '', $out_file = '')
    {
        $current_dir = null;

        try {
            if (empty($dir) && count($this->files) == 0) {
                throw new \Exception('Could not merge files because no file had been added');
            }

            if (empty($dir)) {
                $operation = sprintf('"%s" cat', implode('" "', $this->files));
            } else {
                $current_dir = getcwd();
                if (!chdir($dir)) {
                    throw new \Exception(
                        'Failed to change working directory from '
                        . $current_dir
                        . ' to '
                        . $dir
                        .  ' while trying to merge '
                        . count($this->files)
                        . ' files'
                    );
                }
                $operation = '*.pdf cat';
            }

            $result = $this->run_operation($operation, $out_file);
        } catch (\Exception $oEx) {
            $this->log_error('[PDFTK] PDF merge critical error: '.$oEx->getMessage());

            $result = false;
        }

        //revert working directory
        if (!is_null($current_dir)) {
            chdir($current_dir);
        }

        return $result;
    }


    /**
     * @param string $index
     * @return bool
     */
    public function rotate_left_90($index)
    {
        try {
            if (!isset($this->files[$index])) {
                throw new \Exception("Could not rotate file because file index '{$index}' does not exist");
            }

            $operation = sprintf('"%s" cat %s', $this->files[$index], '1-endW');
            $new_filename = $this->run_operation($operation);
            unlink($this->files[$index]);
            $status = rename($new_filename, $this->files[$index]);
            if (!$status) {
                throw new \Exception("Failed to created a PDf file that was rotated to left by 90");
            }

            return $this->files[$index];
        } catch (\Exception $oEx) {
            $this->log_error('[PDFTK] PDF rotate critical error: '.$oEx->getMessage());
        }

        return false;
    }

    /**
     * @param string $operation
     * @param string $out_filename
     * @return string
     * @throws \Exception
     */
    public function run_operation($operation, $out_filename = '')
    {
        if (empty($out_filename)) {
            $out_filename = $this->generate_out_filename();
        }

        $bin = $this->get_binary_path();

        if (!is_executable($bin)) {
            throw new \Exception("'{$bin} is not executable");
        }

        $command = sprintf('%s %s output "%s"', $bin, $operation, $out_filename);
        //Escape only in *nix and not in development env
        if (PHP_SHLIB_SUFFIX == 'so' && $this->is_dev_env()) {
            $command = escapeshellcmd($command);
        }

        //Fix for *.pdf
        $command = str_replace('\*.pdf', '*.pdf', $command);
        $this->log_info(strtr("[PDFTK] run_operation command: :cmd", array(':cmd' => $command)));

        $op_result = exec($command, $output, $status);

        if ($status !== 0) {
            $this->log_error(
                "[PDFTK] run_operation critical error: OP_result: {$op_result}, output" . print_r($output, true)
            );
            throw new \Exception("Failed to run command '{$command}', status {$status}");
        }
        
        return $out_filename;
    }


    /**
     * @return string
     */
    protected function generate_out_filename()
    {
        return $this->tmp . md5(microtime() . mt_rand()) . '.pdf';
    }

    /**
     * @param string $message
     */
    protected function log_error($message)
    {
        error_log($message, 0);
    }

    /**
     * @param string $message
     */
    protected function log_info($message)
    {
        error_log($message, 0);
    }

    /**
     * @param string $tmp
     * @return Pdftk
     */
    public static function get_instance($tmp)
    {
        return new self($tmp);
    }

    /**
     * @return bool
     */
    protected function is_dev_env()
    {
        return true;
    }

    /**
     * @return string
     */
    protected function get_binary_path()
    {
        return dirname(__FILE__) . '/../../bin/' . self::FILENAME;
    }
}
