<?php

namespace BitApps\Social\HTTP\Services\Social;

use BitApps\Social\HTTP\Services\Interfaces\SocialInterface;

class Social implements SocialInterface
{
    private $socialNetwork;

    public function __construct(SocialInterface $socialNetwork)
    {
        $this->socialNetwork = $socialNetwork;
    }

    public function publishPost($data)
    {
        $this->socialNetwork->publishPost($data);
    }
}
