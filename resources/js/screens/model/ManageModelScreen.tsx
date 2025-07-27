import ModelEntityTables from '@/components/model/ModelEntityTables';
import ModelManagementTabs from '@/components/model/ModelEntityTabs';
import ModelSummary from '@/components/model/ModelSummary';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import ModelLayout from '@/layouts/model-layout';
import { useApi } from '@/lib/api';
import { manageModelText, type ManageModelText } from '@/lib/lang/en/model/manage';
import { getModel } from '@/lib/models';
import { GatekeeperFeature, GatekeeperPermission, GatekeeperRole, GatekeeperTeam, type ModelManagementTab } from '@/types';
import { type ConfiguredModel, type ModelRequest } from '@/types/api/model';
import { Loader } from 'lucide-react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { useParams } from 'react-router';

export default function ManageModelScreen() {
    const api = useApi();
    const { modelLabel, modelPk } = useParams<{ modelLabel: string; modelPk: string }>() as { modelLabel: string; modelPk: string };

    const [model, setModel] = useState<ConfiguredModel | null>(null);
    const [loadingModel, setLoadingModel] = useState<boolean>(true);
    const [errorLoadingModel, setErrorLoadingModel] = useState<string | null>(null);

    const [tab, setTab] = useState<ModelManagementTab>('overview');
    const language: ManageModelText = useMemo(() => manageModelText, []);

    const refreshModel = useCallback(async () => {
        const request: ModelRequest = { model_label: modelLabel, model_pk: modelPk };
        getModel(api, request, setModel, setLoadingModel, setErrorLoadingModel);
    }, [modelLabel, modelPk, api]);

    useEffect(() => {
        refreshModel();
    }, [refreshModel]);

    if (loadingModel) {
        return (
            <div className="flex h-full w-full items-center justify-center">
                <Loader className="h-6 w-6 animate-spin text-gray-500 dark:text-gray-400" />
            </div>
        );
    }

    if (errorLoadingModel || !model) {
        return <div className="text-red-500">{errorLoadingModel || language.failedToLoad}</div>;
    }

    return (
        <GatekeeperLayout>
            <ModelLayout>
                <div className="flex flex-col gap-8 space-y-6 p-4">
                    <ModelManagementTabs model={model} tab={tab} changeTab={setTab} />

                    {tab === 'overview' && <ModelSummary model={model} />}

                    {tab === 'permissions' && (
                        <ModelEntityTables<GatekeeperPermission> model={model} entity="permission" refreshModel={refreshModel} />
                    )}

                    {tab === 'roles' && <ModelEntityTables<GatekeeperRole> model={model} entity="role" refreshModel={refreshModel} />}

                    {tab === 'features' && <ModelEntityTables<GatekeeperFeature> model={model} entity="feature" refreshModel={refreshModel} />}

                    {tab === 'teams' && <ModelEntityTables<GatekeeperTeam> model={model} entity="team" refreshModel={refreshModel} />}
                </div>
            </ModelLayout>
        </GatekeeperLayout>
    );
}
