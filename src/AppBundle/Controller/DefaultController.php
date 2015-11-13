<?php

namespace AppBundle\Controller;

use OAuth2\Storage\JwtAccessToken;
use OAuth2\Storage\Memory;
use OAuth2\Storage\Pdo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {

    /**
     * @Route("/jwt_authorize", name="_jwt_authorize_validate")
     * @Method({"GET"})
     * @Template("OAuth2ServerBundle:Authorize:authorize.html.twig")
     */
    public function validateAuthorizeAction()
    {
        echo "HOLA";
        $server = $this->get('oauth2.server');

        if (!$server->validateAuthorizeRequest($this->get('oauth2.request'), $this->get('oauth2.response'))) {
            return $server->getResponse();
        }

        // Get descriptions for scopes if available
        $scopes = array();
        $scopeStorage = $this->get('oauth2.storage.scope');
        foreach (explode(' ', $this->get('oauth2.request')->query->get('scope')) as $scope) {
            $scopes[] = $scopeStorage->getDescriptionForScope($scope);
        }

        return array('request' => $this->get('oauth2.request')->query->all(), 'scopes' => $scopes);
    }

    /**
     * @Route("/jwt_authorize", name="_jwt_authorize_handle")
     * @Method({"POST"})
     */
    public function handleAuthorizeAction()
    {
        $server = $this->get('oauth2.server');

        return $server->handleAuthorizeRequest($this->get('oauth2.request'), $this->get('oauth2.response'), true);
    }

    /**
     * @Route("/jwt_token", name="jwt_token")
     */
    public function jwtTokenAction(Request $request) {

        $privateKey = file_get_contents($this->container->getParameter('private_key'));
        $publicKey = file_get_contents($this->container->getParameter('public_key'));

        $server = $this->get('oauth2.server');

        $server->addGrantType($this->get('oauth2.grant_type.client_credentials'));
        $server->addGrantType($this->get('oauth2.grant_type.authorization_code'));
        $server->addGrantType($this->get('oauth2.grant_type.refresh_token'));
        $server->addGrantType($this->get('oauth2.grant_type.user_credentials'));

        $clientId = $request->get('client_id');
        $clientSecret = $request->get('client_secret');
        $username = $request->get('username');
        $password = $request->get('password');

//        $storage = new \AppBundle\Storage\JwtStorage($privateKey, $publicKey);

        $storage = new Memory(array(
            'keys' => array(
                'public_key'  => $publicKey,
                'private_key' => $privateKey,
            ),
            // add a Client ID for testing
            'client_credentials' => array(
                $clientId => array('client_secret' => $clientSecret)
            ),
            'user_credentials' => array(
                'username' => $username,
                'password' => $password
            ),
            'supported_scopes' => array('publica', 'privada')

        ));


        $server->addStorage($storage);


        $server->setConfig('use_jwt_access_tokens', TRUE);

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
