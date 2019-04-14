<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthAuthenticator extends AbstractGuardAuthenticator
{
    private $repository;
    private $linkedinProvider;

    public function __construct(LinkedinProvider $linkedinProvider, UserRepository $repository)
    {
        $this->linkedinProvider = $linkedinProvider;
        $this->repository = $repository;
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') != 'home';
    }

    public function getCredentials(Request $request)
    {
        if($request->headers->get('authorization')) {
            $bearer = str_replace("Bearer ", "", $request->headers->get('authorization'));

            return [
                'bearer' => $bearer
            ];
        }

        return ['code'=> $request->get('code')];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if(isset($credentials['bearer'])) {

            $user = $this->repository->findOneBy(['token' => $credentials['bearer']]);

            if (null == $user) {
                return new JsonResponse("Invalid credentials", Response::HTTP_FORBIDDEN);
            }

            return $user;
        }

        if(null == $credentials['code']) {
            return;
        }

        $token =  $this->linkedinProvider ->getAccessTokenFromAPI($credentials['code']);
        $user = $this->linkedinProvider->getUserFromAPI($token);

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse('You should be connect to access', Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            // you might translate this message
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
