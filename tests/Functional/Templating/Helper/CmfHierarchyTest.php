<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Functional\Templating\Helper;

use Doctrine\ODM\PHPCR\Document\Generic;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Cmf\Bundle\CoreBundle\Templating\Helper\Cmf;
use Symfony\Cmf\Bundle\CoreBundle\Tests\Fixtures\App\DataFixture\LoadHierarchyRouteData;
use Symfony\Cmf\Bundle\CoreBundle\Tests\Fixtures\App\Document\RouteAware;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CmfHierarchyTest extends BaseTestCase
{
    private MockObject&AuthorizationCheckerInterface $publishWorkflowChecker;
    private Cmf $helper;

    public function setUp(): void
    {
        $dbManager = $this->db('PHPCR');
        $dbManager->loadFixtures([LoadHierarchyRouteData::class]);

        $this->publishWorkflowChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->publishWorkflowChecker
            ->method('isGranted')
            ->willReturn(true)
        ;

        $this->helper = new Cmf($this->publishWorkflowChecker);
        $this->helper->setDoctrineRegistry($dbManager->getRegistry(), 'default');
    }

    public function testGetDescendants(): void
    {
        $this->assertEquals([], $this->helper->getDescendants(null));

        $expected = ['/a/b', '/a/b/c', '/a/b/d', '/a/b/e', '/a/f', '/a/f/g', '/a/f/g/h', '/a/i'];
        $this->assertEquals($expected, $this->helper->getDescendants('/a'));

        $expected = ['/a/b', '/a/f', '/a/i'];
        $this->assertEquals($expected, $this->helper->getDescendants('/a', 1));
    }

    /**
     * @dataProvider getPrevData
     */
    public function testGetPrev(?string $expected, ?string $path, ?string $anchor = null, ?int $depth = null, string $class = Generic::class): void
    {
        $prev = $this->helper->getPrev($path, $anchor, $depth);
        if (null === $expected) {
            $this->assertNull($prev);
        } else {
            $this->assertInstanceOf($class, $prev);
            $this->assertEquals($expected, $prev->getId());
        }
    }

    public static function getPrevData(): array
    {
        return [
            [null, null],
            [null, '/a'],
            [null, '/a/b'],
            [null, '/a/b/c'],
            ['/a/b/c', '/a/b/d', null, null, RouteAware::class],
            ['/a/b/d', '/a/b/e'],
            ['/a/b', '/a/f'],
            [null, '/a/f/g'],
            [null, '/a/f/g/h'],
            [null, '/a', '/a'],
            ['/a', '/a/b', '/a'],
            ['/a/b', '/a/b/c', '/a'],
            ['/a/b/c', '/a/b/d', '/a', null, RouteAware::class],
            ['/a/b/d', '/a/b/e', '/a'],
            ['/a/b/e', '/a/f', '/a', null, RouteAware::class],
            ['/a/f', '/a/f/g', '/a'],
            ['/a/f/g', '/a/f/g/h', '/a'],
            ['/a/f/g/h', '/a/i', '/a'],
            ['/a/f/g', '/a/i', '/a', 2],
        ];
    }

    /**
     * @dataProvider getNextData
     */
    public function testGetNext(?string $expected, ?string $path, ?string $anchor = null, ?int $depth = null, string $class = Generic::class): void
    {
        $next = $this->helper->getNext($path, $anchor, $depth);
        if (null === $expected) {
            $this->assertNull($next);
        } else {
            $this->assertInstanceOf($class, $next);
            $this->assertEquals($expected, $next->getId());
        }
    }

    public static function getNextData(): array
    {
        return [
            [null, null],
            [null, '/a'],
            ['/a/f', '/a/b'],
            ['/a/b/d', '/a/b/c'],
            ['/a/b/e', '/a/b/d', null, null, RouteAware::class],
            [null, '/a/b/e'],
            ['/a/i', '/a/f'],
            [null, '/a/f/g'],
            [null, '/a/f/g/h'],
            ['/a/b', '/a', '/a'],
            ['/a/b/c', '/a/b', '/a', null, RouteAware::class],
            ['/a/b/d', '/a/b/c', '/a'],
            ['/a/b/e', '/a/b/d', '/a', null, RouteAware::class],
            ['/a/f', '/a/b/e', '/a'],
            ['/a/f/g', '/a/f', '/a'],
            ['/a/f/g/h', '/a/f/g', '/a'],
            ['/a/i', '/a/f/g/h', '/a'],
            [null, '/a/i', '/a'],
            [null, '/a/b/e', '/a/b'],
            ['/a/i', '/a/f/g', '/a', 2],
        ];
    }

    /**
     * @dataProvider getPrevLinkableData
     */
    public function testGetPrevLinkable(?string $expected, ?string $path, ?string $anchor = null, ?int $depth = null): void
    {
        $prev = $this->helper->getPrevLinkable($path, $anchor, $depth);
        if (null === $expected) {
            $this->assertNull($prev);
        } else {
            $this->assertInstanceOf(RouteAware::class, $prev);
            $this->assertEquals($expected, $prev->getId());
        }
    }

    public static function getPrevLinkableData(): array
    {
        // TODO: expand test case
        return [
            [null, null],
            [null, '/a/b/c'],
            ['/a/b/c', '/a/b/d'],
            ['/a/b/c', '/a/b/e'],
        ];
    }

    /**
     * @dataProvider getNextLinkableData
     */
    public function testGetNextLinkable(?string $expected, ?string $path, ?string $anchor = null, ?int $depth = null): void
    {
        $next = $this->helper->getNextLinkable($path, $anchor, $depth);
        if (null === $expected) {
            $this->assertNull($next);
        } else {
            $this->assertInstanceOf(RouteAware::class, $next);
            $this->assertEquals($expected, $next->getId());
        }
    }

    public static function getNextLinkableData(): array
    {
        // TODO: expand test case
        return [
            [null, null],
            ['/a/b/e', '/a/b/c'],
            ['/a/b/e', '/a/b/d'],
            [null, '/a/b/e'],
        ];
    }
}
