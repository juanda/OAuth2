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
     * @Route("/prueba", name="prueba")
     */
    public function pruebaAction(){
        echo "HOLA";exit;
    }

    /**
     * @Route("/jwt_authorize", name="_jwt_authorize_validate")
     * @Method({"GET"})
     * @Template(":Authorize:authorize.html.twig")
     */
    public function validateJWTAuthorizeAction()
    {
        $server = $this->get('yuido.oauth2.server');
        $server->setConfig('allow_implicit', true);
        $server->setConfig('use_jwt_access_tokens', TRUE);

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
    public function handleJWTAuthorizeAction(Request $request)
    {
        $server = $this->get('yuido.oauth2.server');

        $server->setConfig('allow_implicit', true);
        $server->setConfig('use_jwt_access_tokens', TRUE);

        $username = $this->getUser()->getUsername();

        return $server->handleAuthorizeRequest($this->get('oauth2.request'), $this->get('oauth2.response'), true, $username);
    }

    /**
     * @Route("/authorize", name="_authorize_validate")
     * @Method({"GET"})
     * @Template("Authorize/authorize.html.twig")
     */
    public function validateAuthorizeAction()
    {
        $server = $this->get('oauth2.server');
        $server->setConfig('allow_implicit', true);

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
     * @Route("/authorize", name="_authorize_handle")
     * @Method({"POST"})
     */
    public function handleAuthorizeAction()
    {
        $server = $this->get('oauth2.server');
        $server->setConfig('allow_implicit', true);

        $username = $this->getUser()->getUsername();

        return $server->handleAuthorizeRequest($this->get('oauth2.request'), $this->get('oauth2.response'), true, $username);
    }
}
