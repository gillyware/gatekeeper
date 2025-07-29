import { useGatekeeper } from '@/context/GatekeeperContext';
import { useApi } from '@/lib/api';
import { getEntities } from '@/lib/entities';
import { entityIndexText, type EntityTableText } from '@/lib/lang/en/entity';
import { swapOrder } from '@/lib/utils';
import { type GatekeeperEntity, type GatekeeperEntityModelMap, type GatekeeperUser } from '@/types';
import { type Pagination } from '@/types/api';
import { EntityPageRequest } from '@/types/api/entity';
import { Button } from '@components/ui/button';
import { DebouncedInput } from '@components/ui/debounced-input';
import { ArrowUpDown, Ban, CheckCircle, Loader, PauseCircle } from 'lucide-react';
import { type SetStateAction, useEffect, useMemo, useState } from 'react';
import { type NavigateFunction, useNavigate } from 'react-router-dom';

interface EntitiesTableProps {
    entity: GatekeeperEntity;
}

interface EntitiesTableHeaderProps {
    language: EntityTableText;
    setPageRequest: (request: SetStateAction<EntityPageRequest>) => void;
}

interface EntitiesTableRowProps<E extends GatekeeperEntity> {
    user: GatekeeperUser;
    navigate: NavigateFunction;
    entity: GatekeeperEntity;
    entityModel: GatekeeperEntityModelMap[E];
    language: EntityTableText;
}

interface EntitiesTablePaginationProps<E extends GatekeeperEntity> {
    entities: Pagination<GatekeeperEntityModelMap[E]> | null;
    language: EntityTableText;
    pageRequest: EntityPageRequest;
    setPageRequest: (request: SetStateAction<EntityPageRequest>) => void;
}

