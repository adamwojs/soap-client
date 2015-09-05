<?php

namespace Phpro\SoapClient\Console\Command;

use Phpro\SoapClient\CodeGenerator\Patcher;
use Phpro\SoapClient\Exception\PatchException;
use Phpro\SoapClient\Util\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * BaseGenerateCommand.
 *
 * @author Adam Wójs <adam@wojs.pl>
 */
class BaseGenerateCommand extends Command {

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem = null)
    {
        parent::__construct(null);
        $this->filesystem = $filesystem ?: new Filesystem();
    }    
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $file
     * @param string          $name
     * @param string          $newContent
     */
    protected function handleExistingFile(InputInterface $input, OutputInterface $output, $file, $name, $newContent)
    {
        // Patch the file
        $patched = $this->patchExistingFile($output, $file, $newContent);
        if ($patched) {
            $output->writeln('Patched!');
            return;
        }
        $output->writeln('Could not patch.');

        // Ask for overwriting the file:
        $allowOverwrite = $this->askForOverwrite($input, $output, $newContent);
        if (!$allowOverwrite) {
            $output->writeln(sprintf('Skipping %s', $name));
            return;
        }

        // Overwrite
        $this->filesystem->putFileContents($file, $newContent);
    }    
    
   /**
     * @param OutputInterface $output
     * @param                 $file
     * @param                 $newContent
     *
     * @return bool
     */
    protected function patchExistingFile(OutputInterface $output, $file, $newContent)
    {
        $patcher = new Patcher($this->filesystem);
        try {
            $patcher->patch($file, $newContent);
        } catch (PatchException $e) {
            $output->writeln('<fg=red>' . $e->getMessage() . '</fg=red>');
            return false;
        }

        return true;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function askForOverwrite(InputInterface $input, OutputInterface $output)
    {
        $overwriteByDefault = $input->getOption('overwrite');
        $question = new ConfirmationQuestion('Do you want to overwrite it?', $overwriteByDefault);
        return $this->getHelper('question')->ask($input, $output, $question);
    }    
}
