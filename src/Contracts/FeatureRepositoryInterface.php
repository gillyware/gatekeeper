<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\AbstractBaseEntityModel;

/**
 * @template TModel of AbstractBaseEntityModel
 *
 * @implements EntityRepositoryInterface<TModel>
 */
interface FeatureRepositoryInterface extends EntityRepositoryInterface
{
    /**
     * Set a feature as off by default.
     *
     * @param  TModel  $entity
     * @return TModel
     */
    public function turnOffByDefault($entity);

    /**
     * Set a feature as on by default.
     *
     * @param  TModel  $entity
     * @return TModel
     */
    public function turnOnByDefault($entity);
}
