<?php

namespace App\Command;

use App\Service\Commands\ScanArtists;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScanArtistsCommand extends Command
{
    protected static $defaultName = 'app:scan-artists';
 
    private $scanArtists;
    
    public function __construct(ScanArtists $scanArtists, string $name = null)
    {
        parent::__construct($name);
        
        $this->scanArtists = $scanArtists;
    }
    
    protected function configure()
    {
    
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->scanArtists->scan();
        
        return Command::SUCCESS;
    }
}
