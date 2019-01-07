<?php
namespace PDS;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines command line options and executes.
 *
 * @author Zaahid Bateson <zbateson@users.noreply.github.com>
 */
class SendMailCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('sendmail')
            ->setDescription('Dummy PHP-based sendmail stores the email in a configured folder')
            ->addArgument(
                'to',
                InputArgument::OPTIONAL,
                'Sender address (required by a real sendmail)'
            )
            ->addOption(
                'directory',
                null,
                InputOption::VALUE_OPTIONAL,
                'Base directory for reading from/writing to',
                '.'
            )
            ->addOption(
                'timestamp',
                null,
                InputOption::VALUE_OPTIONAL,
                'Uses a timestamp in the provided format (see PHP date for format)',
                'Y-m-d H-i-s-u'
            )
            ->addOption(
                'increment-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, stores a sequence number in the file specified by increment-file and uses it to name ' .
                    'the output files (overrides timestamp)',
                false
            )
            ->addOption(
                'input-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'Input file containing the email',
                'php://stdin'
            )
            ->addOption(
                'file-extension',
                null,
                InputOption::VALUE_OPTIONAL,
                'The file extension to use for output files',
                'mime'
            )
            ->addOption(
                'print',
                null,
                InputOption::VALUE_NONE,
                'If set, prints the email to the output stream instead of printing a file'
            )
            ->ignoreValidationErrors();
    }
    
    protected function getIncrementedFileName($file)
    {
        $next = 0;
        $fp = fopen($file, 'c+');
        if (flock($fp, LOCK_EX)) {
            $next = intval(fread($fp, 20));
            ++$next;
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $next);
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        return $next;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('to');
        $dir = rtrim($input->getOption('directory'), '/') . '/';

        // changing dir so input and output file names don't need to have a full
        // path (like increment file can be specified relatively)
        if (!empty($dir)) {
            if ($output->isVerbose()) {
                $output->writeln('Changing directory to ' . $dir);
            }
            if ($output->isDebug()) {
                $output->writeln('[Debug] chdir=' . $dir);
            }
            chdir($dir);
        }
        
        $file = '';
        if ($input->getOption('print')) {
            if ($output->isDebug()) {
                $output->writeln('[Debug] printing to php://stdout');
            }
            $file = 'php://stdout';
        } else {
            $incrFile = $input->getOption('increment-file');
            if (!empty($incrFile)) {
                if ($output->isDebug()) {
                    $output->writeln('[Debug] incrFile=' . $incrFile);
                }
                $file = $this->getIncrementedFileName($incrFile);
            } else {
                $ts = $input->getOption('timestamp');
                if ($output->isDebug()) {
                    $output->writeln('[Debug] timestamp=' . $ts);
                }
                list($usec, $sec) = explode(" ", microtime());
                $usec = substr($usec, 2, 6);
                $file = date(str_replace('u', $usec, $ts), $sec);
            }
            $ext = $input->getOption('file-extension');
            if (!empty($ext)) {
                if ($output->isDebug()) {
                    $output->writeln('[Debug] ext=' . $ext);
                }
                $file .= '.' . ltrim($ext, '.');
            }
        }

        $inFile = $input->getOption('input-file');
        if ($output->isVerbose()) {
            $output->writeln('Reading from ' . $inFile);
            $output->writeln('Writing output to ' . $file);
        }
        if ($output->isDebug()) {
            $output->writeln('[Debug] inFile=' . $inFile);
            $output->writeln('[Debug] outFile=' . $file);
        }
        
        $body = file_get_contents($inFile);
        file_put_contents($file, $body);
        
        if (!$input->getOption('print')) {
            @chmod($file, 0744);
        }
    }
}
