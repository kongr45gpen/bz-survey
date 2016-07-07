<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

class BZDBAuthenticator extends AbstractFormLoginAuthenticator
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Router
     */
    protected $router;

    /**
     * The list of accepted BZFlag groups.
     *
     * @var string[]
     */
    protected $groups;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * Create new BZDBAuthenticator.
     *
     * @param EntityManager   $entityManager The doctrine entity manager
     * @param Router          $router        The Symfony router
     * @param string          $debug         Whether the kernel is on debug mode
     * @param string[]|string $groups        The accepted BZFlag groups
     */
    public function __construct(EntityManager $entityManager, Router $router, $groups, $debug)
    {
        $this->entityManager = $entityManager;
        $this->router        = $router;
        $this->groups        = is_array($groups) ? $groups : array($groups);
        $this->debug         = $debug;
    }

    /**
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     */
    public function getCredentials(Request $request)
    {
        $username = $request->query->get('username');
        $token    = $request->query->get('token');

        if (!$username || !$token) {
            return null;
        }

        return [
            'username' => $username,
            'token' => $token
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $bzData = \validate_token($credentials['token'], $credentials['username'], $this->groups, !$this->debug);

        if (!$bzData) {
            throw new BadCredentialsException("The authentication token provided is invalid");
        }

        if ($this->groups && !empty($this->groups)) {
            // BZFlag groups are case sensitive, so we don't need to make a case-insensitive check
            if (!isset($bzData['groups']) || !is_array($bzData['groups']) || empty(array_intersect($this->groups, $bzData['groups']))) {
                throw new AccessDeniedException(
                    'You are not allowed to access this area'
                );
            }
        }

        // if null, authentication will fail
        // if a User object, checkCredentials() is called
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

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof BadCredentialsException || $exception instanceof AccessDeniedException) {
            $request->getSession()
                ->getFlashbag()
                ->add('error', $exception->getMessage());
        }
    }

    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Return the URL to the login page.
     *
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('login_route');
    }

    /**
     * The user will be redirected to the secure page they originally tried
     * to access. But if no such page exists (i.e. the user went to the
     * login page directly), this returns the URL the user should be redirected
     * to after logging in successfully (e.g. your homepage).
     *
     * @return string
     */
    protected function getDefaultSuccessRedirectUrl()
    {
        return $this->router->generate('survey');
    }


}
