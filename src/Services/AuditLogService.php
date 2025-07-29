<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Contracts\AuditLogServiceInterface;
use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Packets\AuditLog\AuditLogPagePacket;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AuditLogService implements AuditLogServiceInterface
{
    public function __construct(private readonly AuditLogRepository $auditLogRepository) {}

    /**
     * Check if the audit log table exists.
     */
    public function tableExists(): bool
    {
        return $this->auditLogRepository->tableExists();
    }

    /**
     * Get a page of audit logs.
     */
    public function getPage(AuditLogPagePacket $packet): LengthAwarePaginator
    {
        return $this->auditLogRepository->getPage($packet)
            ->through(fn (AuditLog $log) => $log->toPacket());
    }

    /**
     * Get the message for the audit log based on the action type.
     */
    public function getMessageForAuditLog(AuditLog $log): string
    {
        $context = $this->buildContext($log);

        $templates = [
            AuditLogAction::CreatePermission->value => '{actor} created a new {entity} named {name}',
            AuditLogAction::CreateRole->value => '{actor} created a new {entity} named {name}',
            AuditLogAction::CreateFeature->value => '{actor} created a new {entity} named {name}',
            AuditLogAction::CreateTeam->value => '{actor} created a new {entity} named {name}',

            AuditLogAction::UpdatePermissionName->value => '{actor} updated {entity} name from {old_name} to {name}',
            AuditLogAction::UpdateRoleName->value => '{actor} updated {entity} name from {old_name} to {name}',
            AuditLogAction::UpdateFeatureName->value => '{actor} updated {entity} name from {old_name} to {name}',
            AuditLogAction::UpdateTeamName->value => '{actor} updated {entity} name from {old_name} to {name}',

            AuditLogAction::GrantPermissionByDefault->value => '{actor} granted {entity} named {name} by default',
            AuditLogAction::GrantRoleByDefault->value => '{actor} granted {entity} named {name} by default',
            AuditLogAction::GrantFeatureByDefault->value => '{actor} granted {entity} named {name} by default',
            AuditLogAction::GrantTeamByDefault->value => '{actor} granted {entity} named {name} by default',

            AuditLogAction::RevokePermissionDefaultGrant->value => '{actor} revoked the default grant from {entity} named {name}',
            AuditLogAction::RevokeRoleDefaultGrant->value => '{actor} revoked the default grant from {entity} named {name}',
            AuditLogAction::RevokeFeatureDefaultGrant->value => '{actor} revoked the default grant from {entity} named {name}',
            AuditLogAction::RevokeTeamDefaultGrant->value => '{actor} revoked the default grant from {entity} named {name}',

            AuditLogAction::DeactivatePermission->value => '{actor} deactivated {entity} named {name}',
            AuditLogAction::DeactivateRole->value => '{actor} deactivated {entity} named {name}',
            AuditLogAction::DeactivateFeature->value => '{actor} deactivated {entity} named {name}',
            AuditLogAction::DeactivateTeam->value => '{actor} deactivated {entity} named {name}',

            AuditLogAction::ReactivatePermission->value => '{actor} reactivated {entity} named {name}',
            AuditLogAction::ReactivateRole->value => '{actor} reactivated {entity} named {name}',
            AuditLogAction::ReactivateFeature->value => '{actor} reactivated {entity} named {name}',
            AuditLogAction::ReactivateTeam->value => '{actor} reactivated {entity} named {name}',

            AuditLogAction::DeletePermission->value => '{actor} deleted {entity} named {name}',
            AuditLogAction::DeleteRole->value => '{actor} deleted {entity} named {name}',
            AuditLogAction::DeleteFeature->value => '{actor} deleted {entity} named {name}',
            AuditLogAction::DeleteTeam->value => '{actor} deleted {entity} named {name}',

            AuditLogAction::AssignPermission->value => '{actor} assigned {entity} named {name} to {target}',
            AuditLogAction::AssignRole->value => '{actor} assigned {entity} named {name} to {target}',
            AuditLogAction::AssignFeature->value => '{actor} assigned {entity} named {name} to {target}',

            AuditLogAction::UnassignPermission->value => '{actor} unassigned {entity} named {name} from {target}',
            AuditLogAction::UnassignRole->value => '{actor} unassigned {entity} named {name} from {target}',
            AuditLogAction::UnassignFeature->value => '{actor} unassigned {entity} named {name} from {target}',

            AuditLogAction::RevokePermission->value => '{actor} unassigned {entity} named {name} from {target}',
            AuditLogAction::RevokeRole->value => '{actor} unassigned {entity} named {name} from {target}',
            AuditLogAction::RevokeFeature->value => '{actor} unassigned {entity} named {name} from {target}',

            AuditLogAction::AddTeam->value => '{actor} added {target} to team named {name}',
            AuditLogAction::RemoveTeam->value => '{actor} removed {target} from team named {name}',

            AuditLogAction::DenyPermission->value => '{actor} denied {entity} named {name} from {target}',
            AuditLogAction::DenyRole->value => '{actor} denied {entity} named {name} from {target}',
            AuditLogAction::DenyFeature->value => '{actor} denied {entity} named {name} from {target}',
            AuditLogAction::DenyTeam->value => '{actor} denied {entity} named {name} from {target}',

            AuditLogAction::UndenyPermission->value => '{actor} undenied {entity} named {name} from {target}',
            AuditLogAction::UndenyRole->value => '{actor} undenied {entity} named {name} from {target}',
            AuditLogAction::UndenyFeature->value => '{actor} undenied {entity} named {name} from {target}',
            AuditLogAction::UndenyTeam->value => '{actor} undenied {entity} named {name} from {target}',
        ];

        $template = $templates[$log->action] ?? '';

        return $this->renderTemplate($template, $context);
    }

    private function buildContext(AuditLog $log): array
    {
        return [
            'actor' => $this->actor($log),
            'target' => $this->target($log),
            'entity' => Str::before($log->action, '_'),
            'name' => Arr::get($log->metadata, 'name', ''),
            'old_name' => Arr::get($log->metadata, 'old_name', ''),
        ];
    }

    private function renderTemplate(string $template, array $context): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($m) use ($context) {
            $key = $m[1];
            $val = $context[$key] ?? '';

            return "<strong>{$val}</strong>";
        }, $template);
    }

    private function actor(AuditLog $log): string
    {
        $type = $log->action_by_model_type;
        $id = $log->action_by_model_id;

        if (! $type) {
            return 'Unknown';
        }

        return Str::endsWith($type, 'SystemActor')
            ? 'System'
            : class_basename($type).'#'.$id;
    }

    private function target(AuditLog $log): string
    {
        $type = $log->action_to_model_type;
        $id = $log->action_to_model_id;

        return $type ? class_basename($type).'#'.$id : 'Unknown';
    }
}
