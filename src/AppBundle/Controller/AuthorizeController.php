<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class AuthorizeController extends Controller
{
    /**
     * @Route("/yauthorize", name="_yauthorize_validate")
     * @Method({"GET"})
     * @Template("Authorize/authorize.html.twig")
     */
    public function validateAuthorizeAction()
    {
        $server = $this->getServer();

        return $this->getResponse($server);
    }

    /**
     * @Route("/yauthorize", name="_yauthorize_handle")
     * @Method({"POST"})
     */
    public function handleAuthorizeAction()
    {
        $server = $this->getServer();

        $username = $this->getUser()->getUsername();

        return $server->handleAuthorizeRequest($this->get('oauth2.request'), $this->get('oauth2.response'), true, $username);
    }

    /**
     * @Route("/jwt_authorize", name="_jwt_authorize_validate")
     * @Method({"GET"})
     * @Template("Authorize/authorize.html.twig")
     */
    public function validateJWTAuthorizeAction()
    {
        $server = $this->getServer();
        $server->setConfig('use_jwt_access_tokens', TRUE);

        return $this->getResponse($server);
    }

    /**
     * @Route("/jwt_authorize", name="_jwt_authorize_handle")
     * @Method({"POST"})
     */
    public function handleJWTAuthorizeAction(Request $request)
    {
        $server = $this->getServer();
        $server->setConfig('use_jwt_access_tokens', TRUE);

        $username = $this->getUser()->getUsername();

        return $server->handleAuthorizeRequest($this->get('oauth2.request'), $this->get('oauth2.response'), true, $username);
    }

    protected function getServer(){
        $server = $this->get('yuido.oauth2.server');
        $server->setConfig('allow_implicit', true);

        return $server;
    }

    protected function getResponse($server){
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
}
