<?php

namespace Gillyware\Gatekeeper\Packets\Config;

use Gillyware\Gatekeeper\Exceptions\Model\ModelConfigurationException;
use Gillyware\Gatekeeper\Traits\EnforcesForGatekeeper;
use Gillyware\Postal\Attributes\Rule;
use Gillyware\Postal\Packet;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

/**
 * Wraps one entry of gatekeeper.models.manageable
 *
 * Example source array:
 * [
 *   'label'      => 'Role',
 *   'class'      => \App\Models\Role::class,
 *   'searchable' => [ ['column'=>'name','label'=>'name'] ],
 *   'displayable'=> [ ['column'=>'name','label'=>'Name','cli_width'=>20] ],
 * ]
 */
final class ManageableModelPacket extends Packet
{
    use EnforcesForGatekeeper;

    public function __construct(
        #[Rule(['required', 'string', 'max:255'])]
        public readonly string $label,

        #[Rule(['required', 'string'])]
        public readonly string $class,

        #[Rule(['required', 'array'])]
        public readonly array $searchable,

        #[Rule(['required', 'array'])]
        public readonly array $displayable,
    ) {}

    public function toArray(): array
    {
        return [
            'model_label' => $this->label,
            'searchable' => $this->searchable,
            'displayable' => $this->displayable,
            'is_permission' => $this->modelIsPermission($this->class),
            'is_role' => $this->modelIsRole($this->class),
            'is_team' => $this->modelIsTeam($this->class),
            'has_permissions' => $this->modelInteractsWithPermissions($this->class),
            'has_roles' => $this->modelInteractsWithRoles($this->class),
            'has_teams' => $this->modelInteractsWithTeams($this->class),
        ];
    }

    protected static function failedValidation(Validator $validator): void
    {
        throw new ModelConfigurationException($validator->errors()->toJson());
    }

    protected static function explicitRules(): array
    {
        return [
            // Ensure the class exists and is an Eloquent model
            'class' => [
                function (string $attr, string $value, \Closure $fail): void {
                    if (! class_exists($value)) {
                        $fail("Class {$value} does not exist.");

                        return;
                    }
                    if (! is_subclass_of($value, Model::class)) {
                        $fail("{$value} is not an Eloquent model.");
                    }
                    if ((new ReflectionClass($value))->isAbstract()) {
                        $fail("Class {$value} is abstract.");
                    }
                },
            ],

            // searchable.* must have column + label
            'searchable.*.column' => ['required', 'string'],
            'searchable.*.label' => ['required', 'string'],

            // displayable.* must have column + label + cli_width
            'displayable.*.column' => ['required', 'string'],
            'displayable.*.label' => ['required', 'string'],
            'displayable.*.cli_width' => ['required', 'integer'],
        ];
    }
}
