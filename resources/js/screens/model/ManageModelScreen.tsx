import { useApi } from '@/lib/api';
import { getModel } from '@/lib/models';
import { useCallback, useEffect, useState } from 'react';
import { useParams } from 'react-router';

import ModelEntityTables from '@/components/model/ModelEntityTables';
import ModelManagementTabs from '@/components/model/ModelEntityTabs';
import ModelSummary from '@/components/model/ModelSummary';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import ModelLayout from '@/layouts/model-layout';

import { ModelProvider } from '@/context/ModelContext';
import { manageModelText } from '@/lib/lang/en/model/manage';
import { type ModelManagementTab } from '@/types';
import { type ConfiguredModel, type ModelRequest } from '@/types/api/model';
import { Loader } from 'lucide-react';

export default function ManageModelScreen() {
    const api = useApi();
    const { modelLabel, modelPk } = useParams<{ modelLabel: string; modelPk: string }>();

    const [model, setModel] = useState<ConfiguredModel | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [tab, setTab] = useState<ModelManagementTab>('overview');

    const refreshModel = useCallback(async () => {
        const request: ModelRequest = { model_label: modelLabel!, model_pk: modelPk! };
        getModel(api, request, setModel, setLoading, setError);
    }, [api, modelLabel, modelPk]);

    useEffect(() => {
        refreshModel();
    }, [refreshModel]);

    if (loading) {
        return (
            <div className="flex h-full w-full items-center justify-center">
                <Loader className="h-6 w-6 animate-spin text-gray-500 dark:text-gray-400" />
            </div>
        );
    }

    if (error || !model) {
        return <div className="text-red-500">{error || manageModelText.failedToLoad}</div>;
    }

    return (
        <GatekeeperLayout>
            <ModelLayout>
                <ModelProvider value={{ model, refreshModel, tab, setTab }}>
                    <div className="flex flex-col gap-8 space-y-6 py-4">
                        <ModelManagementTabs model={model} tab={tab} changeTab={setTab} />

                        {tab === 'overview' && <ModelSummary />}
                        {tab === 'permissions' && <ModelEntityTables entity="permission" />}
                        {tab === 'roles' && <ModelEntityTables entity="role" />}
                        {tab === 'features' && <ModelEntityTables entity="feature" />}
                        {tab === 'teams' && <ModelEntityTables entity="team" />}
                    </div>
                </ModelProvider>
            </ModelLayout>
        </GatekeeperLayout>
    );
}
