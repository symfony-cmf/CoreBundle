<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Doctrine\Phpcr;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;

/**
 * Metadata listener for when translations are disabled in PHPCR-ODM to remove
 * mapping information that makes fields being translated.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class NonTranslatableMetadataListener implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            'loadClassMetadata',
        ];
    }

    /**
     * Handle the load class metadata event: remove translated attribute from
     * fields and remove the locale mapping if present.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        /** @var ClassMetadata $meta */
        $meta = $eventArgs->getClassMetadata();

        if (!$meta->translator) {
            return;
        }

        foreach ($meta->translatableFields as $field) {
            unset($meta->mappings[$field]['translated']);
        }
        $meta->translatableFields = [];
        if (null !== $meta->localeMapping) {
            unset($meta->mappings[$meta->localeMapping]);
            $meta->localeMapping = null;
        }
        $meta->translator = null;
    }
}
