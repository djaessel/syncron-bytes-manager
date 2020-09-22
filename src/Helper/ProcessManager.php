<?php

namespace App\Helper;

use Throwable;

/**
 * Class ProcessManager
 * @package App\Helper
 */
class ProcessManager
{
    /**
     * @param string cmd
     * @param string $logFile
     * @return int
     */
    public function runCommand($cmd, $logFile)
    {
      $pid = shell_exec(sprintf("%s > %s 2>&1 & echo $! &", $cmd, $logFile));
      return $pid;
    }

    /**
     * @param int $pid
     * @return bool
     */
    public function isRunning($pid)
    {
      $running = false;

      try
      {
          $result = shell_exec(sprintf("ps %d", $pid));
          $processCount = count(preg_split("/\n/", $result));

          if ($processCount > 2)
          {
              $running = true;
          }
      } catch(Throwable $e) {
        // FIXME: add error message to log file
      }

      return $running;
    }
}
