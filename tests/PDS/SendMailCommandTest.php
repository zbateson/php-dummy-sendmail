<?php

use PDS\SendMailCommand;
use PDS\PDSApplication;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;
use PHPUnit\Framework\TestCase;

/**
 * Description of SendMailCommandTest
 *
 * @author Zaahid Bateson <zbateson@gmail.com>
 */
class SendMailCommandTest extends TestCase
{
    protected $application;
    protected $command;
    
    public function __construct()
    {
        parent::__construct();
        $application = new PDSApplication;
        $application->add(new SendMailCommand());
        $this->command = $application->find('sendmail');
    }
    
    public function testReadWrite()
    {
        $tester = new CommandTester($this->command);
        $tester->execute([
            '--directory' => dirname(__DIR__) . '/output',
            '--input-file' => '../data/email.txt'
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);

        $this->assertRegExp('/\[Debug\] outFile=.*/', $tester->getDisplay());
        preg_match('/\[Debug\] outFile=(.*)/', $tester->getDisplay(), $matches);

        $file = $matches[1];
        $this->assertFileEquals(dirname(__DIR__) . '/output/' . $file, dirname(__DIR__) . '/data/email.txt');
    }
    
    public function testPrint()
    {
        $tester = new CommandTester($this->command);
        
        // PHPUnit's expectOutputString doesn't apply cause we're not using echo it seems
        $tester->execute([
            '--directory' => dirname(__DIR__) . '/output',
            '--input-file' => '../data/email.txt',
            '--print' => true
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
        
        $this->assertContains('[Debug] printing to php://stdout', $tester->getDisplay());
        $this->assertContains('[Debug] outFile=php://stdout', $tester->getDisplay());
    }
    
    public function testFormat()
    {
        $tester = new CommandTester($this->command);
        $tester->execute([
            '--directory' => dirname(__DIR__) . '/output',
            '--input-file' => '../data/email.txt',
            '--timestamp' => 'YmdHisu',
            '--file-extension' => '.mime'
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
        
        $this->assertRegExp('/\[Debug\] outFile=\d{20}\.mime/', $tester->getDisplay());
        preg_match('/\[Debug\] outFile=(.*)/', $tester->getDisplay(), $matches);
    }
    
    public function testIncrement()
    {
        $tester = new CommandTester($this->command);
        $randIncr = 'incr-test-' . mt_rand();
        $incrFile = dirname(__DIR__) . '/output/randIncr';
        
        $next = 1;
        if (file_exists($incrFile)) {
            $next = file_get_contents($incrFile);
        } else {
            $next = intval($next);
        }
        
        for ($i = 0; $i < 5; ++$i) {
            $tester->execute([
                '--directory' => dirname(__DIR__) . '/output',
                '--input-file' => '../data/email.txt',
                '--increment-file' => $randIncr
            ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
            $this->assertContains("[Debug] outFile=$next.mime", $tester->getDisplay());
            $this->assertFileEquals(dirname(__DIR__) . "/output/$next.mime", dirname(__DIR__) . '/data/email.txt');
            ++$next;
        }
    }
    
    public function testWithNonExistentParameters()
    {
        $tester = new CommandTester($this->command);
        $tester->execute([
            '--directory' => dirname(__DIR__) . '/output',
            '--input-file' => '../data/email.txt',
            '-f' => 'test@example.com'
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
        
        $this->assertRegExp('/\[Debug\] outFile=.*/', $tester->getDisplay());
        preg_match('/\[Debug\] outFile=(.*)/', $tester->getDisplay(), $matches);
        
        $file = $matches[1];
        $this->assertFileEquals(dirname(__DIR__) . '/output/' . $file, dirname(__DIR__) . '/data/email.txt');
    }
}
