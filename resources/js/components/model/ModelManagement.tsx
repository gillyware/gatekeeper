import { useApi } from '@/lib/api';
import { fetchModel } from '@/lib/models';
import { type ModelManagementTab } from '@/types';
import { type ConfiguredModel, type LookupModelRequest } from '@/types/api/model';
import ModelManagementTabs from '@components/model/ModelEntityTabs';
import ModelPermissions from '@components/model/ModelPermissions';
import ModelRoles from '@components/model/ModelRoles';
import ModelSummary from '@components/model/ModelSummary';
import ModelTeams from '@components/model/ModelTeams';
import { Loader } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router';

export default function ModelManagement() {
    const api = useApi();
    const { modelLabel, modelPk } = useParams<{ modelLabel: string; modelPk: string }>() as { modelLabel: string; modelPk: string };

    const [model, setModel] = useState<ConfiguredModel | null>(null);
    const [tab, setTab] = useState<ModelManagementTab>('overview');

    const [loadingModel, setLoadingModel] = useState<boolean>(true);

    const [errorLoadingModel, setErrorLoadingModel] = useState<string | null>(null);

    useEffect(() => {
        const params: LookupModelRequest = { model_label: modelLabel, model_pk: modelPk };
        fetchModel(api, params, setModel, setLoadingModel, setErrorLoadingModel);
    }, [modelLabel, modelPk]);

    if (loadingModel) {
        return (
            <div className="flex h-full w-full items-center justify-center">
                <Loader className="h-6 w-6 animate-spin text-gray-500 dark:text-gray-400" />
            </div>
        );
    }

    if (errorLoadingModel || !model) {
        return <div className="text-red-500">{errorLoadingModel || 'Failed to load model.'}</div>;
    }

    return (
        <div className="flex flex-col gap-8 space-y-6 p-4">
            <ModelManagementTabs tab={tab} changeTab={setTab} model={model} />

            {tab === 'overview' && <ModelSummary model={model} />}

            {tab === 'permissions' && <ModelPermissions model={model} />}

            {tab === 'roles' && <ModelRoles model={model} />}

            {tab === 'teams' && <ModelTeams model={model} />}
        </div>
    );
}
