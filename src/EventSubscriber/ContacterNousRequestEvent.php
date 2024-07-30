<?php 

namespace App\EventSubscriber;

use App\DTO\ContactDTO;

class ContacterNousRequestEvent {
    
    public function __construct(public readonly ContactDTO $data)
    {
        
    }
}