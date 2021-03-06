<?php

namespace AppBundle\Controller;

use OAuth2\Server;
use OAuth2\Storage\JwtAccessToken;
use OAuth2\Storage\Memory;
use OAuth2\Storage\Pdo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TokenController extends Controller {

    /**
     * This is called by the client app once the client has obtained
     * an authorization code from the Authorize Controller (@see OAuth2\ServerBundle\Controller\AuthorizeController).
     * returns a JSON-encoded Access Token or a JSON object with
     * "error" and "error_description" properties.
     *
     * @Route("/ytoken", name="_ytoken")
     */
    public function tokenAction()
    {
        $server = $this->get('yuido.oauth2.server');

        return $server->handleTokenRequest($this->get('oauth2.request'), $this->get('oauth2.response'));
    }

    /**
     * @Route("/jwt_token", name="jwt_token")
     */
    public function jwtTokenAction(Request $request) {

        $server = $this->get('yuido.oauth2.server');

        $server->setConfig('use_jwt_access_tokens', TRUE);
        $server->setConfig('allow_implicit', true);

        return $server->handleTokenRequest($this->get('oauth2.request'), $this->get('oauth2.response'));
    }

    /**
     * Acción de muestra que simula el Resource Server
     * 
     * @Route("/api", name="api")
     */
    public function apiAction(\Symfony\Component\HttpFoundation\Request $request) {
        
        //Esta es la forma de verificar el token usando la librería
        /*
          $public_key = file_get_contents($this->container->getParameter('public_key'));

          // no private key necessary
          $keyStorage = new \OAuth2\Storage\Memory(array('keys' => array(
          'public_key' => $public_key,
          )));

          $server = new \OAuth2\Server($keyStorage, array(
          'use_jwt_access_tokens' => true,
          ));

          if (!$server->verifyResourceRequest(\OAuth2\Request::createFromGlobals())) {
          exit("Failed");
          }
          echo "Success!";


          exit;
         */

        // Esta es la forma de verificar el token a pelo

        $jwt_access_token = $request->get('access_token');

        $separator = '.';

        if (2 !== substr_count($jwt_access_token, $separator)) {
            throw new \Exception("Incorrect access token format");
        }

        list($header, $payload, $signature) = explode($separator, $jwt_access_token);

        echo $header . PHP_EOL;
        echo $payload . PHP_EOL;
        echo $signature . PHP_EOL;



        $decoded_signature = $this->urlSafeB64Decode($signature);


        // The header and payload are signed together
        $payload_to_verify = $header . $separator . $payload;

        // however you want to load your public key
        $public_key = file_get_contents($this->container->getParameter('public_key'));

        // default is SHA256
        //$verified = openssl_verify($payload_to_verify, $decoded_signature, $public_key, OPENSSL_ALGO_SHA256);
        $verified = openssl_verify($payload_to_verify, $decoded_signature, $public_key, defined('OPENSSL_ALGO_SHA256') ? OPENSSL_ALGO_SHA256 : 'sha256') === 1;

        echo "VERIFY:" . $verified . PHP_EOL;
        //if ($verified !== 1) {
        //throw new \Exception("Cannot verify signature");
        // }
        // output the JWT Access Token payload
        var_dump(base64_decode($payload));

        exit;
    }

    public function urlSafeB64Decode($b64) {
        $b64 = str_replace(array('-', '_'), array('+', '/'), $b64);

        return base64_decode($b64);
    }

}
