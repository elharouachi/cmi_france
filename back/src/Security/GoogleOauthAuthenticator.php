<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Security\UserBadge;
use League\OAuth2\Client\Provider\Google;
use Doctrine\ORM\EntityManagerInterface;
//...
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class GoogleOauthAuthenticator extends AbstractAuthenticator
{
    /**
     * @var \League\OAuth2\Client\Provider\Google
     */
    private $client;
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    public function __construct(Google $client, EntityManagerInterface $entityManager, UserProviderInterface $userProvider)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->userProvider = $userProvider;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('authorization');
    }

    public function getCredentials(Request $request): \League\OAuth2\Client\Token\AccessToken
    {
        $accessToken = explode(' ', $request->headers->get('authorization'))[1];

        return new \League\OAuth2\Client\Token\AccessToken(['access_token' => $accessToken]);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {

        return null;
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = explode(' ', $request->headers->get('authorization'))[1];
        if (null === $apiToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }
        $userBadge = new UserBadge($this->client,  $this->entityManager,  $this->userProvider, $apiToken);

        return new Passport($userBadge,
            new CustomCredentials(function($credentials, User $user) {
                return true;
            }, $userBadge->getUser()->getEmail()));


      //  return new SelfValidatingPassport(new UserBadge($this->client,  $this->entityManager,  $this->userProvider, $apiToken));
    }
}