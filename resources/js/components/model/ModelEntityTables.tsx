import ModelEntityAssignmentsTable from '@/components/model/ModelEntityAssignmentsTable';
import ModelUnassignedEntitiesTable from '@/components/model/ModelUnassignedEntitiesTable';
import { useGatekeeper } from '@/context/GatekeeperContext';
import { useModel } from '@/context/ModelContext';
import { useApi } from '@/lib/api';
import {
    assignEntityToModel,
    denyEntityFromModel,
    getDeniedEntitiesForModel,
    getEntityAssignmentsForModel,
    getEntitySupportForModel,
    getUnassignedEntitiesForModel,
    unassignEntityFromModel,
    undenyEntityFromModel,
} from '@/lib/models';
import {
    GatekeeperModelEntityDenialMap,
    type GatekeeperEntity,
    type GatekeeperEntityModelMap,
    type GatekeeperModelEntityAssignmentMap,
} from '@/types';
import { type Pagination } from '@/types/api';
import { type GetModelEntitiesPageRequest, type ModelEntityRequest } from '@/types/api/model';
import { useCallback, useEffect, useMemo, useState } from 'react';
import ModelDeniedEntitiesPage from './ModelDeniedEntitiesTable';

export default function ModelEntityTables<E extends GatekeeperEntity>({ entity }: { entity: E }) {
    const api = useApi();
    const { config, user } = useGatekeeper();
    const { model } = useModel();

    const buildPayload = useCallback(
        (entityName: string): ModelEntityRequest => ({
            model_label: model.model_label,
            model_pk: model.model_pk,
            entity,
            entity_name: entityName,
        }),
        [model, entity],
    );

    const [assignments, setAssignments] = useState<Pagination<GatekeeperModelEntityAssignmentMap[E]> | null>(null);
    const [unassigned, setUnassigned] = useState<Pagination<GatekeeperEntityModelMap[E]> | null>(null);
    const [denied, setDenied] = useState<Pagination<GatekeeperModelEntityDenialMap[E]> | null>(null);

    const [assignmentsPage, setAssignmentsPage] = useState<GetModelEntitiesPageRequest>({
        entity,
        model_label: model.model_label,
        model_pk: model.model_pk,
        page: 1,
        search_term: '',
    });
    const [unassignedPage, setUnassignedPage] = useState<GetModelEntitiesPageRequest>({
        entity,
        model_label: model.model_label,
        model_pk: model.model_pk,
        page: 1,
        search_term: '',
    });
    const [deniedPage, setDeniedPage] = useState<GetModelEntitiesPageRequest>({
        entity,
        model_label: model.model_label,
        model_pk: model.model_pk,
        page: 1,
        search_term: '',
    });

    const [processing, setProcessing] = useState<boolean>(false);
    const [assignmentsError, setAssignmentsError] = useState<string | null>(null);
    const [unassignedError, setUnassignedError] = useState<string | null>(null);
    const [deniedError, setDeniedError] = useState<string | null>(null);

    const [searchAssignments, setSearchAssignments] = useState<string>('');
    const [searchUnassigned, setSearchUnassigned] = useState<string>('');
    const [searchDenied, setSearchDenied] = useState<string>('');

    useEffect(() => {
        getEntityAssignmentsForModel(api, assignmentsPage, setAssignments, () => {}, setAssignmentsError);
    }, [api, assignmentsPage]);

    useEffect(() => {
        getUnassignedEntitiesForModel(api, unassignedPage, setUnassigned, () => {}, setUnassignedError);
    }, [api, unassignedPage]);

    useEffect(() => {
        getDeniedEntitiesForModel(api, deniedPage, setDenied, () => {}, setDeniedError);
    }, [api, deniedPage]);

    const support = useMemo(() => getEntitySupportForModel(config, model), [config, model]);
    const canManage = user.permissions.can_manage;

    const refreshPages = async () => {
        setAssignmentsPage((prev) => ({ ...prev }));
        setUnassignedPage((prev) => ({ ...prev }));
        setDeniedPage((prev) => ({ ...prev }));
    };

    return (
        <div className="flex w-full flex-col gap-8">
            <ModelEntityAssignmentsTable<E>
                entity={entity}
                data={assignments}
                pageRequest={assignmentsPage}
                setPageRequest={setAssignmentsPage}
                processing={processing}
                error={assignmentsError}
                setProcessing={setProcessing}
                refreshPages={refreshPages}
                onEntityAction={async (name) => unassignEntityFromModel(api, buildPayload(name), setProcessing, setAssignmentsError)}
                canManage={canManage}
                search={searchAssignments}
                setSearch={setSearchAssignments}
            />

            <ModelUnassignedEntitiesTable<E>
                entity={entity}
                data={unassigned}
                pageRequest={unassignedPage}
                setPageRequest={setUnassignedPage}
                processing={processing}
                error={unassignedError}
                setProcessing={setProcessing}
                refreshPages={refreshPages}
                assignEntity={async (name) => assignEntityToModel(api, buildPayload(name), setProcessing, setUnassignedError)}
                denyEntity={async (name) => denyEntityFromModel(api, buildPayload(name), setProcessing, setUnassignedError)}
                canManage={canManage}
                entitySupported={support[entity].supported}
                search={searchUnassigned}
                setSearch={setSearchUnassigned}
            />

            <ModelDeniedEntitiesPage<E>
                entity={entity}
                data={denied}
                pageRequest={deniedPage}
                setPageRequest={setDeniedPage}
                processing={processing}
                error={deniedError}
                setProcessing={setProcessing}
                refreshPages={refreshPages}
                undenyEntity={async (name) => undenyEntityFromModel(api, buildPayload(name), setProcessing, setDeniedError)}
                canManage={canManage}
                entitySupported={support[entity].supported}
                search={searchDenied}
                setSearch={setSearchDenied}
            />
        </div>
    );
}
