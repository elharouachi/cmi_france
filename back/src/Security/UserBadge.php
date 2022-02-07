<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use App\Entity\User;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Token\AccessToken;
use phpDocumentor\Reflection\Utils;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\EventListener\UserProviderListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

/**
 * Represents the user in the authentication process.
 *
 * It uses an identifier (e.g. email, or username) and
 * "user loader" to load the related User object.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class UserBadge extends \Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge implements BadgeInterface
{
    private $userIdentifier;
    private $userLoader;
    private $user;
    private $client;
    private $entityManager;
    private $userProvider;

    /**
     * Initializes the user badge.
     *
     * You must provide a $userIdentifier. This is a unique string representing the
     * user for this authentication (e.g. the email if authentication is done using
     * email + password; or a string combining email+company if authentication is done
     * based on email *and* company name). This string can be used for e.g. login throttling.
     *
     * Optionally, you may pass a user loader. This callable receives the $userIdentifier
     * as argument and must return a UserInterface object (otherwise an AuthenticationServiceException
     * is thrown). If this is not set, the default user provider will be used with
     * $userIdentifier as username.
     */
    public function __construct(Google $client, $entityManager, UserProviderInterface $userProvider, string $userIdentifier, callable $userLoader = null)
    {
        parent::__construct($userIdentifier);
        $this->userIdentifier = $userIdentifier;
        $this->userLoader = $userLoader;
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->userProvider = $userProvider;

    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }



    /**
     * @throws AuthenticationException when the user cannot be found
     */
    public function getUser(): UserInterface
    {
        /** @var \League\OAuth2\Client\Provider\GoogleUser $googleUser */
        $token = new AccessToken(['access_token' => $this->userIdentifier]); //$this->client->getAccessToken('authorization_code', ['code' => $this->userIdentifier]);

        $googleUser = $this->client->getResourceOwner($token);

        $email = $googleUser->getEmail();


        try {

            $user = $this->userProvider->loadUserByIdentifier($email);
        } catch (UserNotFoundException $exception) {
          //  echo 'ococococ'; exit;
            $user = (new User())->setEmail($email);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

        }
        $this->user = $user;
        return $this->user;
    }

    public function getUserLoader(): ?callable
    {
        return $this->userLoader;
    }

    public function setUserLoader(callable $userLoader): void
    {
        $this->userLoader = $userLoader;
    }

    public function isResolved(): bool
    {
        return true;
    }
}
