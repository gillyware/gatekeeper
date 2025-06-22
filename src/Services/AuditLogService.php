<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Constants\Action;
use Gillyware\Gatekeeper\Models\AuditLog;

class AuditLogService
{
    /**
     * Get the message for the audit log based on the action type.
     */
    public function getMessageForAuditLog(AuditLog $log): string
    {
        return match ($log->action) {
            Action::PERMISSION_CREATE, Action::ROLE_CREATE, Action::TEAM_CREATE => $this->formatCreateMessage($log),
            Action::PERMISSION_UPDATE, Action::ROLE_UPDATE, Action::TEAM_UPDATE => $this->formatUpdateMessage($log),
            Action::PERMISSION_DEACTIVATE, Action::ROLE_DEACTIVATE, Action::TEAM_DEACTIVATE => $this->formatDeactivateMessage($log),
            Action::PERMISSION_REACTIVATE, Action::ROLE_REACTIVATE, Action::TEAM_REACTIVATE => $this->formatReactivateMessage($log),
            Action::PERMISSION_DELETE, Action::ROLE_DELETE, Action::TEAM_DELETE => $this->formatDeleteMessage($log),

            Action::PERMISSION_ASSIGN, Action::ROLE_ASSIGN => $this->formatAssignMessage($log),
            Action::PERMISSION_REVOKE, Action::ROLE_REVOKE => $this->formatRevokeMessage($log),

            Action::TEAM_ADD => $this->formatAddToTeamMessage($log),
            Action::TEAM_REMOVE => $this->formatRemoveFromTeamMessage($log),

            default => '',
        };
    }

    private function formatCreateMessage(AuditLog $log): string
    {
        $actor = $this->getActor($log);
        $entity = strtolower(class_basename($log->action_to_model_type));

        return "<strong>{$actor}</strong> created a new <strong>{$entity}</strong> with name <strong>{$log->metadata['name']}</strong>";
    }

    private function formatUpdateMessage(AuditLog $log): string
    {
        $actor = $this->getActor($log);
        $entity = strtolower(class_basename($log->action_to_model_type));

        return "<strong>{$actor}</strong> updated <strong>{$entity}</strong> name from <strong>{$log->metadata['old_name']}</strong> to <strong>{$log->metadata['name']}</strong>";
    }

    private function formatDeactivateMessage(AuditLog $log): string
    {
        $actor = $this->getActor($log);
        $entity = strtolower(class_basename($log->action_to_model_type));

        return "<strong>{$actor}</strong> deactivated <strong>{$entity}</strong> with name <strong>{$log->metadata['name']}</strong>";
    }

    private function formatReactivateMessage(AuditLog $log): string
    {
        $actor = $this->getActor($log);
        $entity = strtolower(class_basename($log->action_to_model_type));

        return "<strong>{$actor}</strong> reactivated <strong>{$entity}</strong> with name <strong>{$log->metadata['name']}</strong>";
    }

    private function formatDeleteMessage(AuditLog $log): string
    {
        $actor = $this->getActor($log);
        $entity = strtolower(class_basename($log->action_to_model_type));

        return "<strong>{$actor}</strong> deleted <strong>{$entity}</strong> with name <strong>{$log->metadata['name']}</strong>";
    }

    private function formatAssignMessage(AuditLog $log): string
    {
        $actor = $this->getActor($log);
        $target = $this->getTarget($log);
        $entity = explode('_', $log->action)[0];

        return "<strong>{$actor}</strong> assigned <strong>{$entity}</strong> with name <strong>{$log->metadata['name']}</strong> to <strong>{$target}</strong>";
    }

    private function formatRevokeMessage(AuditLog $log): string
    {
        $actor = $this->getActor($log);
        $target = $this->getTarget($log);
        $entity = explode('_', $log->action)[0];

        return "<strong>{$actor}</strong> revoked <strong>{$entity}</strong> with name <strong>{$log->metadata['name']}</strong> from <strong>{$target}</strong>";
    }

    private function formatAddToTeamMessage(AuditLog $log): string
    {
        $actor = $this->getActor($log);
        $target = $this->getTarget($log);

        return "<strong>{$actor}</strong> added <strong>{$target}</strong> to team <strong>{$log->metadata['name']}</strong>";
    }

    private function formatRemoveFromTeamMessage(AuditLog $log): string
    {
        $actor = $this->getActor($log);
        $target = $this->getTarget($log);

        return "<strong>{$actor}</strong> removed <strong>{$target}</strong> from team <strong>{$log->metadata['name']}</strong>";
    }

    private function getActor(AuditLog $log): string
    {
        $byType = $log->action_by_model_type;
        $byId = $log->action_by_model_id;

        return $byType
          ? (str_ends_with($byType, 'SystemActor') ? 'System' : class_basename($byType).'#'.$byId)
          : 'Unknown';
    }

    private function getTarget(AuditLog $log): string
    {
        $toType = $log->action_to_model_type;
        $toId = $log->action_to_model_id;

        return $toType
          ? class_basename($toType).'#'.$toId
          : 'Unknown';
    }
}
