<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class BZDBAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The list of accepted BZFlag groups
     * @var string[]
     */
    protected $groups;

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * Create new BZDBAuthenticator
     * @param EntityManager   $entityManager The doctrine entity manager
     * @param string          $debug         Whether the kernel is on debug mode
     * @param string[]|string $groups        The accepted BZFlag groups
     */
    public function __construct(EntityManager $entityManager, $groups, $debug)
    {
        $this->entityManager = $entityManager;
        $this->groups = is_array($groups) ? $groups : array($groups);
        $this->debug = $debug;
    }

    public function createToken(Request $request, $providerKey)
    {
        $username = $request->query->get('username');
        $token = $request->query->get('token');

        if (!$username || !$token) {
            throw new AuthenticationException('No authentication token found');
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

        $bzData = \validate_token($token, $username, $this->groups, !$this->debug);

        if (!$bzData) {
            throw new BadCredentialsException(
                'The authentication token provided is invalid'
            );
        }

        if ($this->groups && !empty($this->groups)) {
            // BZFlag groups are case sensitive, so we don't need to make a case-insensitive check
            if (!isset($bzData['groups']) || !is_array($bzData['groups']) || empty(array_intersect($this->groups, $bzData['groups']))) {
                throw new AccessDeniedException(
                    'You are not allowed to access this area'
                );
            }
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

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof BadCredentialsException || $exception instanceof AccessDeniedException) {
            $request->getSession()
                ->getFlashbag()
                ->add('error', $exception->getMessage());
        }
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }
}
