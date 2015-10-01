<?php

/**
 * ownCloud - User Set Password
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2015 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Set_Password\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use OC\DB\Connection;

class uspReset extends Command
{
    const INFO = 1; // green text (from symfony doc)
    const COMMENT = 2; // yellow text
    const QUESTION = 3; // black text on a cyan background
    const ERROR = 4; // white text on a red background

    protected $output;

    protected function configure()
    {
        $this
            ->setName('user_set_password:reset')
            ->setDescription('Reset the flag to true for all accounts.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        // Cleaning
        $this->consoleDisplay('Resetting all flags to true');
        $this->reset($output);
        $this->consoleDisplay('End');
    }

    /**
     * Reset the flag to true for all accounts
     * @param OutputInterface $output
     */
    protected function reset($output)
    {
        try {
            \OCP\DB::beginTransaction();

            $sql = "UPDATE *PREFIX*preferences
                        SET configvalue = 1
                        WHERE appid = 'user_set_password' AND configkey = 'show'";
            $stmt = \OCP\DB::prepare($sql);
            $rowCount = $stmt->execute();

            \OCP\DB::commit();

            $this->consoleDisplay(/*$rowCount . */' flags have been resetted to "true".');
        }
        catch (\Exception $e) {
            // rollBack not implemented in \OCP\DB! (ownCloud 7.0.5)
            $conn = \OCP\DB::getConnection();
            $conn->rollBack();
            $this->consoleDisplay('Fatal error: ' . $e->getMessage(), self::ERROR);
        }
    }

    protected function consoleDisplay($msg = '', $type = self::INFO)
    {
        $now = date('Ymd_His');
        switch($type) {
            case self::COMMENT: {
                $this->output->writeln('<comment>' . $now . ' ' . $msg . '</comment>');
                break;
            }
            case self::QUESTION: {
                $this->output->writeln('<question>' . $now . ' ' . $msg . '</question>');
                break;
            }
            case self::ERROR: {
                $this->output->writeln('<error>' . $now . ' ' . $msg . '</error>');
                break;
            }
            default: {
                $this->output->writeln('<info>' . $now . ' ' . $msg . '</info>');
            }
        }
    }
}
