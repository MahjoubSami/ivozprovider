<?php

namespace Ivoz\Provider\Infrastructure\Gearman\Jobs;

use Ivoz\Core\Infrastructure\Domain\Service\Gearman\Jobs\AbstractJob;

class Invoicer extends AbstractJob
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $method = "WorkerInvoices~create";

    /**
     * @var array
     */
    protected $mainVariables = array(
        'id',
    );

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
