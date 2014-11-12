<?php
namespace PDS;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Created to allow for a single command without needing it to be specified
 * specifically.
 *
 * @author Zaahid Bateson <zbateson@users.noreply.github.com>
 */
class PDSApplication extends Application
{
    /**
     * Overridden to always returns 'sendmail' as the command name.
     *
     * @param InputInterface $input The input interface
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'sendmail';
    }

    /**
     * Appends SendMailCommand as a default command.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new SendMailCommand;
        return $defaultCommands;
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     * 
     * @return InputDefinition the definition object after clearing the first
     *      argument
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();
        return $inputDefinition;
    }
}
