<?php

namespace Tests\Provider\OutgoingRouting;

use Ivoz\Kam\Domain\Model\TrunksLcrRuleTarget\TrunksLcrRuleTarget;
use Ivoz\Provider\Domain\Model\OutgoingRouting\OutgoingRouting;
use Ivoz\Provider\Domain\Model\OutgoingRouting\OutgoingRoutingDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\DbIntegrationTestHelperTrait;
use Ivoz\Kam\Domain\Model\TrunksLcrRule\TrunksLcrRule;

class OutgoingRoutingLifeCycleTestLifeCycleTest extends KernelTestCase
{
    use DbIntegrationTestHelperTrait;

    /**
     * @return OutgoingRouting
     */
    protected function getOutgoingRoutingDto()
    {
        $outgoingRoutingDto = new OutgoingRoutingDto();
        $outgoingRoutingDto
              ->setType('pattern')
              ->setPriority(2)
              ->setWeight(2)
              ->setBrandId(1)
              ->setCompanyId(1)
              ->setPeeringContractId(1)
              ->setRoutingPatternId(1);

        return $outgoingRoutingDto;
    }

    /**
     * @return OutgoingRouting
     */
    protected function addOutgoingRouting()
    {
        return $this
            ->entityPersister
            ->persistDto($this->getOutgoingRoutingDto(), null, true);
    }

    /**
     * @test
     */
    public function it_persists_outgoing_routing()
    {
        $extensionRepository = $this->em
            ->getRepository(OutgoingRouting::class);

        $fixtureOutgoingRoutings = $extensionRepository->findAll();
        $this->assertCount(2, $fixtureOutgoingRoutings);

        $this->addOutgoingRouting();

        $extensions = $extensionRepository->findAll();
        $this->assertCount(
            count($fixtureOutgoingRoutings) + 1,
            $extensions
        );
    }

    /**
     * @test
     */
    public function new_outgoing_routing_creates_lcr_rules()
    {
        $this->addOutgoingRouting();

        $lcrRules = $this->getChangelogByClass(
            TrunksLcrRule::class
        );

        $this->assertGreaterThan(0, count($lcrRules));
    }

    /**
     * @test
     */
    public function new_outgoing_routing_creates_lcr_rule_targets()
    {
        $this->addOutgoingRouting();

        $lcrRuleTargets = $this->getChangelogByClass(
            TrunksLcrRuleTarget::class
        );

        $this->assertGreaterThan(0, count($lcrRuleTargets));
    }
}