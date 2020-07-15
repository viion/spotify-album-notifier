<?php

namespace App\Command;

use App\Service\Commands\RecordArtists;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecordArtistsCommand extends Command
{
    protected static $defaultName = 'app:record-artists';
 
    private $recordArtists;
    
    public function __construct(RecordArtists $recordArtists, string $name = null)
    {
        parent::__construct($name);
        
        $this->recordArtists = $recordArtists;
    }
    
    protected function configure()
    {
    
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->recordArtists->collect();
        
        return Command::SUCCESS;
    }
}