export default function EntitiesTable<E extends GatekeeperEntity>({ entity }: EntitiesTableProps) {
    const api = useApi();
    const navigate = useNavigate();
    const { user } = useGatekeeper();

    const initialPageRequest: EntityPageRequest = useMemo(
        () => ({
            page: 1,
            search_term: '',
            prioritized_attribute: 'is_active',
            name_order: 'asc',
            grant_by_default_order: 'asc',
            is_active_order: 'desc',
        }),
        [entity],
    );

    const [entities, setEntities] = useState<Pagination<GatekeeperEntityModelMap[E]> | null>(null);
    const [searchTerm, setSearchTerm] = useState<string>('');
    const [pageRequest, setPageRequest] = useState<EntityPageRequest>(initialPageRequest);

    const [loadingEntities, setLoadingEntities] = useState<boolean>(true);
    const [errorLoadingEntities, setErrorLoadingEntities] = useState<string | null>(null);

    const language: EntityTableText = useMemo(() => entityIndexText[entity].entityTableText, [entity]);

    useEffect(() => {
        setEntities(null);
        setSearchTerm('');
        setPageRequest(initialPageRequest);
        getEntities(api, entity, initialPageRequest, setEntities, setLoadingEntities, setErrorLoadingEntities);
    }, [entity]);

    useEffect(() => {
        if (entities !== null) {
            getEntities(api, entity, pageRequest, setEntities, setLoadingEntities, setErrorLoadingEntities);
        }
    }, [pageRequest]);

    return (
        <div className="w-full">
            <div className="mb-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-center">
                <DebouncedInput
                    value={searchTerm}
                    placeholder={language.searchInputPlaceholder}
                    debounceTime={1000}
                    setValue={setSearchTerm}
                    onDebouncedChange={async (value: string) => {
                        setPageRequest((prev) => ({
                            ...prev,
                            page: 1,
                            search_term: value,
                        }));
                    }}
                />
            </div>

            <div className="overflow-auto rounded-lg border dark:border-gray-700">
                <table className="w-full text-sm">
                    <EntitiesTableHeader language={language} setPageRequest={setPageRequest} />
                    <tbody>
                        {loadingEntities ? (
                            <tr>
                                <td colSpan={3} className="text-muted-foreground px-2 py-6 text-center sm:px-4">
                                    <Loader className="mx-auto h-5 w-5 animate-spin text-gray-500 dark:text-gray-400" />
                                </td>
                            </tr>
                        ) : errorLoadingEntities ? (
                            <tr>
                                <td colSpan={3} className="px-2 py-6 text-center text-red-500 sm:px-4">
                                    {errorLoadingEntities}
                                </td>
                            </tr>
                        ) : (entities?.data?.length || 0) === 0 ? (
                            <tr>
                                <td colSpan={3} className="text-muted-foreground px-2 py-6 text-center sm:px-4">
                                    {language.empty}
                                </td>
                            </tr>
                        ) : (
                            entities?.data.map((entityModel) => (
                                <EntitiesTableRow<E>
                                    key={entityModel.id}
                                    user={user}
                                    navigate={navigate}
                                    entity={entity}
                                    entityModel={entityModel}
                                    language={language}
                                />
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            <EntitiesTablePagination<E> entities={entities} language={language} pageRequest={pageRequest} setPageRequest={setPageRequest} />
        </div>
    );
}

function EntitiesTableHeader({ language, setPageRequest }: EntitiesTableHeaderProps) {
    return (
        <thead className="bg-muted">
            <tr>
                <th className="px-2 py-2 text-left font-semibold sm:px-4">
                    <span className="inline-flex items-center gap-1">
                        {language.nameColumn}
                        <ArrowUpDown
                            className="h-4 w-4 cursor-pointer opacity-50"
                            onClick={() => {
                                setPageRequest((prev) => ({
                                    ...prev,
                                    page: 1,
                                    prioritized_attribute: 'name',
                                    name_order: swapOrder(prev.name_order),
                                }));
                            }}
                        />
                    </span>
                </th>
                <th className="px-2 py-2 text-left font-semibold sm:px-4">
                    <span className="inline-flex items-center gap-1">
                        {language.grantedByDefaultColumn}
                        <ArrowUpDown
                            className="h-4 w-4 cursor-pointer opacity-50"
                            onClick={() => {
                                setPageRequest((prev) => ({
                                    ...prev,
                                    page: 1,
                                    prioritized_attribute: 'grant_by_default',
                                    grant_by_default_order: swapOrder(prev.grant_by_default_order),
                                }));
                            }}
                        />
                    </span>
                </th>
                <th className="px-2 py-2 text-left font-semibold sm:px-4">
                    <span className="inline-flex items-center gap-1">
                        {language.statusColumn}
                        <ArrowUpDown
                            className="h-4 w-4 cursor-pointer opacity-50"
                            onClick={() => {
                                setPageRequest((prev) => ({
                                    ...prev,
                                    page: 1,
                                    prioritized_attribute: 'is_active',
                                    is_active_order: swapOrder(prev.is_active_order),
                                }));
                            }}
                        />
                    </span>
                </th>
            </tr>
        </thead>
    );
}

function EntitiesTableRow<E extends GatekeeperEntity>({ user, navigate, entity, entityModel, language }: EntitiesTableRowProps<E>) {
    return (
        <tr
            role="button"
            tabIndex={0}
            onClick={() => {
                if (user.permissions.can_manage) {
                    navigate(`/${entity}s/${entityModel.id}/manage`);
                }
            }}
            onKeyDown={(e) => {
                if (user.permissions.can_manage && e.key === 'Enter') {
                    navigate(`/${entity}s/${entityModel.id}/manage`);
                }
            }}
            className="cursor-pointer border-t transition-colors hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800"
        >
            <td className="px-2 py-2 sm:px-4">{entityModel.name}</td>
            <td className="px-2 py-2 sm:px-4">
                {entityModel.grant_by_default ? (
                    <div className="flex items-center gap-2">
                        <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                        <span className="hidden text-green-700 sm:inline-flex dark:text-green-300">{language.granted}</span>
                    </div>
                ) : (
                    <div className="flex items-center gap-2">
                        <Ban className="h-4 w-4 text-red-600 dark:text-red-400" />
                        <span className="hidden text-red-600 sm:inline-flex dark:text-red-400">{language.notGranted}</span>
                    </div>
                )}
            </td>
            <td className="px-2 py-2 sm:px-4">
                {entityModel.is_active ? (
                    <div className="flex items-center gap-2">
                        <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                        <span className="hidden text-green-700 sm:inline-flex dark:text-green-300">{language.active}</span>
                    </div>
                ) : (
                    <div className="flex items-center gap-2">
                        <PauseCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                        <span className="hidden text-yellow-700 sm:inline-flex dark:text-yellow-300">{language.inactive}</span>
                    </div>
                )}
            </td>
        </tr>
    );
}

function EntitiesTablePagination<E extends GatekeeperEntity>({ entities, language, pageRequest, setPageRequest }: EntitiesTablePaginationProps<E>) {
    if (!entities || entities.total === 0) {
        return null;
    }

    return (
        <div className="flex w-full items-center justify-end gap-2 pt-2">
            <Button
                size="sm"
                variant="outline"
                disabled={pageRequest.page === 1}
                onClick={() => setPageRequest((prev) => ({ ...prev, page: prev.page - 1 }))}
            >
                {language.previous}
            </Button>

            <span className="text-sm">{language.pagination(entities.from, entities.to, entities.total)}</span>

            <Button
                size="sm"
                variant="outline"
                disabled={pageRequest.page === entities.last_page}
                onClick={() => setPageRequest((prev) => ({ ...prev, page: prev.page + 1 }))}
            >
                {language.next}
            </Button>
        </div>
    );
}
