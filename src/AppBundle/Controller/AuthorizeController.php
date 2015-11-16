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
     * @Route("/jwt_authorize", name="_jwt_authorize_validate")
     * @Method({"GET"})
     * @Template("OAuth2ServerBundle:Authorize:authorize.html.twig")
     */
    public function validateAuthorizeAction()
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
    public function handleAuthorizeAction(Request $request)
    {
        $server = $this->get('yuido.oauth2.server');

        $server->setConfig('allow_implicit', true);
        $server->setConfig('use_jwt_access_tokens', TRUE);

        return $server->handleAuthorizeRequest($this->get('oauth2.request'), $this->get('oauth2.response'), true);
    }
}
