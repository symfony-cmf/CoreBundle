<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * A placeholder service to provide instead of the normal publish workflow
 * checker in case the publish workflow is deactivated in the configuration.
 *
 * Services should never accept null as publish workflow checker for security
 * reasons. Typos or service renames could otherwise lead to severe security
 * issues.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class AlwaysPublishedWorkflowChecker implements AuthorizationCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return true;
    }
}
