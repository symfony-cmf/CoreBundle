<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\PublishWorkflow\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableReadInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\Voter\PublishableVoter;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PublishableVoterTest extends TestCase
{
    /**
     * @var PublishableVoter
     */
    private $voter;

    /**
     * @var TokenInterface
     */
    private $token;

    public function setUp(): void
    {
        $this->voter = new PublishableVoter();
        $this->token = new AnonymousToken('', '');
    }

    public function providePublishWorkflowChecker()
    {
        return [
            [
                'expected' => VoterInterface::ACCESS_GRANTED,
                'isPublishable' => true,
                'attributes' => PublishWorkflowChecker::VIEW_ATTRIBUTE,
            ],
            [
                'expected' => VoterInterface::ACCESS_DENIED,
                'isPublishable' => false,
                'attributes' => PublishWorkflowChecker::VIEW_ATTRIBUTE,
            ],
            [
                'expected' => VoterInterface::ACCESS_GRANTED,
                'isPublishable' => true,
                'attributes' => [
                    PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE,
                    PublishWorkflowChecker::VIEW_ATTRIBUTE,
                ],
            ],
            [
                'expected' => VoterInterface::ACCESS_DENIED,
                'isPublishable' => false,
                'attributes' => PublishWorkflowChecker::VIEW_ANONYMOUS_ATTRIBUTE,
            ],
            [
                'expected' => VoterInterface::ACCESS_ABSTAIN,
                'isPublishable' => true,
                'attributes' => 'other',
            ],
            [
                'expected' => VoterInterface::ACCESS_ABSTAIN,
                'isPublishable' => true,
                'attributes' => [PublishWorkflowChecker::VIEW_ATTRIBUTE, 'other'],
            ],
        ];
    }

    /**
     * @dataProvider providePublishWorkflowChecker
     *
     * use for voters!
     */
    public function testPublishWorkflowChecker($expected, $isPublishable, $attributes)
    {
        $attributes = (array) $attributes;
        $doc = $this->createMock(PublishableReadInterface::class);
        $doc->expects($this->any())
            ->method('isPublishable')
            ->will($this->returnValue($isPublishable))
        ;

        $this->assertEquals($expected, $this->voter->vote($this->token, $doc, $attributes));
    }

    public function testUnsupportedClass()
    {
        $result = $this->voter->vote(
            $this->token,
            $this,
            [PublishWorkflowChecker::VIEW_ATTRIBUTE]
        );
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testNonClassSubject()
    {
        $result = $this->voter->vote($this->token, [1, 2, 3], [PublishWorkflowChecker::VIEW_ATTRIBUTE]);
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }
}
