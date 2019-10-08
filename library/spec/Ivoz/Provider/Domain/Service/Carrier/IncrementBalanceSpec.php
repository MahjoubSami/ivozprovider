<?php

namespace spec\Ivoz\Provider\Domain\Service\Carrier;

use Ivoz\Core\Application\Service\EntityTools;
use Ivoz\Provider\Domain\Model\Brand\BrandInterface;
use Ivoz\Provider\Domain\Model\Carrier\CarrierInterface;
use Ivoz\Provider\Domain\Model\Carrier\CarrierRepository;
use Ivoz\Provider\Domain\Service\Carrier\CarrierBalanceServiceInterface;
use Ivoz\Provider\Domain\Service\Carrier\IncrementBalance;
use Ivoz\Provider\Domain\Service\Carrier\SyncBalances;
use Ivoz\Provider\Domain\Service\BalanceMovement\CreateByCarrier;
use Symfony\Bridge\Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\HelperTrait;

class IncrementBalanceSpec extends ObjectBehavior
{
    use HelperTrait;

    protected $entityTools;
    protected $logger;
    protected $client;
    protected $carrierRepository;
    protected $syncBalanceService;
    protected $lastError;
    protected $createBalanceMovementByCarrier;

    const BALANCE = 100.45;

    public function let(
        EntityTools $entityTools,
        Logger $logger,
        CarrierBalanceServiceInterface $client,
        CarrierRepository $carrierRepository,
        SyncBalances $syncBalanceService,
        CreateByCarrier $createByCarrier
    ) {
        $this->entityTools = $entityTools;
        $this->logger = $logger;
        $this->client = $client;
        $this->carrierRepository = $carrierRepository;
        $this->syncBalanceService = $syncBalanceService;
        $this->createBalanceMovementByCarrier = $createByCarrier;

        $this->beConstructedWith(
            $this->entityTools,
            $this->logger,
            $this->client,
            $this->carrierRepository,
            $this->syncBalanceService,
            $this->createBalanceMovementByCarrier
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IncrementBalance::class);
    }

    function it_increases_balance(
        CarrierInterface $carrier,
        BrandInterface $brand
    ) {
        $this->prepareExecution(
            $carrier,
            $brand
        );

        $this
            ->syncBalanceService
            ->updateCarriers(
                Argument::type('numeric'),
                Argument::type('array')
            )
            ->shouldBeCalled();

        $this->execute(
            1,
            10.15
        );
    }

    function it_creates_balance_movement(
        CarrierInterface $carrier,
        BrandInterface $brand
    ) {
        $this->prepareExecution(
            $carrier,
            $brand
        );

        $this
            ->createBalanceMovementByCarrier
            ->execute(
                $carrier,
                10.15,
                self::BALANCE
            )
            ->shouldBeCalled();

        $this->execute(
            1,
            10.15
        );
    }

    protected function prepareExecution(
        CarrierInterface $carrier,
        BrandInterface $brand
    ) {
        $this
            ->logger
            ->info(Argument::any())
            ->willReturn(null);

        $this
            ->carrierRepository
            ->find(Argument::type('numeric'))
            ->willReturn($carrier);

        $this
            ->client
            ->incrementBalance(
                $carrier,
                Argument::type('float')
            )
            ->willReturn([
                'success' => true
            ]);

        $carrier
            ->getBrand()
            ->willReturn($brand);

        $carrier
            ->getId()
            ->willReturn(2);

        $brand
            ->getId()
            ->willReturn(1);

        $this
            ->client
            ->getBalance(
                Argument::type('numeric'),
                Argument::type('numeric')
            )
            ->willReturn(self::BALANCE);
    }
}
