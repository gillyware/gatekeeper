import { useGatekeeper } from '@/context/GatekeeperContext';
import { type EntityLayoutText } from '@/lib/lang/en/entity/layout';
import { type GatekeeperEntity } from '@/types';
import { Alert, AlertDescription, AlertTitle } from '@components/ui/alert';
import { Ban } from 'lucide-react';

interface EntityFeatureDisabledAlertProps {
    entity: GatekeeperEntity;
    language: EntityLayoutText;
}

export default function EntityFeatureDisabledAlert({ entity, language }: EntityFeatureDisabledAlertProps) {
    const { config } = useGatekeeper();

    const featureEnabled = {
        permission: () => true,
        role: () => config.roles_enabled,
        feature: () => config.features_enabled,
        team: () => config.teams_enabled,
    };

    if (featureEnabled[entity]()) {
        return null;
    }

    return (
        <Alert className="mb-8 w-full max-w-xl lg:max-w-full">
            <Ban className="s-4" />
            <AlertTitle>{language.featureDisabledTitle}</AlertTitle>
            <AlertDescription>{language.featureDisabledDescription}</AlertDescription>
        </Alert>
    );
}
