<?php

namespace AppBundle;

use OAuth2\Storage\PublicKeyInterface;

class JwtStorage  implements  PublicKeyInterface
{    

    private $privKey;
    private $pubKey;
    
    public function __construct($privKey, $pubKey)
    {

        $this->privKey = file_get_contents($privKey);
        $this->pubKey = file_get_contents($pubKey);
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