<?php

namespace Phpro\SoapClient\Console\Command;

use Phpro\SoapClient\CodeGenerator\Generator\ClientGenerator;
use Phpro\SoapClient\Soap\SoapClient;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * GenerateClientCommand.
 *
 * @author Adam Wójs <adam@wojs.pl>
 */
class GenerateClientCommand extends BaseGenerateCommand
{
    const COMMAND_NAME = 'generate:client';
    
    /**
     * Configure the command.
     */    
    protected function configure() 
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Generates client class based on WSDL.')
            ->addArgument('destination', InputArgument::REQUIRED, 'Destination folder')
            ->addOption('wsdl', null, InputOption::VALUE_REQUIRED, 'The WSDL on which you base the client')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Client class name', 'Client')
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Resulting namespace')
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'Makes it possible to overwrite by default')
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $destination = rtrim($input->getArgument('destination'), '/\\');
        if (!$this->filesystem->dirextoryExists($destination)) {
            throw new RunTimeException(sprintf('The destination %s does not exist.', $destination));
        }

        $wsdl = $input->getOption('wsdl');
        if (!$wsdl) {
            throw new RuntimeException('You MUST specify a WSDL endpoint.');
        }

        $name = $input->getOption('name');
        $namespace = $input->getOption('namespace');
        $soapClient = new SoapClient($wsdl, []);
        
        $generator = new ClientGenerator($name, $namespace);
        $data = $generator->generate($soapClient->getSoapFunctions());

        // Check if file exists:
        $file = sprintf('%s/%s.php', $destination, $name);
        if (!$this->filesystem->fileExists($file)) {
            // New files...
            $this->filesystem->putFileContents($file, $data);
            $output->writeln(sprintf('Generated class %s to %s', $name, $file));
        }
        else {
            // Existing files ...
            $output->write(sprintf('Client class %s exists. Trying to patch ...', $name));
            $this->handleExistingFile($input, $output, $file, $name, $data);            
        }

        $output->writeln('Done');        
    }    
    
}
