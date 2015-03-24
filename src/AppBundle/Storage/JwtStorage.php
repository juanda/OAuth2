<?php

namespace AppBundle\Storage;

use OAuth2\Storage\PublicKeyInterface;

class JwtStorage implements  PublicKeyInterface
{    

    private $privKey;
    private $pubKey;
    
    public function __construct($privKey, $pubKey)
    {
        $this->privKey = $privKey;
        $this->pubKey = $pubKey;        
    }

    
    public function getEncryptionAlgorithm($client_id = null) {
        return 'RS256';
    }

    public function getPrivateKey($client_id = null) {
                
        return $this->privKey;
    }

    public function getPublicKey($client_id = null) {        
        return $this->pubKey;
    }
}