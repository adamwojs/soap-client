<?php

namespace Phpro\SoapClient\Console\Command;

use Phpro\SoapClient\Exception\RunTimeException;
use Phpro\SoapClient\CodeGenerator\Generator\TypeGenerator;
use Phpro\SoapClient\Soap\SoapClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateTypesCommand
 *
 * @package Phpro\SoapClient\Console\Command
 */
class GenerateTypesCommand extends BaseGenerateCommand
{

    const COMMAND_NAME = 'generate:types';

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Generates types based on WSDL.')
            ->addArgument('destination', InputArgument::REQUIRED, 'Destination folder')
            ->addOption('wsdl', null, InputOption::VALUE_REQUIRED, 'The WSDL on which you base the types')
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

        $namespace = $input->getOption('namespace');
        $soapClient = new SoapClient($wsdl, []);
        $types = $soapClient->getSoapTypes();
        $functions = $soapClient->getSoapFunctions();

        $generator = new TypeGenerator($namespace);
        foreach ($types as $type => $properties) {
            // Check if file exists:
            $file = sprintf('%s/%s.php', $destination, ucfirst($type));
            $data = $generator->generate($type, $properties, $functions);

            // Existing files ...
            if ($this->filesystem->fileExists($file)) {
                $output->write(sprintf('Client class %s exists. Trying to patch ...', $type));
                $this->handleExistingFile($input, $output, $file, $type, $data);
                continue;
            }

            // New files...
            $this->filesystem->putFileContents($file, $data);
            $output->writeln(sprintf('Generated class %s to %s', $type, $file));
        }

        $output->writeln('Done');
    }
}
