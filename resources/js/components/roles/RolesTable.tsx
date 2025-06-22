import { useGatekeeper } from '@/context/GatekeeperContext';
import { useApi } from '@/lib/api';
import { cn } from '@/lib/utils';
import { Pagination } from '@/types/api';
import { RolePageRequest, RolePageResponse } from '@/types/api/role';
import { Role } from '@/types/models';
import { Button } from '@components/ui/button';
import { ArrowUpDown, CheckCircle, Loader, PauseCircle } from 'lucide-react';
import { HTMLAttributes, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

export default function RolesTable({ className = '', ...props }: HTMLAttributes<HTMLDivElement>) {
    const api = useApi();
    const navigate = useNavigate();
    const { user } = useGatekeeper();

    const [roles, setRoles] = useState<Role[]>([]);
    const [filterParameters, setFilterParameters] = useState<RolePageRequest>({
        page: 1,
        important_attribute: 'is_active',
        name_order: 'asc',
        is_active_order: 'desc',
    });
    const [pagination, setPagination] = useState<Omit<Pagination<Role>, 'data'> | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        setLoading(true);
        api.getRoles(filterParameters)
            .then((response: RolePageResponse) => {
                if (response.status >= 400) {
                    setError(response.errors?.general || 'Failed to load roles.');
                    setPagination(null);
                    return;
                }

                const body = response.data as Pagination<Role>;
                const { data, ...meta } = body;

                setRoles(body.data);
                setPagination(meta);
                setError(null);
            })
            .finally(() => setLoading(false));
    }, [filterParameters]);

    return (
        <div className={cn('w-full', className)} {...props}>
            <div className="overflow-auto rounded-lg border dark:border-gray-700">
                <table className="w-full text-sm">
                    <thead className="bg-muted">
                        <tr>
                            <th className="px-4 py-2 text-left font-semibold">
                                <span className="inline-flex items-center gap-1">PK</span>
                            </th>
                            <th className="px-4 py-2 text-left font-semibold">
                                <span className="inline-flex items-center gap-1">
                                    Name
                                    <ArrowUpDown
                                        className="h-4 w-4 cursor-pointer opacity-50"
                                        onClick={() => {
                                            setFilterParameters((prev) => ({
                                                ...prev,
                                                page: 1,
                                                important_attribute: 'name',
                                                name_order: prev.name_order === 'asc' ? 'desc' : 'asc',
                                            }));
                                        }}
                                    />
                                </span>
                            </th>
                            <th className="px-4 py-2 text-left font-semibold">
                                <span className="inline-flex items-center gap-1">
                                    Status
                                    <ArrowUpDown
                                        className="h-4 w-4 cursor-pointer opacity-50"
                                        onClick={() => {
                                            setFilterParameters((prev) => ({
                                                ...prev,
                                                page: 1,
                                                important_attribute: 'is_active',
                                                is_active_order: prev.is_active_order === 'asc' ? 'desc' : 'asc',
                                            }));
                                        }}
                                    />
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {loading ? (
                            <tr>
                                <td colSpan={3} className="text-muted-foreground px-4 py-6 text-center">
                                    <Loader className="mx-auto h-5 w-5 animate-spin text-gray-500 dark:text-gray-400" />
                                </td>
                            </tr>
                        ) : error ? (
                            <tr>
                                <td colSpan={3} className="px-4 py-6 text-center text-red-500">
                                    {error}
                                </td>
                            </tr>
                        ) : roles.length === 0 ? (
                            <tr>
                                <td colSpan={3} className="text-muted-foreground px-4 py-6 text-center">
                                    No roles found.
                                </td>
                            </tr>
                        ) : (
                            roles.map((role) => (
                                <tr
                                    key={role.id}
                                    role="button"
                                    tabIndex={0}
                                    onClick={() => {
                                        if (user.permissions.can_manage) {
                                            navigate(`/roles/${role.id}/manage`);
                                        }
                                    }}
                                    onKeyDown={(e) => {
                                        if (user.permissions.can_manage && e.key === 'Enter') {
                                            navigate(`/roles/${role.id}/manage`);
                                        }
                                    }}
                                    className={cn(
                                        'cursor-pointer border-t transition-colors hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800',
                                    )}
                                >
                                    <td className="px-4 py-2">{role.id}</td>
                                    <td className="px-4 py-2">{role.name}</td>
                                    <td className="px-4 py-2">
                                        <div className="flex items-center gap-2">
                                            {role.is_active ? (
                                                <>
                                                    <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                                                    <span className="text-green-700 dark:text-green-300">Active</span>
                                                </>
                                            ) : (
                                                <>
                                                    <PauseCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                                                    <span className="text-yellow-700 dark:text-yellow-300">Inactive</span>
                                                </>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {pagination && pagination.total > 0 && (
                <div className="flex w-full items-center justify-end gap-2 pt-2">
                    <Button
                        size="sm"
                        variant="outline"
                        disabled={filterParameters.page === 1}
                        onClick={() => setFilterParameters((prev) => ({ ...prev, page: prev.page - 1 }))}
                    >
                        Previous
                    </Button>

                    <span className="text-sm">{`${pagination.from} to ${pagination.to} of ${pagination.total}`}</span>

                    <Button
                        size="sm"
                        variant="outline"
                        disabled={filterParameters.page === pagination.last_page}
                        onClick={() => setFilterParameters((prev) => ({ ...prev, page: prev.page + 1 }))}
                    >
                        Next
                    </Button>
                </div>
            )}
        </div>
    );
}
