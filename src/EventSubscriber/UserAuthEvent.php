<?php 

    namespace App\EventSubscriber;

    class UserAuthEvent {

        public function __construct(public readonly array $user){
        }
    }