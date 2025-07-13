import { useGatekeeper } from '@/context/GatekeeperContext';
import { useApi } from '@/lib/api';
import { GatekeeperEntity, ModelManagementTab } from '@/types';
import { GatekeeperError } from '@/types/api';
import { ConfiguredModel, LookupModelRequest } from '@/types/api/model';
import ModelManagementTabs from '@components/models/ModelEntityTabs';
import ModelSummary from '@components/models/ModelSummary';
import { Loader } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router';

export default function ModelAccessControl() {
    const api = useApi();
    const { config } = useGatekeeper();
    const { modelLabel, modelPk } = useParams<{ modelLabel: string; modelPk: string }>() as { modelLabel: string; modelPk: string };

    const [model, setModel] = useState<ConfiguredModel | null>(null);

    const [tab, setTab] = useState<ModelManagementTab>('overview');

    const [loading, setLoading] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (modelLabel && modelPk) {
            lookupModel({ model_label: modelLabel, model_pk: modelPk });
        }
    }, [modelLabel, modelPk]);

    const lookupModel = async (params: LookupModelRequest) => {
        setLoading(true);
        setError(null);

        const response = await api.lookupModel(params);

        if (response.status >= 400) {
            setError(response.errors?.general || 'Failed to fetch model.');
            setModel(null);
            setLoading(false);
            return;
        }

        const freshModel = response.data as ConfiguredModel;
        setModel(freshModel);
        setLoading(false);
    };

    const assign = async (entity: GatekeeperEntity, value: string, onError: (e: GatekeeperError) => void) => {
        const response = await api.assignToModel({ model_label: modelLabel, model_pk: modelPk, entity, entity_name: value });

        if (response.status >= 400) {
            const error = response.errors?.general || 'Failed to assign entity to model.';
            onError({ message: error });
            return;
        }

        lookupModel({ model_label: modelLabel, model_pk: modelPk });
    };

    const revoke = async (entity: GatekeeperEntity, entityName: string, onError: (e: GatekeeperError) => void) => {
        const response = await api.revokeFromModel({
            model_label: modelLabel,
            model_pk: modelPk,
            entity,
            entity_name: entityName,
        });

        if (response.status >= 400) {
            const error = response.errors?.general || 'Failed to revoke entity from model.';
            onError({ message: error });
            return;
        }

        lookupModel({ model_label: modelLabel, model_pk: modelPk });
    };

    return (
        <div className="space-y-6 p-4">
            {error && <div className="text-red-500">{error}</div>}

            {loading && (
                <div className="flex items-center justify-center">
                    <Loader className="h-6 w-6 animate-spin text-gray-500 dark:text-gray-400" />
                </div>
            )}

            {model && (
                <div className="flex flex-col gap-8 space-y-6">
                    <ModelManagementTabs tab={tab} changeTab={setTab} />
                    <ModelSummary model={model} />

                    {/* {(model.has_permissions || model.direct_permissions.length > 0) && (
                        <ModelAccessList
                            title="Permissions"
                            enabledOnModel={model.has_permissions}
                            enabledInApp={true}
                            items={model.direct_permissions}
                            entity="permission"
                            onRevoke={revoke}
                            onAssign={assign}
                        />
                    )}

                    {(model.has_roles || model.direct_roles.length > 0) && (
                        <ModelAccessList
                            title="Roles"
                            enabledOnModel={model.has_roles}
                            enabledInApp={config.roles_enabled}
                            items={model.direct_roles}
                            entity="role"
                            onRevoke={revoke}
                            onAssign={assign}
                        />
                    )}

                    {(model.has_teams || model.direct_teams.length > 0) && (
                        <ModelAccessList
                            title="Teams"
                            enabledOnModel={model.has_teams}
                            enabledInApp={config.teams_enabled}
                            items={model.direct_teams}
                            entity="team"
                            onRevoke={revoke}
                            onAssign={assign}
                        />
                    )} */}
                </div>
            )}
        </div>
    );
}
