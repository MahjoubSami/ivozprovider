<?php
declare(strict_types = 1);

namespace Ivoz\Provider\Domain\Model\CalendarPeriod;

use Ivoz\Core\Application\DataTransferObjectInterface;
use Ivoz\Core\Application\ForeignKeyTransformerInterface;
use Ivoz\Provider\Domain\Model\CalendarPeriodsRelSchedule\CalendarPeriodsRelScheduleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
* @codeCoverageIgnore
*/
trait CalendarPeriodTrait
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var ArrayCollection
     * CalendarPeriodsRelScheduleInterface mappedBy calendarPeriod
     * orphanRemoval
     */
    protected $relSchedules;

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct(...func_get_args());
        $this->relSchedules = new ArrayCollection();
    }

    abstract protected function sanitizeValues();

    /**
     * Factory method
     * @internal use EntityTools instead
     * @param CalendarPeriodDto $dto
     * @param ForeignKeyTransformerInterface  $fkTransformer
     * @return static
     */
    public static function fromDto(
        DataTransferObjectInterface $dto,
        ForeignKeyTransformerInterface $fkTransformer
    ) {
        /** @var static $self */
        $self = parent::fromDto($dto, $fkTransformer);
        if (!is_null($dto->getRelSchedules())) {
            $self->replaceRelSchedules(
                $fkTransformer->transformCollection(
                    $dto->getRelSchedules()
                )
            );
        }

        $self->sanitizeValues();
        if ($dto->getId()) {
            $self->id = $dto->getId();
            $self->initChangelog();
        }

        return $self;
    }

    /**
     * @internal use EntityTools instead
     * @param CalendarPeriodDto $dto
     * @param ForeignKeyTransformerInterface  $fkTransformer
     * @return static
     */
    public function updateFromDto(
        DataTransferObjectInterface $dto,
        ForeignKeyTransformerInterface $fkTransformer
    ) {
        parent::updateFromDto($dto, $fkTransformer);
        if (!is_null($dto->getRelSchedules())) {
            $this->replaceRelSchedules(
                $fkTransformer->transformCollection(
                    $dto->getRelSchedules()
                )
            );
        }
        $this->sanitizeValues();

        return $this;
    }

    /**
     * @internal use EntityTools instead
     * @param int $depth
     * @return CalendarPeriodDto
     */
    public function toDto($depth = 0)
    {
        $dto = parent::toDto($depth);
        return $dto
            ->setId($this->getId());
    }

    /**
     * @return array
     */
    protected function __toArray()
    {
        return parent::__toArray() + [
            'id' => self::getId()
        ];
    }

    /**
     * Add relSchedule
     *
     * @param CalendarPeriodsRelScheduleInterface $relSchedule
     *
     * @return static
     */
    public function addRelSchedule(CalendarPeriodsRelScheduleInterface $relSchedule): CalendarPeriodInterface
    {
        $this->relSchedules->add($relSchedule);

        return $this;
    }

    /**
     * Remove relSchedule
     *
     * @param CalendarPeriodsRelScheduleInterface $relSchedule
     *
     * @return static
     */
    public function removeRelSchedule(CalendarPeriodsRelScheduleInterface $relSchedule): CalendarPeriodInterface
    {
        $this->relSchedules->removeElement($relSchedule);

        return $this;
    }

    /**
     * Replace relSchedules
     *
     * @param ArrayCollection $relSchedules of CalendarPeriodsRelScheduleInterface
     *
     * @return static
     */
    public function replaceRelSchedules(ArrayCollection $relSchedules): CalendarPeriodInterface
    {
        $updatedEntities = [];
        $fallBackId = -1;
        foreach ($relSchedules as $entity) {
            $index = $entity->getId() ? $entity->getId() : $fallBackId--;
            $updatedEntities[$index] = $entity;
            $entity->setCalendarPeriod($this);
        }
        $updatedEntityKeys = array_keys($updatedEntities);

        foreach ($this->relSchedules as $key => $entity) {
            $identity = $entity->getId();
            if (in_array($identity, $updatedEntityKeys)) {
                $this->relSchedules->set($key, $updatedEntities[$identity]);
            } else {
                $this->relSchedules->remove($key);
            }
            unset($updatedEntities[$identity]);
        }

        foreach ($updatedEntities as $entity) {
            $this->addRelSchedule($entity);
        }

        return $this;
    }

    /**
     * Get relSchedules
     * @param Criteria | null $criteria
     * @return CalendarPeriodsRelScheduleInterface[]
     */
    public function getRelSchedules(Criteria $criteria = null): array
    {
        if (!is_null($criteria)) {
            return $this->relSchedules->matching($criteria)->toArray();
        }

        return $this->relSchedules->toArray();
    }

}
