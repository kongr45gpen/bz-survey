<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class BZDBAuthenticator implements SimplePreAuthenticatorInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createToken(Request $request, $providerKey)
    {
        $username = $request->query->get('username');
        $token = $request->query->get('token');

        if (!$username || !$token) {
            throw new BadCredentialsException('No authentication token found');
        }

        return new PreAuthenticatedToken(
            'anon.',
            array($username, $token),
            $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $credentials = $token->getCredentials();
        $username = $credentials[0];
        $token = $credentials[1];

        // TODO: Check IP on production
        // TODO: Group support
        $bzData = \validate_token($token, $username, array(), false);

        if (!$bzData) {
            throw new AuthenticationException(
                'The authentication token provided is invalid'
            );
        }

        $bzid = $bzData['bzid'];
        $user = $this->entityManager
            ->getRepository('AppBundle:User')
            ->findOneByBzid($bzid);

        if (!$user) {
            $user = new User();
            $user->setBZID($bzid);
        }

        $user->setUsername($bzData['username']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // TODO: Custom token class
        return new PreAuthenticatedToken(
            $user,
            $username,
            $providerKey,
            $user->getRoles()
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }
}
