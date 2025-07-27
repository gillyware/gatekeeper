<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\AbstractBaseEntityModel;
use Gillyware\Gatekeeper\Packets\Entities\AbstractBaseEntityPacket;
use UnitEnum;

/**
 * @template TModel of AbstractBaseEntityModel
 * @template TPacket of AbstractBaseEntityPacket
 *
 * @implements EntityServiceInterface<TModel, TPacket>
 */
interface FeatureServiceInterface extends EntityServiceInterface
{
    /**
     * Set a feature as off by default.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     * @return TPacket
     */
    public function turnOffByDefault($entity);

    /**
     * Set a feature as on by default.
     *
     * @param  TModel|TPacket|string|UnitEnum  $entity
     * @return TPacket
     */
    public function turnOnByDefault($entity);
}
