<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccessDeniedException extends AuthenticationException
{
}
